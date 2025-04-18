<?php

namespace merchant\models\form;

use common\components\utils\Translate;

class MerchantUpdateForm extends LanguageBasicForm {

    public $name;
    public $logo;
    public $website;
    public $email_notification;
    public $mobile_notification;
    public $url_notification;
    public $verifyCode;

    public function rules() {
        return array(
            array(array('name', 'verifyCode'), 'required', 'message' => Translate::get('Bạn phải nhập') . ' {attribute}.'),
            array(array('name', 'website'), 'string'),
            array(array('logo'), 'file', 'extensions' => ['jpg', 'jpge', 'png', 'gif']),
            array(array('email_notification'), 'isEmail'),
            array(array('mobile_notification'), 'isMobile'),
            array(array('url_notification'), 'isURL'),
            array('verifyCode', 'captcha', 'captchaAction' => 'merchant/captcha', 'message' => '{attribute} ' . Translate::get('không đúng')),
        );
    }

    public function isEmail($attribute, $params) {
        if (!\common\components\utils\Validation::isEmail($this->$attribute)) {
            $this->addError($attribute, Translate::get('Email nhận thông báo không hợp lệ'));
        }
    }

    public function isMobile($attribute, $params) {
        if (!\common\components\utils\Validation::isMobile($this->$attribute)) {
            $this->addError($attribute, Translate::get('Số điện thoại nhận thông báo không hợp lệ'));
        }
    }

    public function isURL($attribute, $params) {
        if (!\common\components\utils\Validation::isURL($this->$attribute)) {
            $this->addError($attribute, Translate::get('URL nhận thông báo không hợp lệ'));
        }
    }

    public function attributeLabels() {
        return [
            'name' => Translate::get('Tên merchant'),
            'logo' => Translate::get('Logo'),
            'website' => Translate::get('Địa chỉ trang web'),
            'email_notification' => Translate::get('Email nhận thông báo'),
            'mobile_notification' => Translate::get('Số điện thoại nhận thông báo'),
            'url_notification' => Translate::get('URL nhận thông báo'),
            'verifyCode' => Translate::get('Mã bảo mật')
        ];
    }

}
