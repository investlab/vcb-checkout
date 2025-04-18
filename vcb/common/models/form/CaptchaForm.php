<?php
namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class CaptchaForm extends LanguageBasicForm
{

    public $cancel_id;
    public $verifyCode;

    public function rules()
    {
        return array(
            array(array('verifyCode'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array(array('cancel_id'), 'integer'),
            array('verifyCode', 'captcha', 'captchaAction' => 'bill/captcha', 'message' => '{attribute} không đúng.'),
        );
    }

    public function attributeLabels()
    {
        return [
            'cancel_id' => 'cancel_id',
            'verifyCode' => 'Mã xác thực'
        ];
    }
} 