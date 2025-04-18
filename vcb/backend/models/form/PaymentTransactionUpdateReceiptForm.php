<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\PaymentTransaction;
use common\components\libs\Tables;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPaymentMethod;
use common\components\libs\Weblib;

class PaymentTransactionUpdateReceiptForm extends PaymentTransaction {

    public $receipt = null;
    public $time_paid = null;

    public function rules() {
        return [
            [['receipt'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['receipt'], 'string', 'max' => 50, 'message' => '{attribute} không hợp lệ'],
        ];
    }

    public function attributeLabels() {
        return array(
            'receipt' => 'Mã GD kênh thanh toán',           
        );
    }
}
