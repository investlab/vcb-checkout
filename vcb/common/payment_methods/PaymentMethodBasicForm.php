<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods;

use common\components\utils\Logs;
use yii\base\Model;
use common\components\libs\Tables;
use common\models\business\PaymentMethodBusiness;
use common\models\db\Zone;
use common\components\utils\Strings;
use common\models\db\Method;
use Yii;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\BillBusiness;
use common\models\db\Voucher;
use common\components\libs\Weblib;
use common\components\utils\Translate;

class PaymentMethodBasicForm extends Model
{

    public $active = false;
    public $option = null;
    public $enviroment = null;
    public $payment_transaction = null;
    public $method = null;
    public $payment_method_code = null;
    public $payment_method_id = null;
    public $info = null;
    public $payment_url = null;
    public $error_message = '';
    public $partner_payment_code = null;
    public $partner_payment_id = null;
    public $partner_payment = null;
    public $config = null;
    public $payment_amount = null;
    public $checkout_order = null;
    public $has_verify_3d = true;
    public $payer_fee = null;
    public $merchant_fee_info = null;
    public $cycle_installment = null;
    public $version = null;

    final function set($payment_amount, $enviroment, $option, $payment_method_info, $partner_payment_code, $partner_payment_id, $payment_transaction = null, $method = null,$version = 1)
    {
        $this->payment_amount = $payment_amount;
        $this->enviroment = $enviroment;
        $this->option = $option;
        $this->payment_transaction = $payment_transaction;
        $this->method = $method;
        $this->checkout_order = isset($this->method->checkout_order) ? $this->method->checkout_order : null;
        $this->info = $payment_method_info;
        $this->payment_method_id = $payment_method_info['id'];
        $this->payment_method_code = $payment_method_info['code'];
        $this->partner_payment_code = strtoupper($partner_payment_code);
        $this->partner_payment_id = $partner_payment_id;
        $this->config = json_decode($payment_method_info['config'], true);
        $this->version = $version;
        return true;
    }

    final function initOption()
    {
        $this->_setPartnerPayment();

        if ($this->option == 'index') {
            return true;
        } elseif ($this->option == 'request') {
            if (strpos($this->payment_method_code, 'QR-CODE') && $this->partner_payment_code == 'VCB' && $this->checkout_order['version'] == '2.0') {
                return $this->initRequestOnus($this->partner_payment);
            } elseif (strpos($this->payment_method_code, 'QR-CODE') && $this->partner_payment_code == 'BIDV-VA' && $this->checkout_order['version'] == '2.0') {
                return $this->initRequestSeamless($this->partner_payment);
            } else {
                return $this->initRequest($this->partner_payment);

            }
        } elseif ($this->option == 'request_seamless') {
            return $this->initRequestSeamless($this->partner_payment);
        } elseif ($this->option == 'confirm-request') {
            return $this->initConfirmRequest($this->partner_payment);
        } elseif ($this->option == 'verify') {
            return $this->initVerify($this->partner_payment);
        } elseif ($this->option == 'confirm-verify') {
            return $this->initConfirmVerify($this->partner_payment);
        } elseif ($this->option == 'success') {
            return $this->initSuccess($this->partner_payment);
        }
        return false;
    }

    final function process()
    {
        if ($this->option == 'request') {
            return $this->processRequest();
        } elseif ($this->option == 'confirm-request') {
            return $this->processConfirmRequest();
        } elseif ($this->option == 'verify') {
            return $this->processVerify();
        } elseif ($this->option == 'confirm-verify') {
            return $this->processConfirmVerify();
        } elseif ($this->option == 'success') {
            return $this->processSuccess();
        }
        return false;
    }

    final protected function _setPartnerPayment()
    {
        $temp = strtolower($this->partner_payment_code);
        $temp = explode('-', $temp);
        $partner_payment_class = '\common\partner_payments\PartnerPayment';
        foreach ($temp as $item) {
            $partner_payment_class .= ucfirst($item);
        }
        $this->partner_payment = new $partner_payment_class($this);

    }

    public function isSubmit($partner_payment_code, $inputs)
    {
        if ($this->active && strtolower($this->partner_payment_code) == strtolower($partner_payment_code) && $this->load($inputs)) {
            return true;
        }
        return false;
    }

    public function beforeProcess()
    {
        /* $voucher_id = intval(Yii::$app->request->post('voucher_id'));
          if ($voucher_id != 0) {
          $voucher_id_number = intval(Yii::$app->request->post('voucher_id_number'));
          $voucher_mobile = intval(Yii::$app->request->post('voucher_mobile'));
          $voucher_fullname = intval(Yii::$app->request->post('voucher_fullname'));
          if (Voucher::checkVoucherId($voucher_id, $voucher_id_number, $voucher_mobile, $voucher_fullname, $account_info)) {

          } else {
          $this->error_message = 'Mã Voucher không hợp lệ';
          return false;
          }
          } */
        return true;
    }

    function submit()
    {
        if ($this->validate()) {
            if ($this->beforeProcess()) {
                $result = $this->process();
                if ($result != false) {
                    if ($result['error_message'] == '') {
                        header('Location:' . $result['payment_url']);
                        die();
                    } else {
                        $this->error_message = $result['error_message'];
                    }
                }
            }
        }
    }

    function getRequestActionForm()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/request', 'token_code' => $this->checkout_order['token_code'], 'payment_method_code' => $this->payment_method_code]);
    }

    function getRequestV2ActionForm()
    {
        return Yii::$app->urlManager->createUrl([Yii::$app->controller->id . '/request-v2', 'token_code' => $this->checkout_order['token_code'], 'payment_method_code' => $this->payment_method_code]);
    }

    function initRequest(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initRequest($this);
        return true;
    }

    function initConfirmRequest(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initConfirmRequest($this);
        return true;
    }

    function initVerify(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initVerify($this);
        return true;
    }

    function initConfirmVerify(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initConfirmVerify($this);
        return true;
    }

    function initSuccess(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initSuccess($this);
        return true;
    }

    public function rules()
    {
        if ($this->option == 'request') {
            return array(
                array(array('payment_method_id', 'partner_payment_id'), 'required', 'message' => 'Bạn phải chọn {attribute}.'),
                array(array('payment_method_id', 'partner_payment_id'), 'number'),
            );
        }
    }

    public function attributeLabels()
    {
        if ($this->option == 'request') {
            return [
                'payment_method_id' => 'Hình thức thanh toán',
                'partner_payment_id' => 'Kênh thanh toán',
            ];
        }
    }

    public function _getUrlConfirmRequest($payment_transaction_id)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/confirm-request', 'token_code' => $this->checkout_order['token_code'], 'transaction_checksum' => $this->_getTransactionChecksum($payment_transaction_id)], HTTP_CODE);
    }

    public function _getUrlVerify($payment_transaction_id)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/verify', 'token_code' => $this->checkout_order['token_code'], 'transaction_checksum' => $this->_getTransactionChecksum($payment_transaction_id)], HTTP_CODE);
    }

    public function _getUrlConfirmVerify($payment_transaction_id)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/confirm-verify', 'token_code' => $this->checkout_order['token_code'], 'transaction_checksum' => $this->_getTransactionChecksum($payment_transaction_id)], HTTP_CODE);
    }

    public function _getUrlSuccess($payment_transaction_id)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/success', 'token_code' => $this->checkout_order['token_code']], HTTP_CODE);
    }

    public function _getUrlReview($payment_transaction_id)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/review', 'token_code' => $this->checkout_order['token_code']], HTTP_CODE);
    }

    public function _getUrlCancel()
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/cancel', 'token_code' => $this->checkout_order['token_code']], HTTP_CODE);
    }

    public function _getUrlFailure()
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/failure', 'token_code' => $this->checkout_order['token_code']], HTTP_CODE);
    }

    public function _getUrlCallback($payment_method)
    {
        return Yii::$app->urlManager->createAbsoluteUrl('call-back/' . str_replace('_', '-', $payment_method), HTTP_CODE);
    }

    public function redirectErrorPage($error_message, $back_url = '')
    {
        $error_message = urlencode(base64_encode(base64_encode($error_message)));
        $back_url = $back_url != '' ? urlencode($back_url) : $back_url;
        $url = Yii::$app->urlManager->createAbsoluteUrl(['error/index', 'error_message' => $error_message, 'back_url' => $back_url], HTTP_CODE);
        header('Location:' . $url);
        die();
    }

    final public function _getTransactionChecksum($transaction_id)
    {
        return $transaction_id . '-' . substr(md5(md5($transaction_id . '@passw0rd')), -11);
    }

    final protected function _getSurname($fullname)
    {
        $fullname = Strings::_convertToSMS($fullname);
        $fullname = str_replace('  ', ' ', $fullname);
        return trim(substr($fullname, 0, strpos($fullname, ' ')));
    }

    final protected function _getForename($fullname)
    {
        $fullname = Strings::_convertToSMS($fullname);
        $fullname = str_replace('  ', ' ', $fullname);
        return trim(substr($fullname, strpos($fullname, ' ')));
    }

    public function processRequest($params = array())
    {
        $error_message = 'Lỗi không xác định';
        $payment_url = null;
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
    }

    public function getPartnerPaymentAmount($transaction_info)
    {
        return \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info);
    }

    public function getPaymentAmountByTransaction($transaction_info)
    {
        return $transaction_info['amount'] + $transaction_info['sender_fee'] + $transaction_info['partner_payment_sender_fee'];
    }

    public function getPaymentAmount()
    {
        if ($this->getPayerFee() != false) {
            return $this->checkout_order['amount'] + $this->getPayerFee();
        }
        return $this->checkout_order['amount'];
    }

    public function getPayerFee()
    {
        if ($this->payer_fee === null) {
            if ($this->payment_transaction != null) {
                return $this->payment_transaction['sender_fee'] + $this->payment_transaction['partner_payment_sender_fee'];
            } else {

                $this->merchant_fee_info = \common\models\db\MerchantFee::getPaymentFee($this->checkout_order['merchant_id'], $this->info['id'], $this->checkout_order['amount'], $this->checkout_order['currency'], time());
                if ($this->merchant_fee_info != false) {

                    $sender_fee = \common\models\db\MerchantFee::getSenderFee($this->merchant_fee_info, $this->checkout_order['amount']);
                    $partner_payment_fee_info = \common\models\db\PartnerPaymentFee::getPaymentFee($this->partner_payment_id, $this->checkout_order['merchant_id'], $this->info['id'], $this->checkout_order['amount'] + $sender_fee, $this->checkout_order['currency'], time());
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = \common\models\db\PartnerPaymentFee::getSenderFee($partner_payment_fee_info, $this->checkout_order['amount'] + $sender_fee);
                        $this->payer_fee = $partner_payment_sender_fee + $sender_fee;

                    } else {

                        $this->payer_fee = false;
                    }
                } else {
                    $this->payer_fee = false;
                }
            }
        }

        return $this->payer_fee;
    }

    public function getPaymentTransaction()
    {
        if (intval($this->checkout_order['transaction_id']) != 0) {
            return Tables::selectOneDataTable("transaction", ["id = :id", "id" => $this->checkout_order['transaction_id']]);
        }
        return false;
    }

    public function getVerify3D()
    {
        return $this->has_verify_3d;
    }

    public function addError($attribute, $error = '')
    {
        parent::addError($attribute, Translate::get($error));
    }

    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        return isset($labels[$attribute]) ? Translate::get($labels[$attribute]) : $this->generateAttributeLabel($attribute);
    }
}
