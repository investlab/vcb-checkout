<?php

/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 10/24/2019
 * Time: 2:22 PM
 */

namespace common\payment_methods\alepay;

use common\components\libs\Weblib;
use common\payment_methods\PaymentMethodTraGopForm;
use common\payment_methods\PaymentMethodAtmCardForm;
use common\partner_payments\PartnerPaymentBasic;
use common\models\business\TransactionBusiness;
use common\models\business\CheckoutOrderBusiness;
use common\components\libs\Tables;
use common\components\utils\Translate;

use common\components\utils\Strings;
use common\models\db\TransactionType;
use Yii;
use yii\web\View;


class PaymentMethodTraGopAlepayForm extends PaymentMethodTraGopForm {

    public $cycle_installment = null;
    public $card_type = null;
    public $card_info = null;
    public $fields = array();
    public $verifyCode = null;

    public function rules() {
        if ($this->option == 'request') {
            $rules = array(
                array(array('card_info'), 'required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
//                array(array('cycle_installment'), 'isCycleInstallment'),
                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không đúng.')),
            );
            $this->_setRules($rules);
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
            'cycle_installment' => 'Ký trả góp',
            'card_type' => 'Loại thẻ',
        ];
    }

    function initRequest(PartnerPaymentBasic &$partner_payment) {

        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
            'transaction_type_id' => 5
        );
        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
                //------------
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => $this->getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                );

                if (isset($_POST['card_info'])) {
                    $bank_code = str_replace('-TRA-GOP', '', $this->info['code']);
                    $params = [
                        'merchant_id' => $this->checkout_order['merchant_id'],
                        'partner_payment_code' => $this->partner_payment_code,
                        'currency' => $this->checkout_order['currency'],
                        'bank_code' => $bank_code,
                        'amount' => $this->checkout_order ["amount"]
                    ];
                    $card_accept = $this->partner_payment->getCardAccept($this,$params);
                    $card_input = $_POST['card_info'];
                    if (!in_array($card_input, $card_accept)) {
                        $url = Yii::$app->urlManager->createAbsoluteUrl($this->enviroment . '/index/' . $this->checkout_order['token_code']);
                        Weblib::showMessage('Không tìm thấy cấu hình trả góp phù hợp', $url);
                    }
                }

                $result = $this->partner_payment->processRequest($this, $inputs);

                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => '',
                        'partner_payment_info' => json_encode($result['response']),
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paying($inputs);


                    if ($result['error_message'] == '') {
                        header('Location:' . $payment_url);
                        die();
                    } else {
                        $this->error_message = $result['error_message'];
                    }
                } else {
                    $this->error_message = $result['error_message'];
                }
            }
        } else {
            $this->error_message = $result['error_message'];
        }
        return true;
    }

    protected function _setRules(&$rules) {
        if (!empty($this->fields)) {
            foreach ($this->fields as $field) {
                if (!empty($field['rules'])) {
                    foreach ($field['rules'] as $rule) {
                        $new_rule = array($field['name']);
                        foreach ($rule as $key => $value) {
                            if (is_numeric($key)) {
                                $new_rule[] = $value;
                            } else {
                                $new_rule[$key] = $value;
                            }
                        }
                        $rules[] = $new_rule;
                    }
                }
            }
        }
    }

    protected function _setFieldsRequire($fields) {
        if (is_array($fields)) {
            foreach ($fields as $field_code) {
                $this->fields[$field_code] = $this->_getFieldByCode($field_code);
            }
        } else {
            $this->fields[$fields] = $this->_getFieldByCode($fields);
        }
    }

    protected function _getFieldByCode($field_code) {
        switch ($field_code) {
            case 'BANK_ACCOUNT':
                return array(
                    'name' => 'card_number',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                        array('isCardNumber'),
                    )
                );
            case 'BANK_NAME':
                return array(
                    'name' => 'card_fullname',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                        array('isCardHolderName'),
                    )
                );
            case 'ISSUE_MONTH':
                return array(
                    'name' => 'card_month',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                        array('isIssueCardMonth'),
                    )
                );
            case 'ISSUE_YEAR':
                return array(
                    'name' => 'card_year',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                        array('isIssueCardYear'),
                    )
                );
            case 'EXPIRED_MONTH':
                return array(
                    'name' => 'card_month',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                        array('isExpiredCardMonth'),
                    )
                );
            case 'EXPIRED_YEAR':
                return array(
                    'name' => 'card_year',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                        array('isExpiredCardYear'),
                    )
                );
        }
        return false;
    }



    public function isCycleInstallment($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getCycleInstallment())) {
            $this->addError($attribute, 'Chu kỳ trả góp không hợp lệ');
        }
    }



    public function getCardInfo($method_code,$card_type) {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this,$inputs);
        $card_info = array();
        foreach ($card_accept as $item => $value){
            $arrCard = explode('-',$value);
            if (in_array($card_type,$arrCard)){
                $card_info[$card_type][$arrCard[1]] = $value;
            }
        }
        return $card_info;
    }


    public function getBank ($method_code) {
        $bank_code = str_replace('-TRA-GOP', '', $method_code);
        $inputs = [
            'merchant_id' => $this->checkout_order['merchant_id'],
            'partner_payment_code' => $this->partner_payment_code,
            'currency' => $this->checkout_order['currency'],
            'bank_code' => $bank_code,
            'amount' => $this->checkout_order ["amount"]
        ];
        $card_accept = $this->partner_payment->getCardAccept($this,$inputs);
        $bank = array();
        foreach ($card_accept as $item => $value){
            $bank_item = explode('-',$value);
            array_push($bank,$bank_item[0]);
        }
        return array_unique($bank);
    }





}
