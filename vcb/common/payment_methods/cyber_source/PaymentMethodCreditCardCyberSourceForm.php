<?php

namespace common\payment_methods\cyber_source;

use common\components\libs\Weblib;
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

class PaymentMethodCreditCardCyberSourceForm extends PaymentMethodCreditCardForm
{
    public $card_type = null;
    public $fields = array();
    public $verifyCode = null;
    public $payment_method_id = null;
    public $partner_payment_id = null;
    public $otp = null;
    public $card_number = null;
    public $card_fullname = null;
    public $card_month = null;
    public $card_year = null;
    public $card_cvv = null;
    public $flex_key = null;

    public function rules() {
        if ($this->option == 'request') {
            $rules = array(
                array(array('payment_method_id', 'partner_payment_id'), 'required', 'message' => Translate::get('Bạn phải chọn {attribute}.')),
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
            'payment_method_id' => 'Ngân hàng để thanh toán',
            'partner_payment_id' => 'Kênh thanh toán',
            'verifyCode' => 'Mã xác nhận',
            'otp' => 'Mã OTP',
            'card_cvv' => 'Mã CVV',
            'card_number' => 'Mã số thẻ',
            'card_fullname' => 'Tên chủ thẻ',
            'card_month' => 'Tháng',
            'card_year' => 'Năm',
        ];
    }

    function initRequest(PartnerPaymentBasic &$partner_payment) {
        $fields = $partner_payment->initRequest($this);
        if ($fields !== false) {
            if (!empty($fields)) {
                if (!empty($fields['error_message'])) {
                    $this->error_message = $fields['error_message'];
                } else {
                    $this->_setFieldsRequire($fields);
                }
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
            $transaction_info = $this->partner_payment->getTransaction($transaction_id);
            if ($transaction_info != false) {
                $flex_response = ObjInput::get('flex-response', 'def', '');
                $flex_response = json_decode($flex_response, true);
                $params = [
                    'transaction_id' => $transaction_id,
                    'flex_response' => $flex_response,
                ];
                $result = $this->partner_payment->processRequest($this, $params);

                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => '',
                        'partner_payment_info' => json_encode($result),
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

    public function setReceiverId($receiver_id) {
        $this->receiverId = $receiver_id;
    }

    private function _convertName($content) {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    protected function _processCardFullname($fullname, &$first_name = '', &$last_name = '') {
        $fullname = trim($fullname);
        $pos = strrpos($fullname, ' ');
        if ($pos !== false) {
            $first_name = trim(substr($fullname, $pos));
            $last_name = trim(substr($fullname, 0, $pos));
        } else {
            $first_name = $fullname;
            $last_name = '';
        }
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

            case 'BANK_NAME':
                return array(
                    'name' => 'card_fullname',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                        array('isCardHolderName'),
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
            case 'CARD_CVV':
                return array(
                    'name' => 'card_cvv',
                    'rules' => array(
                        array('required', 'message' => Translate::get('Bạn phải nhập {attribute}.')),
                        array('isCardcvv'),
                    )
                );
        }
        return false;
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

    public function isCardNumber($attribute, $params) {
        $this->$attribute = str_replace(' ', '', $this->$attribute);
        if (!preg_match('/^\d{16,19}$/', $this->$attribute)) {
            $this->addError($attribute, 'Số thẻ không hợp lệ');
        }
    }

    public function isCardHolderName($attribute, $params) {
        $name = Strings::_convertToSMS(trim($this->$attribute));
        if (!preg_match('/^[\w\s]+$/', $name)) {
            $this->addError($attribute, 'Tên chủ thẻ không hợp lệ');
        }
    }

    public function isCardcvv($attribute, $params) {
        $length = strlen(Strings::_convertToSMS(trim($this->$attribute)));
        if ($length < 3 || $length > 4) {
            $this->addError($attribute, 'Mã cvv không hợp lệ');
        }
    }
}
