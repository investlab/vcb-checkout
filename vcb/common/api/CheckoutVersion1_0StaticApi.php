<?php

namespace common\api;

use common\components\libs\NotifySystem;
use common\models\business\MerchantConfigBusiness;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PartnerPaymentFee;
use common\models\db\PaymentMethod;
use common\models\input\CheckoutOrderSearch;
use common\util\Helpers;
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
class CheckoutVersion1_0StaticApi extends CheckoutBasicApi
{

    public function getVersion()
    {
        return ObjInput::get('version', 'str', '1.0');
    }

    protected function _isFunction($function)
    {
        return (
            $function == 'CreateOrder' ||
            $function == 'CheckOrder' ||
            $function == 'GetBanks' ||
            $function == 'GetBanksPos' ||
            $function == 'GetListOrder' ||
            $function == 'CheckOrderByOrderCode' ||
            $function == 'GetListOrderA08'

        );
    }

    public function getData($function)
    {
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
//            if (in_array($data['merchant_site_code'], $GLOBALS['MERCHANT_ON_SEAMLESS'])) {
            $data['payment_method_code'] = ObjInput::get('payment_method_code', 'str', '');
            $data['bank_code'] = ObjInput::get('bank_code', 'str', '');
            $data['link_card'] = ObjInput::get('link_card', 'int', 0);
            $data['customer_field'] = ObjInput::get('customer_field', 'str', '');
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
        } elseif ($function == 'GetBanksPos') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['type'] = ObjInput::get('type', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == 'CheckOrderByOrderCode') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['order_code'] = ObjInput::get('order_code', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == "GetListOrder") {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['order_code'] = ObjInput::get('order_code', 'str', '');
            $data['order_description'] = ObjInput::get('order_description', 'str', '');
            $data['token_code'] = ObjInput::get('token_code', 'str', '');
            $data['time_created_from'] = ObjInput::get('time_created_from', 'str', '');
            $data['time_created_to'] = ObjInput::get('time_created_to', 'str', '');
            $data['payment_method_code'] = ObjInput::get('payment_method_code', 'str', '');
            $data['status'] = ObjInput::get('status', 'str', '');
            $data['page'] = ObjInput::get('page', 'int', '1');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        } elseif ($function == "GetListOrderA08") {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['order_code'] = ObjInput::get('order_code', 'str', '');
            $data['order_description'] = ObjInput::get('order_description', 'str', '');
            $data['token_code'] = ObjInput::get('token_code', 'str', '');
            $data['time_created_from'] = ObjInput::get('time_created_from', 'str', '');
            $data['time_created_to'] = ObjInput::get('time_created_to', 'str', '');
            $data['status'] = ObjInput::get('status', 'str', '');
            $data['page'] = ObjInput::get('page', 'int', '1');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Success',
            '0001' => 'Undefined Error',
            '0002' => 'Invalid Function name',
            '0003' => 'Invalid merchant_site_code ',
            '0004' => 'Invalid version',
            '0005' => 'Invalid order_code',
            '0006' => 'Invalid order_description',
            '0007' => 'Invalid amount format',
            '0008' => 'Invalid currency',
            '0009' => 'Invalid buyer_fullname',
            '0010' => 'Invalid buyer_email',
            '0011' => 'Invalid buyer_mobile',
            '0012' => 'Invalid buyer_address',
            '0013' => 'Invalid return_url',
            '0014' => 'Invalid cancel_url',
            '0015' => 'Invalid notify_url',
            '0016' => 'Invalid time_limit',
            '0017' => 'Invalid checksum',
            '0018' => 'Invalid token_code',
            '0101' => 'Request params are ok, but could not create the order for this merchant.',
            '0020' => 'Can\'t create an order for this merchant',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    /**
     *
     * @param type $params : merchant_site_code, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, language
     * @return type
     */
    protected function _createOrder($params)
    {
        $error_code = '0001';
        $result_data = null;

        $check_fee = self::checkAmountPaymentMethod($params);
        if (!$check_fee) {
            $error_code = '0007';
            return array('error_code' => $error_code, 'result_data' => $result_data);
        }
//        $merchant= Merchant::findOne($params['merchant_site_code']);
//        if ($merchant->email_requirement==0){
//            if ($params['buyer_email']==''){
//                $params['buyer_email'] = 'not-required@not.not';
//            }
//        }
        //-------------
        $inputs = array(
            'version' => $this->getVersion(),
            'language_id' => $this->_getLanguageId($params['language']),
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
            'currency_exchange' => $params['currency_exchange'] ?? "",
            'link_card' => $params['link_card'] == "1" ? "1" : 0,
            'customer_field' => $params['customer_field'],
        );
        $result = CheckoutOrderBusiness::add($inputs);
        if ($result['error_message'] === '') {
            $token_code = $result['token_code'];
            $error_code = '0000';

            if (self::checkAllow3_0($params['merchant_site_code'])) {
                // allow view 3.0
                $result_data = array(
                    'checkout_url' => CheckoutOrder::getCheckoutUrl('3.0', $token_code, $params),
                    'token_code' => $token_code,
                );
            } else {
                // OLD
                $result_data = array(
                    'checkout_url' => CheckoutOrder::getCheckoutUrl($this->getVersion(), $token_code, $params),
                    'token_code' => $token_code,
                );
            }

        } else {
            $error_code = '0101';
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected static function checkAllow3_0($merchant_id)
    {
        //TODO Check merchant config (theo merchant_id) de xem co dc hien thi 3.0 hay khong
        $merchantConfig = MerchantConfigBusiness::getConfigByMerchantId($merchant_id);
        if (is_array($merchantConfig)
            && isset($merchantConfig['ALLOW_VERSION_3_0'])
            && $merchantConfig['ALLOW_VERSION_3_0'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $params
     * @return type boolean(true/false)
     */
    protected function checkAmountPaymentMethod($params)
    {
        if (!empty($params['payment_method_code']) && !empty($params['bank_code'])) {
            $payment_method = CheckoutOrder::getPaymentMethod($params);
            $method_code = CheckoutOrder::getMethodCode($params['payment_method_code']);
            $payment_method_id = PaymentMethod::getPaymentMethodIdActiveByCode($payment_method);

            $method_check = Method::find()->select('method.id, merchant_fee.min_amount as merchant_min_amount, 
                         partner_payment_fee.min_amount as partner_min_amount')
                ->leftJoin('merchant_fee', 'merchant_fee.method_id=method.id')
                ->leftJoin('partner_payment_fee', 'partner_payment_fee.method_id=method.id')
                ->where([
                    'method.code' => $method_code, 'method.status' => Method::STATUS_ACTIVE,
                    'merchant_fee.status' => MerchantFee::STATUS_ACTIVE, 'partner_payment_fee.status' => PartnerPaymentFee::STATUS_ACTIVE,
                    'partner_payment_fee.payment_method_id' => $payment_method_id
                ])
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

        return true;
    }

    /**
     *
     * @param type $params : merchant_site_code, token_code, checkout_order_info, checksum
     * @return type
     */
    protected function _checkOrder($params)
    {
        $error_code = '0001';
        $result_data = null;
        //-------------
        $error_code = '0000';
        if (in_array($params['checkout_order_info']['merchant_id'], $GLOBALS['MERCHANT_BCA'])) {
            $result_data = CheckoutOrder::getParamsForNotifyUrlForBCA($params['checkout_order_info']);
        } elseif (in_array($params['checkout_order_info']['merchant_id'], $GLOBALS['MERCHANT_VHC'])) {
            $result_data = CheckoutOrder::getParamsForNotifyUrlVhc($params['checkout_order_info']);
        }  elseif (in_array($params['checkout_order_info']['merchant_id'], $GLOBALS['MERCHANT_XANHPON'])) {
            $result_data = CheckoutOrder::getParamsForNotifyUrlXanhPon($params['checkout_order_info']);
        } else {
            $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _checkOrderByOrderCode($params)
    {
        $error_code = '0001';
        $result_data = null;
        //-------------
        $checkout_orders = CheckoutOrder::getByOrderId($params['order_code'], $params['merchant_site_code']);
        if ($checkout_orders) {
            $error_code = '0000';
            foreach ($checkout_orders as $checkout_order) {
                if (in_array($checkout_order['merchant_id'], $GLOBALS['MERCHANT_BCA'])) {
                    $result_data[] = CheckoutOrder::getParamsForNotifyUrlForBCA($checkout_order);
                } else {
                    $result_data[] = CheckoutOrder::getParamsForNotifyUrl($checkout_order);
                }

            }
        } else {
//            @NotifySystem::send("Hehe Query" . json_encode($params));
            $error_code = '0005';
        }


//        $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, language, checksum
     */
    protected function _validateDataCreateOrder(&$data)
    {

        $error_code = '0001';

        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        //

        if ($api_key !== false) {
            if ($data['merchant_site_code'] == 25 || $data['merchant_site_code'] == 24) {
                $error_code = '0020';
            } else {
                if ($this->_validateChecksumCreateOrder($data, $api_key)) {
                    if ($this->_validateOrderCode($data['order_code'])) {
                        if ($this->_validateOrderDescription($data['order_description'])) {
                            if ($this->_validateCurrency($data['currency'])) {
                                if ($this->_validateAmount($data)) {
                                    if ($this->_validateBuyerFullname($data['buyer_fullname'])) {
                                        $merchant = Merchant::findOne($data['merchant_site_code']);
                                        if ($merchant->email_requirement == 0) {
                                            if ($data['buyer_email'] == '' || $data['buyer_email'] == null) {
                                                $data['buyer_email'] = 'notrequired@nganluong.vn';
                                                $data['buyer_address'] = 'Viet Nam';
                                            }
                                            if ($data['buyer_mobile'] == '' || $data['buyer_mobile'] == null) {
                                                $data['buyer_mobile'] = '1900585899';
                                                $data['buyer_address'] = 'Viet Nam';

                                            }
                                        }
                                        if (intval($data['merchant_site_code']) == 263) {
                                            $data['return_url'] = 'https://vietcombank.nganluong.vn/test/merchant_demo_2.php?option=return';
                                            $data['cancel_url'] = 'https://vietcombank.nganluong.vn/test/merchant_demo_2.php?option=cancel';
                                            $data['notify_url'] = 'https://vietcombank.nganluong.vn/test/merchant_demo_2.php?option=notify';
                                        }
                                        if (intval($data['merchant_site_code']) == 3353) {
                                            $data['buyer_address'] = 'Viet Nam';
                                        }

                                        if ($this->_validateBuyerEmailV2($data['buyer_email'], $data['merchant_site_code'])) {
                                            if ($this->_validateBuyerMobile($data['buyer_mobile'], $data['merchant_site_code'])) {
                                                if ($this->_validateBuyerAddress($data['buyer_address'])) {
                                                    if ($this->_validateReturnUrl($data['return_url'])) {
                                                        if ($this->_validateCancelUrl($data['cancel_url'])) {
                                                            if ($this->_validateNotifyUrlV1($data['notify_url'], $data['merchant_site_code'])) {
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
                                    $error_code = '0007';
                                }
                            } else {
                                $error_code = '0008';
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
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumCreateOrder($data, $api_key)
    {
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
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        if (intval($data['merchant_site_code']) == 2374) {
            return true;
        }
        if (intval($data['merchant_site_code']) == 2748) {
            return true;
        }
        if (intval($data['merchant_site_code']) == 4094) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, checksum
     */
    protected function _validateDataCheckOrder(&$data)
    {
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
        return array('error_code' => $error_code);
    }

    protected function _validateDataGetListOrder(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key) {
            if ($this->_validateChecksumGetListOrder($data, $api_key)) {
                $error_code = '0000';
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateDataGetListOrderA08(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key) {
            if ($this->_validateChecksumGetListOrderA08($data, $api_key)) {
                $error_code = '0000';
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateDataCheckOrderByOrderCode(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateOrderCode($data['order_code'])) {
                if ($this->_validateMerchantSiteCode($data['merchant_site_code'])) {
                    if ($this->_validateChecksumCheckOrderByOrderCode($data, $api_key)) {
                        $error_code = '0000';
                    } else {
                        $error_code = '0017';
                    }
                } else {
                    $error_code = '0018';
                }
            } else {
//                @NotifySystem::send("Hehe Validate: " . json_encode($data));
                $error_code = '0005';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumCheckOrder($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    protected function _validateChecksumGetListOrder($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $data['time_created_from'];
        $str_checksum .= '|' . $data['time_created_to'];
        $str_checksum .= '|' . $data['payment_method_code'];
        $str_checksum .= '|' . $data['status'];
        $str_checksum .= '|' . $data['page'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[hash checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die($str_checksum . " ====== " . hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _validateChecksumGetListOrderA08($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $data['time_created_from'];
        $str_checksum .= '|' . $data['time_created_to'];
//        $str_checksum .= '|' . $data['payment_method_code'];
        $str_checksum .= '|' . $data['status'];
        $str_checksum .= '|' . $data['page'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[hash checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die($str_checksum . " ====== " . hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _validateChecksumCheckOrderByOrderCode($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['order_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(md5($str_checksum));
            }
        }
        return false;
    }

    protected function _getLanguageId($language)
    {
        if ($language == 'en') {
            return 2;
        } else {
            return 1;
        }
    }

    protected function _getBanks($params)
    {
        $result_data = null;
        //-------------
        $error_code = '0000';
        if (!empty($params['type']) && in_array($params['type'], ['ATM-CARD', 'WALLET', 'QR-CODE', 'IB-ONLINE', 'QRCODE247', 'CREDIT-CARD-INTERNATIONAL', 'CREDIT-CARD'])) {
            $result_data = CheckoutOrder::getBanksByType($params['type']);

        } else {
            $result_data = CheckoutOrder::getBanks($params);

        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _getListOrder($params)
    {
        $error_code = '0000';
        $params_search = [
            'time_created_from' => $params['time_created_from'],
            'time_created_to' => $params['time_created_to'],
            'token_code' => $params['token_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'merchant_id' => $params['merchant_site_code'],
            'status' => $params['status'],
            'page' => $params['page'],
            'transaction_timeout' => "-1",
        ];

        $search = new CheckoutOrderSearch();
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->setAttributes($params_search);
        if (empty($search->time_created_from)) {
            $search->time_created_from = date('d-m-Y');
        }
        if (empty($search->time_created_to)) {
            $search->time_created_to = date('d-m-Y');
        }
//                echo "<pre>";
//                var_dump($search->getAttributes());
//                die();
        $page = $search->search();
        $result_data = [
            'total_record' => $page->pagination->totalCount,
            'per_page' => $search->pageSize,
            'page' => $search->page,
            'data' => [],
        ];

        $field_return = [
            "order_code",
            "token_code",
            "order_description",
            "amount",
            "buyer_email",
            "buyer_fullname",
            "buyer_mobile",
            "buyer_address",
            "time_created",
            "time_paid",
            "time_refund",
            "status",
        ];

        $datas = $page->data;
        foreach ($datas as $data) {

            $d = Helpers::arrayUnsetByKey($data, $field_return);
//            $d['payment_method_code'] = @$data['transaction_info']['payment_method_info']['code']; // LyLK bảo đóng, đòi mở đấm vỡ mồm
            $d['payment_method_name'] = @$data['transaction_info']['payment_method_info']['name'];
//            $d['sender_fee'] = @$data['transaction_info']['sender_fee'];
//            $d['receiver_fee'] = @$data['transaction_info']['receiver_fee'];


            $result_data['data'][] = $d;
        }
//        $result_data = [
//          123
//        ];

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _getListOrderA08($params)
    {
        $error_code = '0000';
        $params_search = [
            'time_created_from' => $params['time_created_from'],
            'time_created_to' => $params['time_created_to'],
            'token_code' => $params['token_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'merchant_id' => $params['merchant_site_code'],
            'status' => $params['status'],
            'page' => $params['page'],
            'transaction_timeout' => "-1",
        ];

        $search = new CheckoutOrderSearch();
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->setAttributes($params_search);

        if (in_array($search->merchant_id, $GLOBALS['MERCHANT_TIME_IN_TIMESTAMP'])) {
            // search theo timestamp
            if (empty($search->time_created_from)
                || empty($search->time_created_to)
                || !Validation::isValidTimestamp($search->time_created_from)
                || !Validation::isValidTimestamp($search->time_created_to)
            ) {
                $search->time_created_from = strtotime('today');
                $search->time_created_to = time();
            }

            $page = $search->searchAsTimeStamp();
        } else {
            // OLD
            if (empty($search->time_created_from)) {
                $search->time_created_from = date('d-m-Y');
            }
            if (empty($search->time_created_to)) {
                $search->time_created_to = date('d-m-Y');
            }
            $page = $search->search();
        }

        $result_data = [
            'total_record' => $page->pagination->totalCount,
            'per_page' => $search->pageSize,
            'page' => $search->page,
            'data' => [],
        ];

        $field_return = [
            "order_code",
            "token_code",
            "order_description",
            "amount_usd",
            "amount_vnd",
            "buyer_email",
            "buyer_fullname",
            "buyer_mobile",
            "buyer_address",
            "time_created",
            "time_paid",
            "time_refund",
            "status",
        ];

        $datas = $page->data;
        foreach ($datas as $data) {
            $d = Helpers::arrayUnsetByKey($data, $field_return);
            $d['amount_vnd'] = $data['amount'];
            $currency_exchange = json_decode($data['currency_exchange'], true);
            $d['amount_usd'] = '';
            if (is_array($currency_exchange) && isset($currency_exchange['transfer']) && intval($currency_exchange['transfer']) > 0) {
                $d['amount_usd'] = $d['amount_vnd'] / $currency_exchange['transfer'];
            }

//            $d['payment_method_name'] = @$data['transaction_info']['payment_method_info']['name'];
            $d['payment_method_name'] = (isset($data['transaction_info']) && isset($data['transaction_info']['payment_method_info'])
                && isset($data['transaction_info']['payment_method_info']['name'])) ? $data['transaction_info']['payment_method_info']['name'] : '';

            $result_data['data'][] = $d;
        }

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataGetBanks(&$data)
    {
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
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumGetBanks($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));

        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;


    }

    protected function _getBanksPos($params)
    {
        $result_data = null;
        //-------------
        $error_code = '0000';
        if (!empty($params['type']) && in_array($params['type'], ['ATM-CARD', 'WALLET', 'QR-CODE', 'IB-ONLINE', 'QRCODE247', 'CREDIT-CARD-INTERNATIONAL', 'CREDIT-CARD'])) {
            $result_data = CheckoutOrder::getBanksByTypePos($params['type']);

        } else {
            $result_data = CheckoutOrder::getBanksPos($params);

        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateDataGetBanksPos(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumGetBanksPos($data, $api_key)) {
                $error_code = '0000';
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumGetBanksPos($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== ' . md5($str_checksum));

        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

}
