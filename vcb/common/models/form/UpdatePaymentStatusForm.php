<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UpdatePaymentStatusForm extends LanguageBasicForm
{

    public $receipt;
    public $payment_transaction_id;

    public function rules()
    {
        return array(
            array(array('receipt'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array(array('payment_transaction_id'), 'integer'),
//            array('verifyCode', 'captcha','captchaAction'=>'bill/captcha','message' => '{attribute} không đúng.'),
        );
    }

    public function attributeLabels()
    {
        return [
            'payment_transaction_id' => 'Mã giao dịch',
            'receipt' => 'Mã giao dịch kênh thanh toán',
//            'verifyCode' => 'Mã xác thực'
        ];
    }

} 