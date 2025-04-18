<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UserUpdatePasswordForm extends LanguageBasicForm
{

    public $password;
    public $rep_password;

    public function rules()
    {
        return [
            [['password', 'rep_password'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['password'], 'string', 'min' => 6, 'tooShort' => 'Mật khẩu gồm 6 ký tự.'],
            ['rep_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Mật khẩu mới và mật khẩu xác nhận không trùng.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => 'Mật khẩu',
            'rep_password' => 'Xác nhận mật khẩu'
        ];
    }

} 