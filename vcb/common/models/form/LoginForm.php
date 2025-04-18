<?php

namespace common\models\form;


use common\components\utils\Logs;
use common\components\utils\Translate;
use common\models\business\UserBusiness;
use common\models\business\UserLoginFailBusiness;
use common\models\db\User;
use common\models\db\UserLogin;
use common\models\db\UserAdminAccount;
use common\payments\CyberSource;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use Yii;

class LoginForm extends LanguageBasicForm
{
    public $username;
    public $password;
    public $verifyCode;

    public function rules()
    {
        return array(
            array(array('username', 'password'), 'required', 'message' => Translate::get('Bạn phải nhập '). ' {attribute}.'),
            array('verifyCode', 'captcha', 'captchaAction' => 'user/captcha', 'message' => '{attribute} '.Translate::get('không đúng')),
        );
    }

    public function attributeLabels()
    {
        return [
            'username' => Translate::get('Tên đăng nhập'),
            'password' => Translate::get('Mật khẩu'),
            'verifyCode' => Translate::get('Mã xác thực')
        ];
    }

    protected function _checkUserGroup($user_id, $check_user_group_codes)
    {
        $user_group_codes = UserAdminAccount::getUserGroupCodesByUserId($user_id);
        if (!empty($user_group_codes)) {
            foreach ($user_group_codes as $user_group_code) {
                if (in_array($user_group_code, $check_user_group_codes)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function _checkLogin($user_grop_check, &$error_message = '')
    {
        if ($this->validate()) {
            $error_message = Translate::get('Tài khoản hoặc mật khẩu không chính xác');
            $user = User::findOne(['username' => $this->username]);

            if ($user != null) {
                if ($this->_checkUserGroup($user->id, $user_grop_check)) {
                    if (md5($this->password) == $user->password) {
                        if ($user->status == User::STATUS_ACTIVE) {
                            UserLoginFailBusiness::deleteByUserId($user->id);
                            if (Yii::$app->user->login($user)) {
                                $error_message = '';
                                return true;
                            }
                        } else {
                            $error_message = Translate::get('Tài khoản đăng nhập đang bị khóa');
                        }
                    } else {
                        UserBusiness::checkLoginFail($user->id, $error_message);
                    }
                } else {
                    $error_message = Translate::get('Tài khoản quản trị chưa được cấp quyền');
                }
            }
        }
        return false;
    }

    public function loginBackend(&$error_message = '')
    {
        $user_grop_check = [
            'ADMIN', 'OPERATOR_STAFF', 'OPERATOR_MANAGE', 'CEO','CN_VCB'
        ];
        if ($this->_checkLogin($user_grop_check, $error_message)) {
            return true;
        } else {
            return false;
        }
    }

} 