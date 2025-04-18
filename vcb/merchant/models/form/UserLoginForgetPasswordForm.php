<?php

namespace merchant\models\form;

use common\components\utils\Validation;
use common\components\utils\Translate;

class UserLoginForgetPasswordForm extends LanguageBasicForm {

    public $new_password;
    public $confirm_password;

    public function rules() {
        return array(
            array(array('new_password', 'confirm_password'), 'required', 'message' => Translate::get('Bạn phải nhập') . ' {attribute}.'),
            array(array('new_password'), 'isPassword'),
            array(array('confirm_password'), 'isConfirmPassword'),
        );
    }

    public function isPassword($attribute, $params) {
        if (!Validation::isPassword($this->$attribute)) {
            $this->addError($attribute, Translate::get('Mật khẩu yêu cầu từ 6 đến 20 ký tự và không chứa dấu cách'));
        }
    }

    public function isConfirmPassword($attribute, $params) {
        if ($this->new_password != $this->confirm_password) {
            $this->addError($attribute, Translate::get('Nhập lại mật khẩu mới không đúng'));
        }
    }

    public function attributeLabels() {
        return [
            'new_password' => Translate::get('Mật khẩu mới'),
            'confirm_password' => Translate::get('Nhập lại mật khẩu mới'),
        ];
    }

}
