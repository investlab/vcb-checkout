<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use Yii;
use common\payment_methods\PaymentMethodBasicForm;
use common\models\db\PartnerPaymentAccount;

class PartnerPaymentBasic
{

    public $form;

    function __construct()
    {

    }

    function initRequest(PaymentMethodBasicForm &$form)
    {

    }

    function initConfirmRequest(PaymentMethodBasicForm &$form)
    {

    }

    function initVerify(PaymentMethodBasicForm &$form)
    {

    }

    function initConfirmVerify(PaymentMethodBasicForm &$form)
    {

    }

    function initSuccess(PaymentMethodBasicForm &$form)
    {

    }

    function processRequest(PaymentMethodBasicForm &$form, $params)
    {

    }

    function processConfirmRequest(PaymentMethodBasicForm &$form, $params)
    {

    }

    function processVerify(PaymentMethodBasicForm &$form, $params)
    {

    }

    function processConfirmVerify(PaymentMethodBasicForm &$form, $params)
    {

    }

    function processSuccess(PaymentMethodBasicForm &$form, $params)
    {

    }
    
    function getPartnerPaymentAccount($transaction_info) {
        return PartnerPaymentAccount::getPartnerPaymentAccount($transaction_info['partner_payment_account_id']);
    }
    
    function getPartnerPaymentAccountByCheckoutOrder($checkout_order_info, $partner_payment_code) {
        return PartnerPaymentAccount::getPartnerPaymentAccountByMerchantId($checkout_order_info['merchant_id'], $checkout_order_info['currency'], $partner_payment_code);
    }

    final protected function _writeLog($fileName, $data)
    {
        $fp = fopen($fileName, 'a');
        if ($fp) {
            $line = date("H:i:s, d/m/Y:  ", time()) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }
}
