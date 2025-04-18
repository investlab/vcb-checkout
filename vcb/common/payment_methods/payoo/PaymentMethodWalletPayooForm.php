<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods\payoo;

use common\payment_methods\PaymentMethodWalletForm;
use common\partner_payments\PartnerPaymentBasic;

class PaymentMethodWalletPayooForm extends PaymentMethodWalletForm
{

    function initRequest(PartnerPaymentBasic &$partner_payment)
    {
        header('Location:' . 'https://www.payoo.vn/');
        die();
    }

}
