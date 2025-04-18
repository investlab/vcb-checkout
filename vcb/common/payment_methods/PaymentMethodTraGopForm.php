<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 10/24/2019
 * Time: 2:26 PM
 */

namespace common\payment_methods;

class PaymentMethodTraGopForm extends PaymentMethodBasicForm
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