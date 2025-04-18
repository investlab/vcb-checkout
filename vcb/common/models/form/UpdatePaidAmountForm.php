<?php

namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class UpdatePaidAmountForm extends LanguageBasicForm
{

    public $invoice_id;
    public $amount;
    public $payment_method_id;
    public $partner_payment_id;
    public $partner_payment_method_receipt;
    public $time_paid;

    public function rules()
    {
        return [
            [['amount', 'partner_payment_method_receipt', 'time_paid'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['payment_method_id', 'partner_payment_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['time_paid'], 'date', 'format' => 'dd-mm-yyyy', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'invoice_id' => 'Hóa đơn',
            'amount' => 'Số tiền',
            'payment_method_id' => 'Phương thức thanh toán',
            'partner_payment_id' => 'Kênh thanh toán',
            'partner_payment_method_receipt' => 'Mã tham chiếu',
            'time_paid' => 'Thời gian thanh toán',
        ];
    }
} 