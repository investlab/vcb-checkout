<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class RefundForm extends LanguageBasicForm
{
    public $id;
    public $payment_refund_transaction_id;
    public $reason_refund;
    public $reason_cancel_id;

    public function rules()
    {
        return array(
            array(array('payment_refund_transaction_id'), 'required', 'message' => 'Bạn phải nhập {attribute}.'),
            array(array('id'), 'integer'),
            array(array('reason_refund'), 'string'),
            [['reason_cancel_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.']
        );
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_refund_transaction_id' => 'Mã giao dịch hoàn tiền',
            'reason_refund' => 'Lý do hoàn tiền',
            'reason_cancel_id' => 'Lý do hủy'
        ];
    }

} 