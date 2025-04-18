<?php

namespace merchant\models\form;

use common\components\utils\Validation;
use common\components\utils\Translate;

class UserLoginRequestForgetPassword extends LanguageBasicForm {

    public $email;
    public $verifyCode;

    public function rules() {
        return array(
            array(array('email', 'verifyCode'), 'required', 'message' => Translate::get('Bạn phải nhập') . ' {attribute}.'),
            array(array('email'), 'isEmail'),
            array('verifyCode', 'captcha', 'captchaAction' => 'user-login/captcha', 'message' => '{attribute} ' . Translate::get('không đúng') . '.'),
        );
    }

    public function attributeLabels() {
        return [
            'email' => Translate::get('Email đăng nhập'),
            'verifyCode' => Translate::get('Mã bảo mật')
        ];
    }

    public function isEmail($attribute, $params) {
        if (!Validation::isEmail($this->$attribute)) {
            $this->addError($attribute, Translate::get('Email đăng nhập không hợp lệ'));
        }
    }

}
