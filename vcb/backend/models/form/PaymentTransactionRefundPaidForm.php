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

class PaymentTransactionRefundPaidForm extends PaymentTransaction
{

    public $refund_amount = null;
    public $reason_refund_id = null;
    public $reason_refund = null;
    public $receipt = null;
    public $time_refund = null;

    public function rules()
    {
        return [
            [['receipt', 'time_refund', 'refund_amount', 'reason_refund'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['receipt'], 'string', 'max' => 50, 'message' => '{attribute} không hợp lệ'],
            [['reason_refund'], 'string'],
            [['reason_refund_id'], 'integer', 'min' => 1, 'tooSmall' => 'Bạn chưa lựa chọn {attribute}'],
            [['time_refund'], 'isDateTime', 'message' => '{attribute} không hợp lệ.'],
            [['refund_amount'], 'validateForm'],
        ];
    }

    public function isDateTime($attribute, $params)
    {
        if (!preg_match('/^\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}:\d{1,2}$/', $this->$attribute)) {
            $this->addError($attribute, 'Thời gian hoàn tiền không hợp lệ');
        }
    }

    public function validateForm($attribute, $param)
    {
        switch ($attribute) {
            case "refund_amount":
                if (intval($this->refund_amount) < 1) {
                    $this->addError('refund_amount', 'Số tiền muốn hoàn phải lớn hơn 0');
                }
                break;
        }
    }

    public function attributeLabels()
    {
        return array(
            'refund_amount' => 'Số tiền muốn hoàn',
            'reason_refund_id' => 'Lý do hoàn tiền',
            'reason_refund' => 'Nội dung hoàn tiền',
            'receipt' => 'Mã GD kênh thanh toán',
            'time_refund' => 'Thời gian hoàn tiền',
        );
    }
}
