<?php

namespace app\components;

use common\components\utils\Encryption;
use common\components\utils\Translate;
use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\components\utils\Logs;
use yii\web\UploadedFile;


//10000 : Lỗi validate tầng form
//50000: Lỗi nhà cung cấp
class ApiV2Controller extends ActiveController {

    public $error_code = 99;
    public $error_message = 'Lỗi không xác định';
    public $data = [];
    public $status = 200;
    public $device_token;
    public static $userInfo;
    public $no_validate = [];
    protected $_except = ['login','forget-password-step1','login-mobile', 'register', 'forgetpassword-request','get-config', 'get-list-promotion', 'get-list-promotion-detail', 'check-mobile-exist', 'remove-mobile-exit-step1', 'remove-mobile-exit-step2', 'process',  'return', 'cancel', 'notify'];
    protected $_encrypt_function = ['order/history', 'checkout/get-bank', 'checkout/payment','order/get-transaction-by-id'];
    protected $_except_checksum = ['get-config','forget-password-step1'];
    protected $_except_non_auth = ['process','forget-password-step1','return', 'cancel', 'notify'];
    private $_request_id;
    public $client_ip;
    public $modelForm;

    public function init() {

        parent::init();
        $this->device_token = Yii::$app->request->headers->get('device-token');
        Yii::$app->language = (Yii::$app->request->headers->get('lang') == 'en' ? 'en' : 'vi');
        //$key_session = 'user_info_' . md5($this->device_token);
//        self::$userInfo = Yii::$app->getUser()->getIdentity(false);
        \Yii::$app->user->enableSession = true;

    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        //$behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
        $behaviors['rateLimiter'] = [
            'class' => \yii\filters\RateLimiter::className(),
            'enableRateLimitHeaders' => false,
        ];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        $behaviors['authenticator'] = [
            'except' => $this->_except,
            'class' => CompositeAuth::className(),
            // 'tokenParam' => 'access_token',
            'authMethods' => [
                HttpBasicAuth::className(),
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];
        return $behaviors;
    }

    function _maskWirteLog($action) {

        $this->_request_id = uniqid();
        $GLOBALS['REQ_ID_APP'] = $this->_request_id;
        $method = 'POST';
        $params_rq = Yii::$app->getRequest()->getBodyParams();
        if (empty($params_rq)) {
            $method = 'GET';
            $params_rq = Yii::$app->getRequest()->getQueryParams();
        }
        if (isset($params_rq['password']))
            unset($params_rq['password']);
        if (isset($params_rq['payment_password']))
            unset($params_rq['payment_password']);
        if (isset($params_rq['card_number']))
            $params_rq['card_number'] = substr($params_rq['card_number'], 0, 6) . '.' . 'xxxx.xxxx.' . substr($params_rq['card_number'], -4);

        $logs = [
            'TYPE' => 'nl-mobi-app',
            'MODE' => 'INPUT',
            'RQ_ID' => $this->_request_id,
            'ACTION' => Yii::$app->controller->id . '/' . $action->id,
            'METHOD' => $method,
            'DATA' => json_encode((empty($params_rq) ? ["msg" => "không có dữ liệu truyền lên"] : $params_rq)),
            'DEVICE-TOKEN' => (string) Yii::$app->request->headers->get('device-token'),
            'HEADER' => json_encode(['authorization' => Yii::$app->request->headers->get('authorization'),
                'user-agent' => Yii::$app->request->headers->get('user-agent'),
                'lang' => Yii::$app->request->headers->get('lang'),
                'ip' => Yii::$app->request->headers->get('x-real-ip'),
                'host' => Yii::$app->request->headers->get('host')]),
        ];

        Logs::createV2('app', date('Ymd') . '.json', $logs);
    }

    public function beforeAction($action) {

//        if ((empty(Yii::$app->request->headers->get('lang')) && $action->id == 'login')) {
//            $this->data = [];
//            $this->error_code = 10001;
//            $this->error_message = Translate::get('device_token bị khoá');
//            $this->asJson(['code' => $this->error_code, 'data' => $this->data, 'message' => Translate::get($this->error_message), 'status' => $this->status, 'encrypt' => false]);
//
//            return false;
//        }
        if ($this->_isChecksum($action) === false) {
            $this->asJson(['code' => 100015, 'data' => [], 'message' => Translate::get('Có lỗi xảy ra, xin vui lòng thử lại'), 'status' => $this->status, 'encrypt' => false]);
            return false;
        }


        $this->_maskWirteLog($action); // Ghi log
        parent::beforeAction($action);
        if (!in_array($action->id,$this->_except_non_auth)){
            $this->require_auth_bacsic($action); //Authen basic
            if (empty($this->device_token)) {
                $this->data = [];
                $this->error_code = 100016;
                $this->error_message = Translate::get('device_token không tồn tại.');
                $this->asJson($this->getResponse($action));
                return false;
            }

        }

        if (empty(self::$userInfo)) {
            self::$userInfo = \yii\helpers\ArrayHelper::toArray(Yii::$app->user->getIdentity());
            if (isset(Yii::$app->user->getIdentity()->client_ip)){
                self::$userInfo['client_ip'] = Yii::$app->user->getIdentity()->client_ip;

            }
            if (isset(Yii::$app->user->getIdentity()->device_token)){
                self::$userInfo['device_token'] = Yii::$app->user->getIdentity()->device_token;

            }
            if (isset(Yii::$app->user->getIdentity()->access_token)){
                self::$userInfo['access_token'] = Yii::$app->user->getIdentity()->access_token;

            }
            if (isset(Yii::$app->user->getIdentity()->time_expired)){
                self::$userInfo['time_expired'] = Yii::$app->user->getIdentity()->time_expired;

            }


        }


        // self::$userInfo = Yii::$app->session->get('user_info_' . md5($this->device_token));
        //var_dump(\yii\helpers\ArrayHelper::toArray(Yii::$app->user->getIdentity()));
        //var_dump(self::$userInfo);
        if ($this->_checkDeviceToken($action) === false) {
            $this->data = [];
            $this->error_code = 10001;
            $this->error_message = Translate::get('Thông tin tài khoản không hợp lệ. Vui lòng đăng nhập lại.');
            $this->asJson($this->getResponse($action));

            return false;
        }

        if ($this->_checkIP($action) == false && in_array(Yii::$app->controller->id, $GLOBALS['LIST_FEATURE_REJECT'])) {
            $this->data = [];
            $this->error_code = 10001;
            $this->error_message = Translate::get('Phiên đăng nhập hết hạn do thay đổi IP. Tài khoản của bạn được đăng nhập bởi IP:');
            $this->asJson($this->getResponse($action));

            return false;
        }

        if (!empty($this->modelClass) && !in_array($action->id, $this->no_validate)) {

            $this->modelForm = new $this->modelClass;
            $this->modelForm->scenario = $action->id;

            if (\Yii::$app->getRequest()->isGet) {
                $load = Yii::$app->getRequest()->getQueryParams();
            } else {
                $load = Yii::$app->getRequest()->getBodyParams();
            }

            if (isset($_FILES)) {
                foreach ($_FILES as $k => $v) {
                    $this->modelForm->$k = UploadedFile::getInstanceByName($k);
                }
            }

            $this->modelForm->load($load, '');


            // var_dump( $model->load(Yii::$app->getRequest()->getBodyParams(),''));
            $this->modelForm->validate();
            if ($this->modelForm->hasErrors()) {
                $this->error_code = '10000';
                if (!empty($this->modelForm->error_code))
                    $this->error_code = $this->modelForm->error_code;
                $this->status = 200;

                foreach ($this->modelForm->getErrors() as $k => $v) {
                    $error_message[] = implode(' | ', $v);
                }
                $this->error_message = implode(' | ', $error_message);

                $this->asJson($this->getResponse($action));

                return false;
            }
        }



        return true;
    }
    private static function _getUserIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private function _checkDeviceToken($action) {

        if (in_array($action->id, $this->_except)) {
            return true;
        }
        if (empty(self::$userInfo) || \Yii::$app->user->isGuest) {
            return false;
        }
        if (!empty(self::$userInfo["access_token"]) && !empty(self::$userInfo["time_expired"])) {
            if (self::$userInfo["time_expired"] < time()) {
                \Yii::$app->user->logout();
                return false;
            }
        }

        return true;
    }

    public function _checkIP($action) {

        if (!in_array($action->id, $this->_except)) {
            if (self::$userInfo['client_ip'] != Yii::$app->getRequest()->getUserIP()) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function getResponse($action = null) {
        if ($this->error_message == '') {
            $this->error_message = 'Thành công';
        }
        $data_logs = $this->data;
        if (isset($data_logs['img_cmt'])) {
            unset($data_logs['img_cmt']);
        }
        if (isset($data_logs['img_person'])) {
            unset($data_logs['img_person']);
        }

        $log_output['TYPE'] = 'nl-mobi-app';
        $log_output['MODE'] = 'OUTPUT';
        $log_output['RQ_ID'] = $this->_request_id;
        $log_output['ACTION'] = Yii::$app->controller->id . '/' . $action->id;
        $log_output['DEVICE-TOKEN'] = (string) Yii::$app->request->headers->get('device-token');
        $log_output['MESSAGE'] = Translate::get($this->error_message);
        $log_output['STATUS'] = (string) $this->status;
        $log_output['CODE'] = (string) Translate::get($this->error_code);
        $log_output['DATA'] = json_encode((empty($data_logs) ? ["msg" => "không có dữ liệu"] : $data_logs), JSON_UNESCAPED_SLASHES);

        Logs::createV2('app', date('Ymd') . '.json', $log_output);

        return ['code' => $this->error_code, 'data' => $this->data, 'message' => Translate::get($this->error_message), 'status' => $this->status, 'encrypt' => false];
    }

    public function getResponseEncrypt($action = null, $data = null) {
        if ($this->error_message == '') {
            $this->error_message = 'Thành công';
        }
        $log_output['TYPE'] = 'nl-mobi-app';
        $log_output['MODE'] = 'OUTPUT';
        $log_output['RQ_ID'] = $this->_request_id;
        $log_output['ACTION'] = Yii::$app->controller->id . '/' . $action->id;
        $log_output['DEVICE-TOKEN'] = (string) Yii::$app->request->headers->get('device-token');
        $log_output['MESSAGE'] = $this->error_message;
        $log_output['STATUS'] = (string) $this->status;
        $log_output['CODE'] =  $this->error_code;
        $log_output['DATA'] = json_encode((empty($this->data) ? ["msg" => "không có dữ liệu"] : $this->data), JSON_UNESCAPED_SLASHES);

        Logs::createV2('app', date('Ymd') . '.json', $log_output);
        $response = self::encryptData($this->data);
        return ['code' => $this->error_code, 'data' => $response, 'message' => ($this->error_message), 'status' => $this->status, 'encrypt' => true];
    }

    public function forward($array) {
        $this->error_code = @$array['error_code'];
        $this->error_message = @$array['error_message'];
        $this->data = @$array['response'];
        $this->status = 200;
    }

    public function afterAction($action, $result) {
        parent::afterAction($action, $result);
        if (in_array(Yii::$app->controller->id . '/' . $action->id, $this->_encrypt_function)) {
            return $this->asJson($this->getResponseEncrypt($action, $this->data));
        } else {
            return $this->asJson($this->getResponse($action));
        }
    }

    function require_auth_bacsic($action) {
        if (in_array($action->id, $this->_except)) {
            $AUTH_USER = 'appVCBMERCHANT';
            $AUTH_PASS = 'appNL@2020';
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
            $is_not_authenticated = (
                    !$has_supplied_credentials ||
                    $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
                    $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS
                    );
            if ($is_not_authenticated) {
                // Logs::createV2('app', '', ['AUTHEN_BASIC_FAIL' => Yii::$app->request->headers->getIterator()]);
                ob_clean();
                header('HTTP/1.1 401 Authorization Required');
                header('WWW-Authenticate: Basic realm="Access denied"');
                exit;
            }
        }
    }

    public function encryptData($data) {
        $key = hash('sha256', $GLOBALS['ENCRYPT_KEY'] . self::getBearerToken());
        $data = Encryption::EncryptTrippleDes(json_encode($data), $key);
        return $data;
    }


    function makeChecksum($action) {
        $key = $GLOBALS['ENCRYPT_KEY'] . $GLOBALS['LINK_FIX'];
        $data = Encryption::EncryptTrippleDes($this->device_token . $action->id . "|" . time(), $key);
        return $data;
    }

    function _getChecksum() {
        $key = $GLOBALS['ENCRYPT_KEY'] . $GLOBALS['LINK_FIX'];
        $checksum = Yii::$app->request->headers->get('checksum');

        $data = Encryption::DecryptTrippleDes($checksum, $key);
        //$flag = explode('|', $data);
        return $data;
    }

    private function _isChecksum($action) {
        $checksum = $this->_getChecksum();
        if (!in_array($action->id, $this->_except_checksum)) {
            //echo $this->device_token . $action->id."|".Yii::$app->request->headers->get('time');
            // echo $checksum;
            if (empty($checksum) || $this->device_token . $action->id."|".Yii::$app->request->headers->get('time') != $checksum) {
                return false;
            }
        }
        return true;
    }

    function getBearerToken() {
        $headers = Yii::$app->request->headers->get('authorization');
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/^Bearer\s+(.*?)$/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

}
