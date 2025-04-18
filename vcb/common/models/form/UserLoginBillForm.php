<?php

namespace common\models\form;

use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserLoginBillForm extends LanguageBasicForm
{

    public $username;
    public $password;
    public $verifyCode;

    public function rules()
    {
        return array(
            array(array('username', 'password', 'verifyCode'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array('verifyCode', 'captcha', 'captchaAction' => 'bill/captcha', 'message' => '{attribute} không đúng.'),
        );
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Tên đăng nhập',
            'password' => 'Mật khẩu',
            'verifyCode' => 'Mã xác thực'
        ];
    }


}