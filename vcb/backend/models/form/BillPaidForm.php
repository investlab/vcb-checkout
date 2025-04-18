<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\components\libs\Tables;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPaymentMethod;
use common\components\libs\Weblib;

class BillPaidForm extends Bill {

    public $payment_transaction_id = null;
    public $payment_transaction_amount = null;
    public $partner_payment_id = null;
    public $payment_method_id = null;
    public $installment_bank_id = null;
    public $installment_period = null;
    public $receipt = null;
    public $time_paid = null;

    public function rules() {
        return [
            [['payment_transaction_id', 'partner_payment_id', 'payment_method_id', 'installment_bank_id', 'installment_period'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['payment_transaction_amount'], 'number', 'message' => '{attribute} không hợp lệ'],
            [['receipt', 'payment_transaction_id', 'payment_transaction_amount', 'partner_payment_id', 'payment_method_id', 'time_paid'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
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
            'payment_method_id' => 'Hình thức thanh toán',
            'partner_payment_id' => 'Kênh thanh toán',
            'payment_transaction_id' => 'Mã GD thanh toán',
            'payment_transaction_amount' => 'Số tiền thanh toán',
            'receipt' => 'Mã GD kênh thanh toán',
            'installment_bank_id' => 'Ngân hàng trả góp',
            'installment_period' => 'Kỳ trả góp',
            'time_paid' => 'Thời gian thanh toán',
        );
    }

    public function getPartnerPayments($result = array()) {
        $partner_payment_info = Tables::selectAllDataTable("partner_payment", "status = " . PartnerPayment::STATUS_ACTIVE);
        if ($partner_payment_info != false) {
            $result = Weblib::getArraySelectBoxForData($partner_payment_info, 'id', 'name', $result);
        }
        return $result;
    }

    public function getPaymentMethods($result = array()) {
        if (intval($this->partner_payment_id) != 0) {
            $partner_payment_method_info = Tables::selectAllDataTable("partner_payment_method", "partner_payment_id = " . $this->partner_payment_id . " AND status = " . PartnerPaymentMethod::STATUS_ACTIVE);
            if ($partner_payment_method_info != false) {
                $payment_method_ids = Weblib::getValuesByKey($partner_payment_method_info, "payment_method_id");
                if (!empty($payment_method_ids)) {
                    $payment_method_info = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") AND status = " . PaymentMethod::STATUS_ACTIVE);
                    if ($payment_method_info != false) {
                        $result = Weblib::getArraySelectBoxForData($payment_method_info, 'id', 'name', $result);
                    }
                }
            }
        }
        return $result;
    }
    
    private function _setInstallmentBankId() {
        if (intval($this->payment_method_id) != 0 && PaymentMethod::hasSupportInstallment($this->payment_method_id)) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", "id = ".$this->payment_method_id." AND status = " . PaymentMethod::STATUS_ACTIVE);
            if ($payment_method_info != false) {
                $config = json_decode($payment_method_info['config'], true);
                if (intval(@$config['installment_bank_id']) != 0) {
                    $this->installment_bank_id = intval(@$config['installment_bank_id']);
                    return $this->installment_bank_id;
                }
            }
        }
        return false;
    }

    public function getInstallmentPeriods($result = array()) {
        if ($this->_setInstallmentBankId() !== false) {
            $installment_bank_period_info = Tables::selectAllDataTable("installment_bank_period", "installment_bank_id = ".$this->installment_bank_id." AND status = " . \common\models\db\InstallmentBankPeriod::STATUS_ACTIVE, "period ASC ");
            if ($installment_bank_period_info != false) {
                $result = Weblib::getArraySelectBoxForData($installment_bank_period_info, 'period', 'name', $result, array(0));
            }
        }
        return $result;
    }
}
