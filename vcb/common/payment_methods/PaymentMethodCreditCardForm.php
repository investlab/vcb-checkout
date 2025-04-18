<?php

namespace common\payment_methods;


class PaymentMethodCreditCardForm extends PaymentMethodBasicForm
{

    public function rules()
    {
        return array(
            array(array('payment_method_id', 'partner_payment_id'), 'required', 'message' => 'Bạn phải chọn {attribute}.'),
            array(array('payment_method_id', 'partner_payment_id'), 'number'),
        );
    }

    public function attributeLabels()
    {
        return [
            'payment_method_id' => 'Ngân hàng để thanh toán',
            'partner_payment_id' => 'Kênh thanh toán',
        ];
    }
}
