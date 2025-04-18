<?php

namespace common\payment_methods\cyber_source_vcb_3ds2;

use common\models\db\PartnerPaymentAccount;
use common\payment_methods\PaymentMethodCreditCardForm;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\TransactionBusiness;
use common\components\utils\Translate;
use common\components\utils\Strings;
use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Yii;
use common\models\business\CheckoutOrderBusiness;

class PaymentMethodCreditCardCyberSourceVcb3ds2VisaCreditCardForm extends PaymentMethodCreditCardCyberSourceVcb3ds2{}
