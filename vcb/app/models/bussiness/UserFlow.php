<?php

/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 5/6/2019
 * Time: 4:37 PM
 */

namespace app\models\bussiness;


use app\models\app\UserLogin;
use app\models\app\UserLoginToken;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\components\utils\Validation;
use common\models\business\SendMailBussiness;
use common\models\db\Merchant;
use Yii;

class UserFlow extends \yii\base\Model {

    public $user_name;
    public $fullname;
    public $email;
    public $new_email;
    public $password;
    public $type;
    public $otp_type;
    public $verify_type;
    public $payment_password;
    public $phone_number;
    public $social_id;
    public $birthday;
    public $address;
    public $zone_id;
    public $gender;
    public $link_register;
    public $user_id;
    public $otp;
    public $representative_birthday;
    public $representative_address;
    public $representative_full_name;
    public $representative_verify_type;
    public $representative_verify_number;
    public $representative_phone;
    public $old_password;
    public $new_password;
    public $device_token;
    public $mobile;
    public $mobile_login;
    public $_user_info;
    public $_merchant_info;
    public $method_code;
    public $secure_code;
    public $code;
    public $time_created;
    public $allow_mobile_login;
    public $created_time;
    public $user_old_id;
    public $access_token;

    public function userCheckLogin() {
        $result = UserLogin::checkLogin($this->email,$this->password);
        if ($result['error_message'] == '') {
            if ($result['response']['status'] == UserLogin::STATUS_ACTIVE) {

                $rs = self::createTokenByUser(['user_id' => $result['response']['id'], 'device_token' => Yii::$app->request->headers->get('device-token'), 'client_ip' => Yii::$app->getRequest()->getUserIP()]);
                if ($rs['error_message'] == '') {
                    if ($result['response']['merchant_id']){
                        $merchant_info = Merchant::getById($result['response']['merchant_id']);
                        if ($merchant_info){
                            $result['response']['merchant_info'] = $merchant_info;
                        }else{
                            $result['response']['merchant_info'] = [];

                        }

                    }else{
                        $result['response']['merchant_info'] = [];

                    }

                    $result['error_code'] = 0;
                    $result['response']['access_token'] = $rs['response']['access_token'];
                    $result['response']['time_expired'] = $rs['response']['time_expired'];
                    $result['response']['client_ip'] = $rs['response']['client_ip'];


                    return $result;
                } else {
                    $result['response']['access_token'] = '';
                    $result['error_message'] = $rs['error_message'];
                    $result['error_code'] = $rs['error_code'];
                }
            }
        }
        return $result;
    }

    //params [id=:id]

    //params [access_token=:token]
    public static function getUserByAccessToken($params) {
        $error_code = 99;
        $message = 'Lỗi không xác địnhh';
        $response = [];
        $user_login_token = UserLoginToken::findOne(['access_token' => $params['access_token']]);
        if (isset($user_login_token) && !empty($user_login_token)){
            $user_login_token_arr = $user_login_token->toArray();
            $user_info = UserLogin::getById($user_login_token_arr['user_id']);

            $error_code = 0;
            $message = '';
            $response = $user_login_token_arr;
            $response['user_info'] = $user_info;

        }
        return [
            'error_code' => $error_code,
            'error_message' => $message,
            'response' => $response,
        ];


    }


//http://192.168.11.121:8088/NganLuongAPI?fnc=createTokenByUser&data={%22email%22:%22thanhbl@peacesoft.net%22,%22device_token%22:%22xxxxxxxxxxxxxxxxxxxxxxxxxxxx%22}&client_ip=127.0.0.1&user_admin_id=103&language=vi
    //params [email=:email,device_token=:device_token]
    public static function createTokenByUser($params) {
        $data = [];
        $error_code = 0;
        $error_message = '';
        $token = UserLoginToken::findOne(['user_id' => $params['user_id']]);

        if ($token){
            Yii::$app->db->createCommand()->update('user_login_token',['status' => 1,'timeUpdated' => time()],'user_id='.$params['user_id'])->execute();
        }
        $model  = new UserLoginToken();
        $model->user_id = $params['user_id'];
        $model->access_token  = hash_hmac('sha256',strtoupper($params['user_id'].$params['device_token'].$params['client_ip'].time()),$GLOBALS['SHA256_KEY']);
        $model->device_token = $params['device_token'];
        $model->clientIp = $params['client_ip'];
        $model->status = UserLoginToken::STATUS_ACTIVE;
        $model->timeCreated = time();
        $model->timeUpdated = time();
        $model->timeLimit = time()+900;//15 phút
        if ($model->validate()){
            if ($model->save()){
                $data['access_token'] = $model->access_token;
                $data['time_expired'] = $model->timeLimit;
                $data['client_ip'] = $model->clientIp;
            } else {
                $error_code = 10016;

                $error_message = 'Có lỗi khi thêm tài khoản';
            }
        }else{
            $error_code = 10006;
            $error_message = $model->getErrors();
        }
        return [
            'error_code' => $error_code,
            'error_message' => $error_message,
            'response' => $data,
        ];



    }
    public function logout(){
        $result = Yii::$app->db->createCommand()->update('user_login_token',['status' => 1,'timeUpdated' => time()],'access_token='."'".$this->access_token."'" )->execute();
        if ($result){
            Yii::$app->session->remove('user_info_' . md5($this->device_token));
            Yii::$app->session->remove('checkPass');
            $data = Yii::$app->user->logout();
            return [
                'error_code' => 0,
                'error_message' => 'Đăng xuất thành công',
                'data' => $data,
            ];
        }else{
            return [
                'error_code' => 10008,
                'error_message' => 'Đăng xuất không thành công',
                'data' => [],
            ];

        }
    }



    public function fogetPassword($email){
        $data = Validation::isEmail($email);
        if($data){
            $user_info = Tables::selectOneDataTable("user_login", "email = '" . $email."'");
            if($user_info == false){
                $result['error_code'] = null;
                $result['error_message'] = 'Email chưa được đăng ký trên hệ thống';
                $result['response'] = [];
                return $result;
            }else{
                $email = Yii::$app->request->post('email');
                $name = $user_info['fullname'];
                $time_update = $user_info['time_updated'];
                SendMailBussiness::sendApp($email, Translate::get('Xác thực yêu cầu lấy mật khẩu'), 'forget_password',[
                    'fullname' => $name,
                    'link' => ROOT_URL.'vi/merchant/user-login/forget-password?email='.$email.'&name='.$name.'&time_update='.$time_update,
                ]);
                $result['error_code'] = 0;
                $result['error_message'] = 'Email lấy mật khẩu đã được gửi';
                $result['response'] = [];
                return $result;
            }
        }else
        {
            $result['error_code'] = 0;
            $result['error_message'] = 'Email không hợp lệ';
            $result['response'] =  [];
            return $result;
        }

    }



}
