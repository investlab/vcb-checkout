<?php
namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UpdatePaymentInforForm extends LanguageBasicForm
{
    public $id;
    public $payment_transaction_id;
    public $card_number;
    public $card_type;
    public $time_paid;

    public function rules()
    {
        return array(
            array(array('payment_transaction_id', 'card_number', 'card_type'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array(array('id'), 'integer'),
            array(array('payment_transaction_id'), 'string'),
            array(array('time_paid'), 'safe'),
            array(array('time_paid'), 'date', 'format' => 'dd-mm-yyyy hh:ii', 'message' => '{attribute} không hợp lệ .hh:ii dd-mm-yyyy'),

        );
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_number' => 'Số thẻ',
            'card_type' => 'Loại thẻ',
            'payment_transaction_id' => 'Mã giao dịch',
            'time_paid' => 'Thời gian thanh toán'
        ];
    }

} 