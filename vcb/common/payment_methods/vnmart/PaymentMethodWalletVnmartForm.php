<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods\vnmart;

use common\payment_methods\PaymentMethodWalletForm;
use common\partner_payments\PartnerPaymentBasic;

class PaymentMethodWalletVnmartForm extends PaymentMethodWalletForm
{

    function initRequest(PartnerPaymentBasic &$partner_payment)
    {
        header('Location:' . 'https://www.vnmart.vn/webportals/Trang-chu.aspx');
        die();
    }

}
