<?php

namespace common\api;

use common\components\libs\qrcode\QrCode;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\components\utils\Strings;
use common\models\business\PaymentMethodBusiness;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PartnerPaymentAccount;
use common\models\db\PartnerPaymentFee;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\input\CheckoutOrderSearch;
use common\util\Helpers;
use Yii;
use common\models\db\Merchant;
use common\components\utils\Validation;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;
use yii\helpers\VarDumper;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
class CheckoutVersionSeamlessStaticApi extends CheckoutBasicSeamlessApi {

    public function getVersion()
    {
//        return ObjInput::get('version', 'str', '1.0'); // KHÔNG CHO PHÉP TẠO 1.0
        return ObjInput::get('version', 'str', '');
    }

    protected function _isFunction($function) {
        return ($function == 'CreateOrder' || $function == 'CheckOrder'||  $function == 'GetListOrder'||  $function == 'GetSummaryOrder');
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

//            $merchant_id = self::getMerchantIdFromMerchantSiteCode($data['merchant_site_code']);
//            var_dump($merchant_id);die();
//            if (in_array($data['merchant_site_code', $GLOBALS['MERCHANT_ON_SEAMLESS'])) {
                $data['payment_method_code'] = ObjInput::get('payment_method_code', 'str', '');
                $data['bank_code'] = ObjInput::get('bank_code', 'str', '');

            return $data;
        } elseif ($function == 'CheckOrder') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str', '');
            $data['token_code'] = ObjInput::get('token_code', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }elseif ($function == "GetListOrder") {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str', '');
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
        }elseif ($function == "GetSummaryOrder") {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str', '');
            $data['time_created_from'] = ObjInput::get('time_created_from', 'str', '');
            $data['time_created_to'] = ObjInput::get('time_created_to', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code) {
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
            '0030' => 'Invalid time_created',
            '0074' => 'Invalid token key',
            '0075' => 'Invalid language',
            '0080' => 'Invalid amount',
            '0090' => 'Chưa cấu hình phí thanh toán',
            '0091' => 'Chưa cấu hình phương thức thanh toán',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    /**
     *
     * @param type $params : merchant_site_code, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, language
     * @return array
     */
    protected function _createOrder($params) {
        $error_code = '0001';
        $result_data = null;

        $check_fee = self::checkAmountPaymentMethod($params);
        if (!$check_fee) {
            $error_code = '0080';
            return array('error_code' => $error_code, 'result_data' => $result_data);
        }

//        $merchant_id = self::getMerchantIdFromMerchantSiteCode($params['merchant_site_code']);
        //-------------
//        $params['merchant_site_code'] = $merchant_id;

        $inputs = array(
            'version' => $this->getVersion(),
            'language_id' => $this->_getLanguageId($params['language']),
            'merchant_id' => $params['merchant_site_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'return_url' => $params['return_url'],
            'cancel_url' => $params['cancel_url'],
            'notify_url' => $params['notify_url'],
            'time_limit' => strtotime($params['time_limit']),
            'buyer_fullname' => $params['buyer_fullname'],
            'buyer_mobile' => !empty($params['buyer_mobile']) ? $params['buyer_mobile'] : '0911111111',
            'buyer_email' => !empty($params['buyer_email']) ? $params['buyer_email'] : 'notrequired@nganluong.vn',
            'buyer_address' => $params['buyer_address'],
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::add($inputs);
        if ($result['error_message'] === '') {
            $token_code = $result['token_code'];
            $error_code = '0000';
            $checkout_url = CheckoutOrder::getCheckoutUrlSeamless($this->getVersion(), $token_code, $params);


            /** checkout_url: http://localhost/qr-bank/vib/vi/checkout/version_2_0/request/152429-COBBD0BA1A34/VIB-QR-CODE */
            //===== Thay vì curl link version2.0/request thì ghép code phần đó vào đây
            //===== START GET DATA_QR
            $this->token_code = $token_code;
            $this->checkout_order = $this->_getCheckoutOrder();

            $data_qr = '';
            @self::writeLog('[INPUT]'. json_encode($this->checkout_order));

            if($this->getVersion() == '2.0'){
                $payment_method_code = CheckoutOrder::getPaymentMethod($params);
//                $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($payment_method_code, 'version_1_0');
                $payment_method_info = PaymentMethodBusiness::getInfoByPaymentMethodCode($payment_method_code, 'version_1_0', $this->checkout_order['merchant_id']);

                if ($payment_method_info != false) {
                    $model_payment_method_name = PaymentMethod::getModelFormName($payment_method_info['partner_payment_code'], $payment_method_info['method_code'], $payment_method_info['code']);
//                    var_dump($model_payment_method_name);die();
                    if (class_exists($model_payment_method_name)) {
                        $model = new $model_payment_method_name();
                        $model->set($this->checkout_order['amount'], 'version_1_0', 'request', $payment_method_info, $payment_method_info['partner_payment_code'], $payment_method_info['partner_payment_id']);
                        $model->active         = true;
                        $model->checkout_order = $this->checkout_order;
//                        var_dump($model->checkout_order['qrcode']['data']['qrData']);die();
                        if ($model->getPayerFee() !== false) {
                            $model->load(Yii::$app->request->get());
//                            if (ob_get_level() > 0) {
//                                ob_end_clean();
//                            }

                            $flag = $model->initOption();
                            @self::writeLog('[INPUT]'. json_encode($model->checkout_order));
                            $message = $model->error_message;
                            @self::writeLog('[RESULT]'.$message);
                            if($message == ''){
                                if (!empty($model->checkout_order['qrcode'])) {
//                                $data_qr = $model->checkout_order['qrcode']['data']['qrData'];
                                    if($payment_method_info['partner_payment_code'] == 'NGANLUONG-SEAMLESS'
                                    || $payment_method_info['partner_payment_code'] == 'VCB'
                                    ){
                                        $pre_data_qr = $model->checkout_order['qrcode']; // NL
                                        $data_qr = self::genQRcode($pre_data_qr['data']);

                                    } elseif($payment_method_info['partner_payment_code'] == 'VCB-VA'
                                        || $payment_method_info['partner_payment_code'] == 'BIDV-VA'

                                    ){
//                                        $data_qr = $model->checkout_order['qrcode']['data']['qrData']; // alepay
                                        $data_qr = $model->checkout_order['qrcode'];
//                                          var_dump($model->checkout_order);die();

                                    } else{
//                                        var_dump($model->checkout_order['qrcode']);die();
                                        $data_qr = '';
                                    }
//                                    var_dump($pre_data_qr);die();



                                    //  $response = self::get_web_page($checkout_url); // CŨ
                                    $result_data = array(
                                        //  'data' => $response['result_data']['data_qr'], // CŨ
                                        'data' => $data_qr,
                                        'token_code' => $token_code,
                                    );
                                } else{
                                    $error_code = '0001';
                                    @self::writeLog('[ERROR]: [debug-1] | ' . $token_code) ;
                                }
                            } else{
                                $error_code = '0001';
                                @self::writeLog('[ERROR]: [debug-2] | ' . $token_code);
                            }

                        } else{
                            $error_code = '0001'; // Chua cau hinh phi thanh toan
                            @self::writeLog('[ERROR]: Chưa cấu hình phí thanh toán | ' . $token_code);
                        }
                    }
                } else{
                    $error_code = '0001'; // Chua cau hinh phuong thuc thanh toan
                    @self::writeLog('[ERROR]: Chưa cấu hình phương thức/nhóm phương thức/kênh | ' . $token_code);
                }
            }else{
                $error_code = '0004';
            }

            //=======END

        } else {
            echo $result['error_message'];
            $error_code = '0101';
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }
    public  function get_web_page( $url ){
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        curl_close( $ch );

        return json_decode($content,true);
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
                    'partner_payment_fee.partner_payment_id' => $payment_method_id
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
//        $result_data = CheckoutOrder::getParamsForNotifyUrl($params['checkout_order_info']);
        $result_data = CheckoutOrder::getParamsForNotifyUrlAppClone($params['checkout_order_info']);
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, language, checksum
     */
    protected function _validateDataCreateOrder(&$data) {
        $error_code = '0001';

        if($data['merchant_site_code'] === ''){
            $error_code = '0003'; // merchant_site_code khong hop le
            return array('error_code' => $error_code);
        }
        if(!$this->_validateLanguage($data['language'])){
            $error_code = '0075'; // Invalid language
            return array('error_code' => $error_code);
        }

        if(!$this->_validateVersion($this->getVersion())){
            $error_code = '0004'; // Invalid version
            return array('error_code' => $error_code);
        }

//        $merchant_id = self::getMerchantIdFromMerchantSiteCode($data['merchant_site_code']);
//        $api_key = Merchant::getApiKey($merchant_id, $this->merchant_info);
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);

        if ($api_key !== false) {
            if ($this->_validateChecksumCreateOrder($data, $api_key)) {
                if ($this->_validateOrderCode($data['order_code'])) {
                    if ($this->_validateOrderDescription($data['order_description'])) {
                        if ($this->_validateAmount($data['amount'])) {
                            if ($this->_validateCurrency($data['currency'])) {
                                if ($this->_validateBuyerFullname($data['buyer_fullname'])) {
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
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateChecksumCreateOrder($data, $api_key) {
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

//        $merchant_id = self::getMerchantIdFromMerchantSiteCode($data['merchant_site_code']);
        $merchant_id = $data['merchant_site_code'];
        $api_key = Merchant::getApiKey($merchant_id, $this->merchant_info);
        if ($api_key !== false) {
            if ($this->_validateChecksumCheckOrder($data, $api_key)) {
                if ($this->_validateTokenCode($data['token_code'], $checkout_order_info)) {
                    if ($checkout_order_info['merchant_id'] == $merchant_id) {
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
        $merchant_id = $data['merchant_site_code'];
        #endregion
        $api_key = Merchant::getApiKey($merchant_id, $this->merchant_info);
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
    protected function _validateDataGetSummaryOrder(&$data)
    {
        $error_code = '0001';

        $merchant_id = $data['merchant_site_code'];
        $api_key = Merchant::getApiKey($merchant_id, $this->merchant_info);
        if ($api_key) {
            if ($this->_validateChecksumGetSummaryOrder($data, $api_key)) {
                if($this->_validateTimeForGetSummaryOrder($data)){
                    $error_code = '0000';
                }else{
                    $error_code = '0030'; // time_created khong hop le
                }
            } else {
                $error_code = '0017';
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
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

    protected function _validateChecksumGetSummaryOrder($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['time_created_from'];
        $str_checksum .= '|' . $data['time_created_to'];
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

    protected function _getListOrder($params)
    {
        $error_code = '0000';
        $merchant_id = $params['merchant_site_code'];
        $params_search = [
            'time_created_from' => $params['time_created_from'],
            'time_created_to' => $params['time_created_to'],
            'token_code' => $params['token_code'],
            'order_code' => $params['order_code'],
            'order_description' => $params['order_description'],
            'merchant_id' => $merchant_id,
//            'status' => $params['status'],
            'status' => [CheckoutOrder::STATUS_PAID], // update 04/06/2024
            'page' => $params['page'],
            'transaction_timeout' => "-1",
        ];

        $search = new CheckoutOrderSearch();
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->setAttributes($params_search);
//        if (empty($search->time_created_from)) {
//            $search->time_created_from = date('d-m-Y');
//        }
//        if (empty($search->time_created_to)) {
//            $search->time_created_to = date('d-m-Y');
//        }
//                echo "<pre>";
//                var_dump($search->getAttributes());
//                die();
        $page = $search->searchGetListOrder();
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
            $d['mid'] = @$data['merchant_info']['merchant_code'];
            $d['tid'] = @$data['merchant_info']['terminal_id'];
            $d['ref_no'] = (intval($data['transaction_id']) != 0) ? 'QRB_PAYGATE_' . $data['transaction_id'] : '';
            $d['auth_code'] = $data['auth_code'];
            $d['sender_fee'] = @$data['transaction_info']['sender_fee'];
            $d['receiver_fee'] = @$data['transaction_info']['receiver_fee'];
            $d['time_created'] = @$data['transaction_info']['time_paid'];

            //TODO Trả thông tin TK của người thanh toán
            $d['account_number'] = CheckoutOrder::formatResponseForVaAccountNumber($data);
            $d['bank_code']      = CheckoutOrder::formatResponseForVaBankCode($data);
            $d['account_name']   = CheckoutOrder::formatResponseForVaAccountName($data);

            $result_data['data'][] = $d;
        }
//        $result_data = [
//          123
//        ];

        return array('error_code' => $error_code, 'result_data' => $result_data);
    }
    protected function _getSummaryOrder($params)
    {
        $error_code = '0000';
        $merchant_id = $params['merchant_site_code'];
        $params_search = [
            'time_created_from' => $params['time_created_from'],
            'time_created_to' => $params['time_created_to'],
            'merchant_id' => $merchant_id,
        ];
//        var_dump($params_search);die();

        $conditions = self::getConditions($params_search, $errors);
//        var_dump($conditions);die();

        $total_cashin_amount = 0; // Tổng số tiền thanh toán
        $total_cashout_amount = 0; // Tổng số tiền được rút

        $search = new CheckoutOrderSearch();
        $search->pageSize = $GLOBALS["PAGE_SIZE"];
        $search->setAttributes($params_search);
        if (empty($search->time_created_from)) {
            $search->time_created_from = date('d-m-Y');
        }
        if (empty($search->time_created_to)) {
            $search->time_created_to = date('d-m-Y');
        }

        $amount_paid = [];$amount_other = [];$total = [];

        if ($conditions) {
            $condition_extension = " AND status IN ("
                . CheckoutOrder::STATUS_PAID . ", " . CheckoutOrder::STATUS_WAIT_REFUND . ", " . CheckoutOrder::STATUS_REFUND . ",". CheckoutOrder::STATUS_REFUND_PARTIAL . ")";
            $condition_paid = " AND status =  " . CheckoutOrder::STATUS_PAID;
            $total_count = Tables::selectSumAndCountDataTableNew("checkout_order",$conditions . $condition_extension, 'amount');
            $total_cashin_amount = @$total_count["amount"];
            $count = @$total_count["records"];
//            var_dump($total_count);die();/**/
            $total = ['amount' => $total_cashin_amount, 'records' => $count];
            self::getFormatZeroForCount($total);

            $amount_paid = Tables::selectSumAndCountDataTableNew("checkout_order", $conditions . $condition_paid, 'amount');
            self::getFormatZeroForCount($amount_paid);

            // CỔNG HIỆN CHƯUA CÓ GD ĐẢO
//            $amount_refund = Tables::selectSumAndCountDataTableForRefundNew("transaction", $conditions . " AND status =  " . Transaction::STATUS_PAID . " AND transaction_type_id = " . TransactionType::getRefundTransactionTypeId(), 'amount');
//            self::getFormatZeroForCount($amount_refund);

//            var_dump($amount_cancel);
//            die();
//            var_dump($amount_refund);

            #region gia lap amount_cancel va amount_refund
//            $amount_cancel = [
//                'amount' => '371200',
//                'records' => '8'
//            ];
//            $amount_refund = [
//                'amount' => '82000',
//                'records' => '10'
//            ];
            #endregion

            $amount_other = [
                'amount' => (string)( floatval($total_cashin_amount) - floatval($amount_paid['amount']) ),
                'records' => (string)( intval($count) - intval($amount_paid['records']) )
            ];
//            var_dump($amount_refund_and_cancel);die();

        } else {
            $count = 0;
        }

        if($errors == ''){
            $result_data = [
//                'total_record' => $count,
//                'total_amount' => $total_cashin_amount,
                'data' => [
                    'total' => $total,
                    'paid' => $amount_paid,
                    'other' => $amount_other,
                ]
            ];
        }



        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    private function getFormatZeroForCount(&$arr){
        if($arr['records'] == '0'){
            $arr['amount'] = '0';
        }
        if($arr['amount'] == '0'){
            $arr['records'] = '0';
        }
        if($arr['amount'] == null){
            $arr['amount'] = '0';
        }
        if($arr['records'] == null){
            $arr['records'] = '0';
        }
    }

    function getConditions($params_search, &$errors = array()) {
        $conditions = array();
        // Thòi gian tạo
        if ($params_search['time_created_from'] != null && trim($params_search['time_created_from']) != "") {
            $conditions[] = "time_created >= " .  $params_search['time_created_from'];
        }else{
            $errors = 'Ngày tạo từ không được để trống';
        }

        if ($params_search['time_created_to'] != null && trim($params_search['time_created_to']) != "") {
            $conditions[] = "time_created <= " .  $params_search['time_created_to'];
        }else{
            $errors = 'Ngày tạo đến không được để trống';
        }

        // Thòi gian thanh toan
        if ($params_search['time_paid_from'] != null && trim($params_search['time_paid_from']) != "") {
            if (!Validation::isDate($params_search['time_paid_from'])) {
                $errors = 'Ngày thanh toán từ không đúng định dạng';
            } else {
                $time_paid_from = FormatDateTime::toTimeBegin($params_search['time_paid_from']);
                $conditions[] = "time_paid >= $time_paid_from ";
            }
        }

        if ($params_search['time_paid_to'] != null && trim($params_search['time_paid_to']) != "") {
            if (!Validation::isDate($params_search['time_paid_to'])) {
                $errors = 'Ngày thanh toán đến không đúng định dạng';
            } else {
                $time_paid_to = FormatDateTime::toTimeEnd($params_search['time_paid_to']);
                $conditions[] = "time_paid <= $time_paid_to ";
            }
        }

//        if (!empty($params_search['status'])) {
//            $conditions[] = "status = " . $params_search['status'];
//        }

//        var_dump($params_search['merchant_id']);die();
        if (intval($params_search['merchant_id']) > 0) {
            $conditions[] = "merchant_id = " . $params_search['merchant_id'];
        }

        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = 1;
        }

        return $conditions;
    }


    public function getMerchantByTokenKey($value){
        $merchant_info = PartnerPaymentAccount::findOne(['token_key' => $value,'status' => PartnerPaymentAccount::STATUS_ACTIVE]);
        if (!empty($merchant_info)){
            if (in_array($merchant_info->partner_payment_id,[20,21])){// Tài khoản kênh thanh toán của Alepay
                $merchant_site_code = $merchant_info->merchant_id;
                return $merchant_site_code;

            }
            return '';
        }
        return '';
    }

    protected function _validateChecksumCheckOrder($data, $api_key) {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $api_key;
        $this->writeLog('[md5 checksum]:' . $str_checksum . ' ======== '. md5($str_checksum));
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        }
        return false;
    }

    protected function _getLanguageId($language) {
        if ($language == 'en') {
            return 2;
        } else {
            return 1;
        }
    }

    protected function getMerchantIdFromMerchantSiteCode($merchant_site_code){
        $merchant = Merchant::find()->where(['mobile_user' => $merchant_site_code])->asArray()->one();
        return ($merchant ? $merchant['id'] : 0);
    }

    protected function genQRcode($qrData)
    {
        ob_start();
        QrCode::png(
            $qrData,
            $outfile = false,
            $level = 3,
            $size = 5,
            $margin = 4,
            $saveandprint = false
        );
        $imageString = base64_encode(ob_get_clean());
        header('Content-Type: text/html');
//        ob_end_clean();
        if(ob_get_length()>0) ob_end_clean();

        return $imageString;
    }

}
