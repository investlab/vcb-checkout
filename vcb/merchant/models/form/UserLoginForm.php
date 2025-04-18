<?php

namespace merchant\models\form;

use common\components\utils\Validation;
use common\models\db\UserLogin;
use common\components\utils\Translate;

class UserLoginForm extends LanguageBasicForm {

    public $username;
    public $password;
    public $verifyCode;

    public function rules() {
        return array(
            array(array('username', 'password', 'verifyCode'), 'required', 'message' => Translate::get('Bạn phải nhập') . ' {attribute}.'),
            array(array('username'), 'isEmail'),
            array('verifyCode', 'captcha', 'captchaAction' => 'user-login/captcha', 'message' => '{attribute} ' . Translate::get('không đúng') . '.'),
        );
    }

    public function attributeLabels() {
        return [
            'username' => Translate::get('Tên đăng nhập'),
            'password' => Translate::get('Mật khẩu'),
            'verifyCode' => Translate::get('Mã bảo mật')
        ];
    }

    public function isEmail($attribute, $params) {
        if (!Validation::isEmail($this->$attribute)) {
            $this->addError($attribute, Translate::get('Tên đăng nhập không hợp lệ'));
        }
    }

}
