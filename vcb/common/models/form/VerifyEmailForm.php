<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class VerifyEmailForm extends LanguageBasicForm
{
    public $user_login_temp_id;
    public $otp;
    public $verifyCode;

    public function rules()
    {
        return array(
            [['otp', 'verifyCode'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['otp'], 'string', 'min' => 6, 'tooShort' => '{attribute} bao gồm 6 ký tự.'],
            [['user_login_temp_id'], 'integer'],
            ['verifyCode', 'captcha', 'captchaAction' => 'user-register/captcha', 'message' => '{attribute} không đúng.'],
        );
    }

    public function attributeLabels()
    {
        return [
            'otp' => 'Mã xác thực',
            'verifyCode' => 'Mã bảo mật',
            'user_login_temp_id' => 'ID',
        ];
    }

} 