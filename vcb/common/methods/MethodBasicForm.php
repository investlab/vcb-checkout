<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\methods;

use yii\base\Model;
use common\components\libs\Tables;
use common\models\business\PaymentMethodBusiness;
use common\models\db\Zone;
use common\components\utils\Strings;
use common\models\db\Method;
use common\models\db\PaymentMethod;
use common\models\db\Bill;
use Yii;

class MethodBasicForm extends Model
{

    public $payment_methods = null;
    public $payment_models = null;
    public $payment_model_active = null;
    public $active = null;
    public $payment_amount = null;
    public $enviroment = null;
    public $info = null;
    public $bill = null;
    public $checkout_order = null;
    public $payment_transaction = null;
    public $no_select_partner_payment = null;

    function __construct($info, $enviroment, $active = false)
    {
        parent::__construct();
        $this->info = $info;
        $this->enviroment = $enviroment;
        $this->active = $active;
    }

    public function getMethodCode()
    {
        return $this->info['code'];
    }

    public function loadPaymentModels($payment_amount, $option, $payment_method_code = '', $partner_payment_code = '', $transaction = null, $no_select_partner_payment = true)
    {
        $this->payment_amount = $payment_amount;
        $this->no_select_partner_payment = $no_select_partner_payment;
        $this->payment_transaction = $transaction;
        $payment_methods = $this->getPaymentMethods($payment_amount, $this->no_select_partner_payment);
        if (!empty($payment_methods)) {
            foreach ($payment_methods as $key => $payment_method) {
                if (isset($payment_method['partner_payments']) && !empty($payment_method['partner_payments'])) {
                    $current_partner_payment_code = $this->_getPartnerPaymentCode($payment_method, $payment_method_code, $partner_payment_code);
                    if ($current_partner_payment_code != false) {
                        $model_payment_method_name = PaymentMethod::getModelFormName($current_partner_payment_code, $this->getMethodCode(), $payment_method['code']);
                        $current_partner_payment_id = $payment_method['partner_payments'][$current_partner_payment_code]['id'];
                        if ($model_payment_method_name != false && class_exists($model_payment_method_name)) {
                            $obj = new $model_payment_method_name();
                            if ($obj->set($this->payment_amount, $this->enviroment, $option, $payment_method, $current_partner_payment_code, $current_partner_payment_id, $this->payment_transaction, $this)) {
                                $obj->load(Yii::$app->request->get());
                                if ($obj->initOption()) {
                                    $merchant_id = $this->checkout_order['merchant_id'];
                                    $payment_method_id = $obj->payment_method_id;
                                    $method_id = $obj->method['info']['id'];
                                    $merchant_payment_method_fee = self::getMerchantFee($merchant_id,$payment_method_id,$method_id);
                                    if ($merchant_payment_method_fee) {

                                        $this->payment_models[$payment_method['code']] = $obj;
                                        if (strtolower($this->payment_models[$payment_method['code']]->payment_method_code) == strtolower($payment_method_code)) {

                                            $this->payment_models[$payment_method['code']]->active = true;

                                            $this->payment_model_active = &$this->payment_models[$payment_method['code']];
                                            $this->active = true;
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    protected function _getPartnerPaymentCode($payment_method, $payment_method_code = '', $partner_payment_code = '')
    {
        if (strtoupper($payment_method['code']) == strtoupper($payment_method_code)) {
            if ($partner_payment_code != '' && array_key_exists(strtoupper($partner_payment_code), $payment_method['partner_payments'])) {
                return strtoupper($partner_payment_code);
            } else {
                foreach ($payment_method['partner_payments'] as $partner_payment) {
                    return strtoupper($partner_payment['code']);
                }
            }
        } else {
            foreach ($payment_method['partner_payments'] as $partner_payment) {
                return strtoupper($partner_payment['code']);
            }
        }
        return false;
    }

    public function getPaymentMethods($payment_amount, $no_select_partner_payment = true)
    {

        $result = array();
        $partner_payment_info = Tables::selectAllDataTable("partner_payment", "status = " . \common\models\db\PartnerPayment::STATUS_ACTIVE, "", "id");
        if ($partner_payment_info != false) {
            $payment_methods = PaymentMethodBusiness::getListByPaymentAmountAndMethodCode($payment_amount, time(), strtolower($this->getMethodCode()), $this->enviroment);
            if (!empty($payment_methods)) {
                if ($no_select_partner_payment) {
                    foreach ($payment_methods as $payment_method) {
                        if (!array_key_exists($payment_method['id'], $result)) {
                            $result[$payment_method['id']] = $payment_method;
                        }
                        if (isset($partner_payment_info[$payment_method['partner_payment_id']])) {
                            $result[$payment_method['id']]['partner_payments'][$payment_method['partner_payment_code']] = $partner_payment_info[$payment_method['partner_payment_id']];
                        }
                    }
                } else {
                    foreach ($payment_methods as $payment_method) {
                        if (!array_key_exists($payment_method['id'], $result)) {
                            $result[$payment_method['id']] = $payment_method;
                        }
                        if (isset($partner_payment_info[$payment_method['partner_payment_id']])) {
                            $result[$payment_method['id']]['partner_payments'][$payment_method['partner_payment_code']] = $partner_payment_info[$payment_method['partner_payment_id']];
                        }
                    }
                }
            }
        }
        return $result;
    }
    public function getMerchantFee($merchant_id,$payment_method_id,$method_id){
        $data = Tables::selectOneDataTable("merchant_fee", ["merchant_id = :merchant_id AND payment_method_id = :payment_method_id AND method_id = :method_id AND status = :status ", "merchant_id" => $merchant_id, "payment_method_id" => $payment_method_id, "method_id" => $method_id, "status" => 4]);// Active
        if ($data){
            return true;
        }else{
            $data_method = Tables::selectOneDataTable("merchant_fee", ["merchant_id = :merchant_id AND payment_method_id = :payment_method_id AND method_id = :method_id AND status = :status ", "merchant_id" => $merchant_id, "method_id" => $method_id, "payment_method_id" => 0, "status" => 4]);// Active

            if ($data_method){
                return true;
            }else{
                return false;
            }
        }
    }
}


