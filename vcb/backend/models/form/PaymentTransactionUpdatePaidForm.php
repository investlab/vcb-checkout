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

class PaymentTransactionUpdatePaidForm extends PaymentTransaction {

    public $receipt = null;
    public $time_paid = null;

    public function rules() {
        return [
            [['receipt', 'time_paid'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['receipt'], 'string', 'max' => 50, 'message' => '{attribute} không hợp lệ'],
            [['time_paid'], 'isDateTime', 'message' => '{attribute} không hợp lệ.'],
        ];
    }

    public function isDateTime($attribute, $params) {
        if (!preg_match('/^\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}:\d{1,2}$/', $this->$attribute)) {
            $this->addError($attribute, 'Thời gian thanh toán không hợp lệ');
        }
    }

    public function attributeLabels() {
        return array(
            'receipt' => 'Mã GD kênh thanh toán',
            'time_paid' => 'Thời gian thanh toán',
        );
    }
}
