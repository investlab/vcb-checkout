<?php

namespace common\payment_methods\cyber_source_vcb;

use common\components\libs\Weblib;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\db\BinAccept;
use common\models\db\Merchant;
use common\models\db\Transaction;
use common\partner_payments\PartnerPaymentCyberSource;
use common\payment_methods\PaymentMethodBasicForm;
use common\payment_methods\PaymentMethodCreditCardForm;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\TransactionBusiness;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\components\utils\Strings;
use common\models\db\TransactionType;
use common\payments\CyberSource;
use Yii;
use common\components\utils\ObjInput;
use common\models\business\CheckoutOrderBusiness;

class PaymentMethodCreditCardCyberSourceVcbForm extends PaymentMethodCreditCardForm
{
    public $card_type = null;
    public $verifyCode = null;
    public $payment_method_id = null;
    public $partner_payment_id = null;
    public $otp = null;
    public $card_number = null;
    public $card_fullname = null;
    public $card_month = null;
    public $card_year = null;
    public $card_cvv = null;

    public function rules() {
        if ($this->option == 'request') {
            $rules = array(
                array(array('card_number', 'card_fullname', 'card_cvv'), 'required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                array(array('payment_method_id', 'partner_payment_id', 'card_month', 'card_year'), 'required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                array(array('card_number'), 'isCardNumber'),
                array(array('card_fullname'), 'isCardHolderName'),
                array(array('card_month'), 'isExpiredCardMonth'),
                array(array('card_year'), 'isExpiredCardYear'),
                array(array('card_cvv'), 'isCardcvv'),
                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không đúng.')),
            );
            return $rules;
        } elseif ($this->option == 'verify') {
            return array(
                array(array('otp'), 'required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                array(array('otp'), 'isOTP'),
                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không đúng.')),
            );
        }
        return array();
    }

    public function attributeLabels() {
        return [
            'payment_method_id' => Translate::get('Ngân hàng để thanh toán'),
            'partner_payment_id' => Translate::get('Kênh thanh toán'),
            'verifyCode' => Translate::get('Captcha'),
            'otp' => Translate::get('Mã OTP'),
            'card_cvv' => Translate::get('Mã CVV'),
            'card_number' => Translate::get('Mã số thẻ'),
            'card_fullname' => Translate::get('Tên chủ thẻ'),
            'card_month' => Translate::get('Tháng'),
            'card_year' => Translate::get('Năm'),
        ];
    }

    function initRequest(PartnerPaymentBasic &$partner_payment) {
        return true;
    }

    public function processRequest($params = array()) {
        $error_message = Translate::get('Lỗi không xác định');
        $payment_url = null;
        //--------
        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = $this->partner_payment->getTransaction($transaction_id);
            if ($transaction_info != false) {
                $cardInfo = [
                    'card_fullname' => $this->card_fullname,
                    'card_number' => Strings::encodeCreditCardNumber($this->card_number),
                    'card_month' => $this->card_month,
                    'card_year' => $this->card_year,
                ];
                Transaction::insertCardInfo($transaction_id, $cardInfo);
                $this->setCardType();
                $params = [
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                ];
                $result = $this->partner_payment->processRequest($this, $params);
                if ($result['error_message'] == '') {
                    $bank_trans_id = $result['bank_trans_id'];
                    $xid = $result['xid'];
                    $result_code = $result['result_code'];
                    if ($result_code == 'ACCEPT') {
                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => json_encode($result),
                            'user_id' => 0,
                        );
                        $result = TransactionBusiness::paying($inputs);
                        if ($result['error_message'] == '') {
                            $inputs = array(
                                'transaction_id' => $transaction_id,
                                'time_paid' => time(),
                                'bank_refer_code' => $bank_trans_id,
                                'user_id' => 0,
                            );
                            $result = TransactionBusiness::paid($inputs);
                            if ($result['error_message'] === '') {
                                $error_message = '';
                                $payment_url = $this->_getUrlSuccess($transaction_id);
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        } else {
                            $error_message = Translate::get($result['error_message']);
                        }
                    } elseif ($result_code == 'REVIEW') {
                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => json_encode($result),
                            'user_id' => 0,
                        );
                        $result = TransactionBusiness::paying($inputs);
                        if ($result['error_message'] == '') {
                            $inputs = array(
                                'transaction_id' => $transaction_id,
                                'time_paid' => time(),
                                'bank_refer_code' => $bank_trans_id,
                                'user_id' => 0,
                            );
                            $result = TransactionBusiness::updateReview($inputs);
                            if ($result['error_message'] === '') {
                                $error_message = '';
                                $payment_url = $this->_getUrlReview($transaction_id);
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        } else {
                            $error_message = Translate::get($result['error_message']);
                        }
                    } elseif ($result_code == '3D') {
                        $inputs = array(
                            'transaction_id' => $transaction_id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => json_encode($result),
                            'user_id' => 0,
                        );
                        $result = TransactionBusiness::paying($inputs);
                        if ($result['error_message'] == '') {
                            $error_message = '';
                            $payment_url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/verify',
                                'token_code' => $this->checkout_order['token_code'],
                                'transaction_checksum' => $this->_getTransactionChecksum($transaction_id),
                                'xid' => $xid], HTTP_CODE);
                        } else {
                            $error_message = Translate::get($result['error_message']);
                        }
                    } else {
                        $error_message = Translate::get('Lỗi không xác định');
                    }
                } else {
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'reason_id' => @$result['reasonCode'],
                        'reason' => $result['error_message'],
                        'user_id' => 0,
                    );
                    $cancel = TransactionBusiness::cancel($inputs);
                    $error_message = Translate::get($result['error_message']);
                    $reason_code_allow = [202, 203, 204, 208, 209, 210, 211, 231, 233, 251];
                    if (!in_array($result['reasonCode'], $reason_code_allow)) {


                        if ($cancel) {
                            $inputs = array(
                                'checkout_order_id' => $this->checkout_order['id'],
                                'user_id' => '0',
                            );
                            $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                            if ($result['error_message'] === '') {
                                $inputs_callback = [
                                    'checkout_order_id' => $this->checkout_order['id'],
                                    'notify_url' => $this->checkout_order['notify_url'],
                                    'time_process' => time(),
                                ];
                                if ($this->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
                                    $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                                }
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $payment_url = $this->_getUrlFailure();
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = Translate::get($result['error_message']);
                            }
                        }

                    } else {
                        $error_message = Translate::get($result['error_message']);
                    }

                }
            }
        } else {
            $error_message = Translate::get($result['error_message']);
        }
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
    }

    function initVerify(PartnerPaymentBasic &$partner_payment)
    {
        $partner_payment->initVerify($this);
        return true;
    }

    function processVerify() {
        return false;
    }

    private function setCardType() {
        switch($this->payment_method_code) {
            case 'VISA-CREDIT-CARD':
                $this->card_type = 'visa';
                break;
            case 'MASTERCARD-CREDIT-CARD':
                $this->card_type = 'mastercard';
                break;
            case 'JCB-CREDIT-CARD':
                $this->card_type = 'jcb';
                break;
            case 'AMEX-CREDIT-CARD':
                $this->card_type = 'americanexpress';
                break;
        }
    }

    public function isExpiredCardMonth($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getCardMonths())) {
            $this->addError($attribute, Translate::get('Tháng hết hạn thẻ không hợp lệ'));
        } else {
            if ($this->card_year == date('Y') && $this->card_month < intval(date('m'))) {
                $this->addError($attribute,  Translate::get('Tháng hết hạn thẻ không hợp lệ'));
            }
        }
    }

    public function isExpiredCardYear($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getExpiredCardYears())) {
            $this->addError($attribute, Translate::get('Năm hết hạn thẻ không hợp lệ'));
        }
    }

    public function isCardNumber($attribute, $params) {
        $this->$attribute = str_replace(' ', '', $this->$attribute);
        if (!preg_match('/^\d{15,19}$/', $this->$attribute)) {
            $this->addError($attribute, Translate::get('Số thẻ không hợp lệ'));
        }
    }

    public function isCardHolderName($attribute, $params) {
        $name = strtoupper(Strings::_convertToSMS(trim($this->$attribute)));
        if (!preg_match('/^[\w\s]+$/', $name)) {
            $this->addError($attribute,  Translate::get('Tên chủ thẻ không hợp lệ'));
        }
    }

    public function isCardcvv($attribute, $params) {
        $length = strlen(Strings::_convertToSMS(trim($this->$attribute)));
        if ($length < 3 || $length > 4) {
            $this->addError($attribute,  Translate::get('Mã cvv không hợp lệ'));
        }
    }

    public function getCardMonths() {
        $card_months = array(
            '' => '----',
            '1' => Translate::get('Tháng 1'),
            '2' => Translate::get('Tháng 2'),
            '3' => Translate::get('Tháng 3'),
            '4' => Translate::get('Tháng 4'),
            '5' => Translate::get('Tháng 5'),
            '6' => Translate::get('Tháng 6'),
            '7' => Translate::get('Tháng 7'),
            '8' => Translate::get('Tháng 8'),
            '9' => Translate::get('Tháng 9'),
            '10' => Translate::get('Tháng 10'),
            '11' => Translate::get('Tháng 11'),
            '12' => Translate::get('Tháng 12')
        );
        return $card_months;
    }

    public function getExpiredCardYears() {
        $card_years = array('' => '----');
        $year = date('Y');
        for ($i = $year; $i < ($year + 10); $i++) {
            $card_years[$i] = $i;
        }
        return $card_years;
    }

}
