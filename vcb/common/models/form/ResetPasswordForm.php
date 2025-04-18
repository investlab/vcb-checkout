<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class ResetPasswordForm extends LanguageBasicForm
{
    public $id;
    public $code;
    public $checksum;
    public $password;
    public $rep_password;

    public function rules()
    {
        return [
            [['password', 'rep_password'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['password'], 'string', 'min' => 6, 'tooShort' => 'Mật khẩu gồm 6 ký tự.'],
            [['id'], 'integer'],
            [['code', 'checksum'], 'string'],
            ['rep_password', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false, 'message' => "Nhập lại mật khẩu không khớp."],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'checksum' => 'Checksum',
            'password' => 'Mật khẩu',
            'rep_password' => 'Nhập lại mật khẩu',
        ];
    }

} 