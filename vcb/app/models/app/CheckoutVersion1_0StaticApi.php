<?php

namespace app\models\app;

use common\models\business\PaymentMethodBusiness;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PartnerPaymentFee;
use common\models\db\PaymentMethod;
use Yii;
use common\models\db\Merchant;
use common\components\utils\Validation;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
class CheckoutVersion1_0StaticApi  {

    public static function getVersion() {
        return ObjInput::get('version', 'str', '1.0');
    }

    protected function _isFunction($function) {
        return ($function == 'CreateOrder' || $function == 'CheckOrder' || $function == 'GetBanks');
    }

    public function getData($function) {
        if ($function == 'CreateOrder') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['order_code'] = ObjInput::get('order_code', 'str', '');
            $data['order_description'] = ObjInput::get('order_description', 'str', '');
            $data['amount'] = ObjInput::get('amount', 'str', '');
            $data['currency'] = ObjInput::get('currency', 'str', '');
            $data['buyer_fullname'] = ObjInput::get('buyer_fullname', 'str', '');
            $data['buyer_email'] = ObjInput::get('buyer_email', 'str', '');
            $data['buyer_mobile'] = ObjInput::get('buyer_mobile', 'str', '');
            $data['buyer_address'] = ObjInput::get('buyer_address', 'str', '');
            $data['return_url'] = ObjInput::get('return_url', 'str', '');
            $data['cancel_url'] = ObjInput::get('cancel_url', 'str', '');
            $data['notify_url'] = ObjInput::get('notify_url', 'str', '');
            $data['time_limit'] = ObjInput::get('time_limit', 'str', date('c', time() + 7 * 86400));
            $data['language'] = ObjInput::get('language', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            $data['object_code'] = ObjInput::get('object_code', 'str', '');
            $data['object_name'] = ObjInput::get('object_name', 'str', '');
            if (in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ON_SEAMLESS'])) {
                $data['payment_method_code'] = ObjInput::get('payment_method_code', 'str', '');
                $data['bank_code'] = ObjInput::get('bank_code', 'str', '');
            }

            return $data;
        } elseif ($function == 'CheckOrder') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['token_code'] = ObjInput::get('token_code', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == 'GetBanks') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
             $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code) {
        $message = array(
            '0000' => 'Success', '0001' => 'Undefined Error', '0002' => 'Invalid Function name', '0003' => 'Invalid merchant_site_code ', '0004' => 'Invalid version', '0005' => 'Invalid order_code', '0006' => 'Invalid order_description', '0007' => 'Invalid amount format', '0008' => 'Invalid currency', '0009' => 'Invalid buyer_fullname', '0010' => 'Invalid buyer_email', '0011' => 'Invalid buyer_mobile', '0012' => 'Invalid buyer_address', '0013' => 'Invalid return_url', '0014' => 'Invalid cancel_url', '0015' => 'Invalid notify_url', '0016' => 'Invalid time_limit', '0017' => 'Invalid checksum', '0018' => 'Invalid token_code', '0101' => 'Request params are ok, but could not create the order for this merchant.','0020' => 'Can\'t create an order for this merchant',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    /**
     *
     * @param type $params : merchant_site_code, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, language
     * @return type
     */
    public static function _createOrder($params) {
        $code = '01';
        $response = '';
        $inputs = array(
            'version' => self::getVersion(),
            'language_id' => self::_getLanguageId($params['language']),
            'merchant_id' => $params['merchant_site_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'amount' => $params['amount'],
            'orginal_amount' => $params['amount'],
            'currency' => $params['currency'],
            'return_url' => $params['return_url'],
            'cancel_url' => $params['cancel_url'],
            'notify_url' => $params['notify_url'],
            'time_limit' => strtotime($params['time_limit']),
            'buyer_fullname' => $params['buyer_fullname'],
            'buyer_mobile' => $params['buyer_mobile'],
            'buyer_email' => $params['buyer_email'],
            'buyer_address' => $params['buyer_address'],
            'object_code' => $params['object_code'],
            'object_name' => $params['object_name'],
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::add($inputs);
        if ($result['error_message'] === '') {
            $token_code = $result['token_code'];
            $id = $result['id'];
            $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($params['payment_method_code'], 'version_1_0');
            if ($payment_method_info != false) {
                //var_dump($payment_method_info);
                $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
                //echo $model_payment_method_name.'<br>';
                if (class_exists($model_payment_method_name)) {
                    $model = new $model_payment_method_name();
                    $model->set($params['amount'],'version_1_0', 'request_seamless', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                    $model->active = true;
                    $model->checkout_order = [
                        'id' => $id,
                        'merchant_id' => $params['merchant_site_code'],
                        'amount' => $params['amount'],
                        'currency' => $params['currency'],
                        'order_code' => $params['order_code'],
                        'token_code' => $token_code,
                        'buyer_fullname' => $params['buyer_fullname'],
                        'buyer_mobile' => $params['buyer_mobile'],
                        'buyer_email' => $params['buyer_email'],
                        'buyer_address' => $params['buyer_address'],
                    ];

                    if ($model->getPayerFee() !== false) {
                        $params_load = [
                            'token_code' => $token_code,
                            'payment_method_code' => $params['payment_method_code'],
                            'location' => 'vi',
                        ];
                        $model->load($params_load);

                        $result = $model->initOption();
                        //var_dump($model->checkout_order);
                        if ( $result['error_message'] == '') {
                            $code = 0;
                            $message = 'Thành công';
                            $response = $result['response'];
                            $response['token_code'] = $token_code ;
                        }else{
                            $code = '0007';
                            $message = $result['error_message'];
                        }
                        // $data_qr = $this->checkout_order['qrcode'];
                    } else {
                        $code = '0005';
                        //die('Chưa cấu hình phí cho phương thức thanh toán này');
                        $message = 'Chưa cấu hình phí cho phương thức thanh toán này';
                    }
                }
            } else {
                $code = '0004';
                $message = 'Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng';
                //$this->redirectErrorPage('Thông tin thanh toán không hợp lệ sai phương thức thanh toán hoặc mã ngân hàng');
            }
        } else {
            $error_code = '0101';
            $error_message = 'Không thể tạo đơn hàng';
        }

        return array('error_code' => (int)$code,'error_message' => $message, 'response' => $response );
    }

    /**
     * @param $params
     * @return type boolean(true/false)
     */
    protected static function checkAmountPaymentMethod($params) {
        if (!empty($params['payment_method_code']) && !empty($params['bank_code'])) {
            $payment_method = CheckoutOrder::getPaymentMethod($params);
            $method_code = CheckoutOrder::getMethodCode($params['payment_method_code']);

            $method_check = Method::find()->select('method.id, merchant_fee.min_amount as merchant_min_amount, 
                         partner_payment_fee.min_amount as partner_min_amount')
                ->leftJoin('merchant_fee', 'merchant_fee.method_id=method.id')
                ->leftJoin('partner_payment_fee', 'partner_payment_fee.method_id=method.id')
                ->where(['method.code' => $method_code, 'method.status' => Method::STATUS_ACTIVE,
                    'merchant_fee.status' => MerchantFee::STATUS_ACTIVE, 'partner_payment_fee.status' => PartnerPaymentFee::STATUS_ACTIVE])
                ->orderBy('merchant_fee.min_amount DESC, partner_payment_fee.min_amount DESC')->asArray()->one();

            $payment_check = PaymentMethod::find()
                ->select('id, code, min_amount')
                ->where(['code' => $payment_method, 'status' => PaymentMethod::STATUS_ACTIVE])
                ->orderBy('min_amount DESC')->asArray()->one();


            // Check số tiền tối thiểu qua payment_method
            $flag_payment_amount = ($params['amount'] >= $payment_check['min_amount']);
            $flag_merchant_amount = ($params['amount'] >= $method_check['merchant_min_amount']);
            $flag_partner_amount = ($params['amount'] >= $method_check['partner_min_amount']);

            if ($flag_payment_amount && $flag_merchant_amount && $flag_partner_amount) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     *
     * @param type $params : merchant_site_code, token_code, checkout_order_info, checksum
     * @return type
     */
    protected function _checkOrder($params) {
        $error_code = '0001';
        $result_data = null;
        //-------------
        $error_code = '0000';
        $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);
        return array('error_code' => (int)$error_code, 'result_data' => $result_data);
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, language, checksum
     */
    protected function _validateDataCreateOrder(&$data) {

        $error_code = '0001';

        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($data['merchant_site_code'] == 25 || $data['merchant_site_code'] == 24){
                $error_code = '0020';
            }else{
                if ($this->_validateChecksumCreateOrder($data, $api_key)) {
                    if ($this->_validateOrderCode($data['order_code'])) {
                        if ($this->_validateOrderDescription($data['order_description'])) {
                            if ($this->_validateAmount($data['amount'])) {
                                if ($this->_validateCurrency($data['currency'])) {
                                    if ($this->_validateBuyerFullname($data['buyer_fullname'])) {
                                        $merchant= Merchant::findOne($data['merchant_site_code']);
                                        if ($merchant->email_requirement==0){
                                            if ($data['buyer_email']==''||$data['buyer_email']==null){
                                                $data['buyer_email'] = 'notrequired@nganluong.vn';
                                            }
                                        }
                                        if ($this->_validateBuyerEmail($data['buyer_email'])) {
                                            if ($this->_validateBuyerMobile($data['buyer_mobile'])) {
                                                if ($this->_validateBuyerAddress($data['buyer_address'])) {
                                                    if ($this->_validateReturnUrl($data['return_url'])) {
                                                        if ($this->_validateCancelUrl($data['cancel_url'])) {
                                                            if ($this->_validateNotifyUrl($data['notify_url'])) {
                                                                if ($this->_validateTimeLimit($data['time_limit'])) {
                                                                    $error_code = '0000';
                                                                } else {
                                                                    $error_code = '0016';
                                                                }
                                                            } else {
                                                                $error_code = '0015';
                                                            }
                                                        } else {
                                                            $error_code = '0014';
                                                        }
                                                    } else {
                                                        $error_code = '0013';
                                                    }
                                                } else {
                                                    $error_code = '0012';
                                                }
                                            } else {
                                                $error_code = '0011';
                                            }
                                        } else {
                                            $error_code = '0010';
                                        }
                                    } else {
                                        $error_code = '0009';
                                    }
                                } else {
                                    $error_code = '0008';
                                }
                            } else {
                                $error_code = '0007';
                            }
                        } else {
                            $error_code = '0006';
                        }
                    } else {
                        $error_code = '0005';
                    }
                } else {
                    $error_code = '0017';
                }
            }

        } else {
            $error_code = '0003';
        }
        return array('error_code' => (int)$error_code);
    }

    protected function _validateChecksumCreateOrder($data, $api_key) {
//        $merchant= Merchant::findOne($data['merchant_site_code']);
//        if ($merchant->email_requirement==0){
//            if ($data['buyer_email']==''){
//                $data['buyer_email'] = 'not-required@not.not';
//            }
//        }
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $data['order_description'];
        $str_checksum .= '|' . $data['amount'];
        $str_checksum .= '|' . $data['currency'];
        $str_checksum .= '|' . $data['buyer_fullname'];
        $str_checksum .= '|' . $data['buyer_email'];
        $str_checksum .= '|' . $data['buyer_mobile'];
        $str_checksum .= '|' . $data['buyer_address'];
        $str_checksum .= '|' . $data['return_url'];
        $str_checksum .= '|' . $data['cancel_url'];
        $str_checksum .= '|' . $data['notify_url'];
        //$str_checksum .= '|' . $data['time_limit'];
        $str_checksum .= '|' . $data['language'];
        $str_checksum .= '|' . $api_key;
        //echo($str_checksum).'<br>';
        //echo md5($str_checksum);
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== '. md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, checksum
     */
    protected function _validateDataCheckOrder(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumCheckOrder($data, $api_key)) {
                if ($this->_validateTokenCode($data['token_code'], $checkout_order_info)) {
                    if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                        $data['checkout_order_info'] = $checkout_order_info;
                        $error_code = '0000';
                    } else {
                        $error_code = '0018';
                    }
                } else {
                    $error_code = '0018';
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => (int)$error_code);
    }

    protected function _validateChecksumCheckOrder($data, $api_key) {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $api_key;
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    protected static function _getLanguageId($language) {
        if ($language == 'en') {
            return 2;
        } else {
            return 1;
        }
    }

    protected function _getBanks($params) {
        $result_data = null;
        //-------------
        $error_code = '0000';
        $result_data = CheckoutOrder::getBanks($params);
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataGetBanks(&$data) {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumGetBanks($data, $api_key)) {
                $error_code = '0000';
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => (int)$error_code);
    }

    protected function _validateChecksumGetBanks($data, $api_key) {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $api_key;
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

}
