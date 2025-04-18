<?php

namespace merchant\models\form;

use common\components\utils\Translate;

class MerchantChangePasswordForm extends LanguageBasicForm {

    public $new_password;
    public $confirm_password;
    public $verifyCode;

    public function rules() {
        return array(
            array(array('new_password', 'confirm_password', 'verifyCode'), 'required', 'message' => Translate::get('Bạn phải nhập') . ' {attribute}.'),
            array(array('new_password'), 'isPassword'),
            array(array('confirm_password'), 'isConfirmPassword'),
            array('verifyCode', 'captcha', 'captchaAction' => 'merchant/captcha', 'message' => '{attribute} ' . Translate::get('không đúng')),
        );
    }

    public function isPassword($attribute, $params) {
        if (!\common\components\utils\Validation::isPassword($this->$attribute)) {
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
            'new_password' => Translate::get('Mật khẩu kết nối mới'),
            'confirm_password' => Translate::get('Nhập lại mật khẩu kết nối mới'),
            'verifyCode' => Translate::get('Mã bảo mật')
        ];
    }

}
