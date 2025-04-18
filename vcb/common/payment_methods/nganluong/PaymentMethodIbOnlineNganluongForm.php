<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods\nganluong;

use common\payment_methods\PaymentMethodIbOnlineForm;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\TransactionBusiness;
use common\models\business\CheckoutOrderBusiness;
use common\components\libs\Tables;

class PaymentMethodIbOnlineNganluongForm extends PaymentMethodIbOnlineForm
{

    function initRequest(PartnerPaymentBasic &$partner_payment)
    {
        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
                //------------
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => $this->getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                );
                $result = $this->partner_payment->processRequest($this, $inputs);
                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => $result['response']['token'],
                        'partner_payment_info' => json_encode($result['response']),
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paying($inputs);
                    if ($result['error_message'] == '') {
                        header('Location:' . $payment_url);
                        die();
                    } else {
                        $this->error_message = $result['error_message'];
                    }
                } else {
                    $this->error_message = $result['error_message'];
                }
            }
        } else {
            $this->error_message = $result['error_message'];
        }
        return true;
    }

}
