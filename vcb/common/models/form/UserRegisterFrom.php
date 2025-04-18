<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserRegisterFrom extends LanguageBasicForm
{
    public $id;
    public $fullname;
    public $email;
    public $mobile;
    public $password;
    public $rep_password;
    public $verifyCode;

    public function rules()
    {
        return [
            [['fullname', 'email', 'password', 'rep_password', 'verifyCode'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id'], 'integer'],
            [['mobile'], 'number', 'message' => 'Số điện thoại không hợp lệ.'],
            [['password'], 'string', 'min' => 6, 'tooShort' => 'Mật khẩu gồm 6 ký tự.'],
            [['mobile'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => 'Số điện thoại không hợp lệ.', 'tooShort' => 'Số điện thoại không hợp lệ.'],
            [['email'], 'email', 'message' => '{attribute} không hợp lệ.'],
            ['rep_password', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => "Nhập lại mật khẩu không khớp."],
            ['verifyCode', 'captcha', 'captchaAction' => 'user-register/captcha', 'message' => '{attribute} không đúng.'],


        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Mã',
            'fullname' => 'Họ và tên',
            'email' => 'Email',
            'mobile' => 'Điện thoại',
            'password' => 'Mật khẩu',
            'rep_password' => 'Nhập lại mật khẩu',
            'verifyCode' => 'Mã bảo mật',
        ];
    }


}