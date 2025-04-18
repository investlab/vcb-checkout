<?php


namespace app\controllers;

use app\models\bussiness\UserFlow;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\components\utils\Validation;
use common\models\business\SendMailBussiness;
use Yii;
use app\components\ApiV2Controller;

class UserController extends  ApiV2Controller
{
    public $modelClass = 'app\models\form\UserLoginForm';
    private $_modelFlow;


    public function init() {

        parent::init();
        $this->no_validate = ['step1', 'get-user-by-email', 'get-user-by-id', 'get-mobile-info', 'get-balance', 'lock-user', 'logout', 'get-bank-withdraw', 'get-bank-withdraw-test', 'forget-payment-password-step1', 'update-lang-notify','user-step1','user-step2','forget-password-step1'];
        $this->_modelFlow = new UserFlow;
    }

    public function actionLogin() {

        Yii::$app->user->logout();

        $this->_modelFlow->setAttributes(\yii\helpers\ArrayHelper::toArray($this->modelForm), false);


            $result = $this->_modelFlow->userCheckLogin();
            if ($result['error_message'] == '') {
                $user_id = $result['response']['id'];
                $result['response']['user_id'] = $user_id;
                Yii::$app->session->set('user_id_' . $result['response']['id'], $result['response']['access_token']);
                Yii::$app->session->set('user_info_' . md5($this->device_token), $result['response']);
                Yii::$app->session->remove($this->device_token);
                Yii::$app->user->loginByAccessToken($result['response']['access_token'], get_class($this));
                $this->_modelFlow->user_id = $user_id;
                $this->_modelFlow->_user_info = $result['response'];

            }

        return $this->forward($result);
    }

    public function actionForgetPasswordStep1()
    {
        if(Yii::$app->request->post()){
            $email = Yii::$app->request->post('email');
            $result = $this->_modelFlow->fogetPassword($email);
            if(isset($result)){
                return $this->forward($result);
            }
        }
    }
    public function actionLogout(){

        $this->_modelFlow->access_token  = self::$userInfo["access_token"];

        $result = $this->_modelFlow->logout();


        return $this->forward($result);
    }
}