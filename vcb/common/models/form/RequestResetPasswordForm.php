<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class RequestResetPasswordForm extends LanguageBasicForm
{
    public $email;

    public function rules()
    {
        return [
            [['email'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['email'], 'email', 'message' => '{attribute} không hợp lệ.'],


        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

} 