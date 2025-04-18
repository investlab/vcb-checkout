<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods\nganluong_seamless;

use common\payment_methods\PaymentMethodAtmCardForm;
use common\components\utils\Strings;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\TransactionBusiness;
use common\components\libs\Tables;
use Yii;
use common\components\utils\Translate;
use common\partner_payments\PartnerPaymentBasic;

class PaymentMethodAtmCardNganluongSeamlessForm extends PaymentMethodAtmCardForm {

    public $card_fullname = null;
    public $card_number = null;
    public $card_month = null;
    public $card_year = null;
    public $verifyCode = null;
    public $otp = null;
    public $mobile = null;
    public $identity_number = null;
    public $fields = array();

    public function rules() {
        if ($this->option == 'request') {

            $rules = array(
                array(array('payment_method_id', 'partner_payment_id'), 'required', 'message' => Translate::get('Quý khách vui lòng nhập {attribute}.')),
                array(array('payment_method_id', 'partner_payment_id'), 'number'),
                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không chính xác.')),
            );
            $this->_setRules($rules);
            return $rules;
        } elseif ($this->option == 'verify') {
            return array(
                array(array('otp'), 'required', 'message' => Translate::get('Quý khách vui lòng nhập {attribute}.')),
                array(array('otp'), 'isOTP'),
                array(array('verifyCode'), 'captcha', 'captchaAction' => 'version_1_0/captcha', 'message' => Translate::get('{attribute} không chính xác.')),
            );
        }
        return array();
    }

    public function attributeLabels() {
        return [
            'payment_method_id' => 'Ngân hàng để thanh toán',
            'partner_payment_id' => 'Kênh thanh toán',
            'card_fullname' => 'Tên chủ thẻ',
            'card_number' => 'Số thẻ ATM',
            'card_month' => 'Tháng trên thẻ',
            'card_year' => 'Năm trên thẻ',
            'mobile' => 'Số điện thoại chủ thẻ',
            'verifyCode' => 'Mã bảo mật',
            'otp' => 'Mã xác thực OTP',
            'identity_number' => 'Số căn cước/CCCD',
        ];
    }
    
    function initRequest(PartnerPaymentBasic &$partner_payment) {
        $fields = $partner_payment->initRequest($this);
        if ($fields !== false) {
            if (!empty($fields)) {
                $this->_setFieldsRequire($fields);
            } else {
                $result = $this->processRequest();
                if ($result['error_message'] == '') {
                    header('Location:'.$result['payment_url']);
                    die();
                } else {
                    $this->error_message = $result['error_message'];
                }
            }
        } else {
            $this->error_message = 'Có lỗi kết nối tới ngân hàng, vui lòng chọn hình thức thanh toán khác';
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
                        array('required', 'message' => Translate::get('Qúy khách vui lòng nhập {attribute}.')),
                        array('isCardNumber'),
                    )
                );
            case 'BANK_NAME':
                return array(
                    'name' => 'card_fullname', 
                    'rules' => array(
                        array('required', 'message' => Translate::get('Qúy khách vui lòng nhập {attribute}.')),
                        array('isCardHolderName'),
                    )
                );
            case 'ISSUE_MONTH':
                return array(
                    'name' => 'card_month', 
                    'rules' => array(
                        array('required', 'message' => Translate::get('Qúy khách vui lòng chọn {attribute}.')),
                        array('isIssueCardMonth'),
                    )
                );
            case 'ISSUE_YEAR':
                return array(
                    'name' => 'card_year', 
                    'rules' => array(
                        array('required', 'message' => Translate::get('Qúy khách vui lòng chọn {attribute}.')),
                        array('isIssueCardYear'),
                    )
                );
            case 'EXPIRED_MONTH':
                return array(
                    'name' => 'card_month', 
                    'rules' => array(
                        array('required', 'message' => Translate::get('Qúy khách vui lòng chọn {attribute}.')),
                        array('isExpiredCardMonth'),
                    )
                );
            case 'EXPIRED_YEAR':
                return array(
                    'name' => 'card_year', 
                    'rules' => array(
                        array('required', 'message' => Translate::get('Qúy khách vui lòng chọn {attribute}.')),
                        array('isExpiredCardYear'),
                    )
                );
            case 'MOBILE':
                return array(
                    'name' => 'mobile',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
                        //array('isCardOwnerPhone'),
                    )
                );
            case 'IDENTITY_NUMBER':
                return array(
                    'name' => 'identity_number',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                        array('isIdentityNumber'),
                    )
                );
        }
        return false;
    }

    public function isCardHolderName($attribute, $params) {
        $name = Strings::_convertToSMS(trim($this->$attribute));
        if (!preg_match('/^[\w\s]+$/', $name)) {
            $this->addError($attribute, 'Tên chủ thẻ không hợp lệ');
        }
    }

    public function isCardNumber($attribute, $params) {
        if (!preg_match('/^\d{10,19}$/', $this->$attribute)) {
            $this->addError($attribute, Translate::get('Số thẻ/tài khoản không hợp lệ'));
        }
    }

    public function isIssueCardMonth($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getCardMonths())) {
            $this->addError($attribute, 'Tháng phát hành thẻ không hợp lệ');
        } else {
            if ($this->card_year == date('Y') && $this->card_month > intval(date('m'))) {
                $this->addError($attribute, 'Tháng phát hành thẻ không hợp lệ');
            }
        }
    }

    public function isIssueCardYear($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getIssueCardYears())) {
            $this->addError($attribute, 'Năm phát hành thẻ không hợp lệ');
        }
    }
    
    public function isExpiredCardMonth($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getCardMonths())) {
            $this->addError($attribute, 'Tháng hết hạn thẻ không hợp lệ');
        } else {
            if ($this->card_year == date('Y') && $this->card_month < intval(date('m'))) {
                $this->addError($attribute, 'Tháng hết hạn thẻ không hợp lệ');
            }
        }
    }

    public function isExpiredCardYear($attribute, $params) {
        $value = intval($this->$attribute);
        if (!array_key_exists($value, $this->getExpiredCardYears())) {
            $this->addError($attribute, 'Năm hết hạn thẻ không hợp lệ');
        }
    }

    public function isOTP($attribute, $params) {
        $value = trim($this->$attribute);
        if (!preg_match('/^[a-zA-Z0-9]{6,8}$/', $value)) {
            $this->addError($attribute, 'Mã xác thực OTP không hợp lệ');
            return false;
        }
    }

    public function isIdentityNumber($attribute, $params)
    {
        $value = trim($this->$attribute);
        if (!preg_match('/^[0-9]{9,12}$/', $value)) {
            $this->addError($attribute, 'Số căn cước/CCCD không hợp lệ');
            return false;
        }
    }

    public function getIssueCardYears() {
        $card_years = array('' => '----');
        $year = date('Y');
        for ($i = $year - 10; $i <= $year; $i++) {
            $card_years[$i] = $i;
        }
        return $card_years;
    }
    
    public function getExpiredCardYears() {
        $card_years = array('' => '----');
        $year = date('Y');
        for ($i = $year; $i < ($year + 10); $i++) {
            $card_years[$i] = $i;
        }
        return $card_years;
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

    public function processRequest($params = array()) {
        $error_message = 'Lỗi không xác định';
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
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
                $card_fullname = Strings::_convertToSMS(trim($this->card_fullname));
                $card_fullname = strtoupper($card_fullname);
                //------------
                if ($this->mobile != '')
                {
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'transaction_amount' => \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info),
                        'transaction_info' => $transaction_info,
                        'card_fullname' => $card_fullname,
                        'card_number' => $this->card_number,
                        'card_month' => substr('0' . $this->card_month, -2),
                        'card_year' => substr('0' . $this->card_year, -2),
                        'mobile' => $this->mobile,
                    );
                }else{
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'transaction_amount' => \common\models\db\Transaction::getPartnerPaymentAmount($transaction_info),
                        'transaction_info' => $transaction_info,
                        'card_fullname' => $card_fullname,
                        'card_number' => $this->card_number,
                        'card_month' => substr('0' . $this->card_month, -2),
                        'card_year' => substr('0' . $this->card_year, -2),
                    );
                }

                if(!is_null($this->identity_number) && $this->identity_number !== ''){
                    $inputs['identity_number'] = $this->identity_number;
                }

                $result = $this->partner_payment->processRequest($this, $inputs);
                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => $result['response']['token'],
                        'partner_payment_info' => json_encode($result['response']),
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paying($inputs);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = $result['error_message'];
                }
            }
        } else {
            $error_message = $result['error_message'];
        }
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
    }

    public function processVerify($params = array()) {
        $error_message = 'Lỗi không xác định';
        $payment_url = null;
        //--------        
        $result = $this->partner_payment->processVerify($this, $params);
        if ($result['error_message'] === '') {
            $inputs = array(
                'transaction_id' => $this->payment_transaction['id'],
                'time_paid' => time(),
                'bank_refer_code' => $result['bank_refer_code'],
                'partner_code' => @$result['partner_code'],

                'user_id' => 0,
            );
            $result = TransactionBusiness::paid($inputs);
            if ($result['error_message'] === '') {
                $error_message = '';
                $payment_url = $this->_getUrlSuccess($this->payment_transaction['id']);
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        return array('error_message' => $error_message, 'payment_url' => $payment_url);
    }

}
