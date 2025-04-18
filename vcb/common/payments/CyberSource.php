<?php


namespace common\payments;


use common\components\utils\Logs;
use common\components\utils\Translate;
use common\components\utils\Strings;
use common\models\db\CheckoutOrder;
use common\models\db\PartnerPaymentAccount;
use common\models\db\Transaction;
use \stdClass;
use \DOMDocument;
use Yii;

class CyberSource extends \SoapClient
{
    
    public $merchant_id;
    public $partner_payment_id;
    public $cybersource_merchant_id;
    public $cybersource_soap_transaction_key;
    public $cybersource_flex_key_id;
    public $cybersource_flex_shared_secret_key;
    
    function __construct($merchant_id, $partner_payment_id, $options = array())
    {
        parent::__construct(CBS_SOAP_WSDL, $options);
        $this->merchant_id = $merchant_id;
        $this->partner_payment_id = $partner_payment_id;
        $this->getConnectParams();
    }
    
    private function getConnectParams() {
        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->merchant_id, $this->partner_payment_id);
        if (!empty($partner_payment_account_info)) {
            $this->cybersource_merchant_id = $partner_payment_account_info['partner_merchant_id'];
            $this->cybersource_soap_transaction_key = $partner_payment_account_info['partner_merchant_password'];
            $this->cybersource_flex_key_id = $partner_payment_account_info['token_key'];
            $this->cybersource_flex_shared_secret_key = $partner_payment_account_info['checksum_key'];
        }
    }

    public static function getPaymentMethodAndBankCode($payment_method_code) {
        $payment_method = '';
        $bank_code = '';
        if (substr($payment_method_code, -8) == 'ATM-CARD') {
            $payment_method = 'ATM_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 9);
            if ($bank_code == 'STB') {
                $bank_code = 'SCB';
            } elseif ($bank_code == 'NCB') {
                $bank_code = 'NAB';
            }
        } elseif (substr($payment_method_code, -9) == 'IB-ONLINE') {
            $payment_method = 'IB_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 10);
        } elseif (substr($payment_method_code, -6) == 'WALLET') {
            $payment_method = 'NL';
        } elseif (substr($payment_method_code, -11) == 'CREDIT-CARD') {
            $payment_method = 'VISA';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 12);
        }
        return array('payment_method' => $payment_method, 'bank_code' => $bank_code);
    }


    function __doRequest($request, $location, $action, $version, $one_way = NULL)
    {
        $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>" . $this->cybersource_merchant_id . "</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">" . $this->cybersource_soap_transaction_key . "</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";
        $requestDOM = new DOMDocument('1.0');
        $soapHeaderDOM = new DOMDocument('1.0');
        try {
            $requestDOM->loadXML($request);
            $soapHeaderDOM->loadXML($soapHeader);
            $node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
            $requestDOM->firstChild->insertBefore($node, $requestDOM->firstChild->firstChild);
            $request = $requestDOM->saveXML();
        } catch (\DOMException $e) {
            die('Error adding UsernameToken: ' . $e->code);
        }
        $result = parent::__doRequest($request, $location, $action, $version);
        return $result;
    }
    
    public function createToken($params) {
        $error = 'Lỗi không xác định';
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => $params['reference_code'],
            'orderNumber' => $params['reference_code'],
            'merchantDefinedData' => array(
                'field1' => substr($params['account_number'], 0, 6),
            ),
            'billTo' => array(
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'street1' => $params['address'],
                'city' => $params['city'],
                'state' => $params['state'],
                'postalCode' => $params['postal_code'],
                'country' => $params['country'],
                'email' => $params['email'],
                'phoneNumber' => $params['phone'],
                'ipAddress' => trim($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : $params['client_ip'],
            ),
            'purchaseTotals' => array(
                'currency' => $params['currency'],
            ),
            'card' => array(
                'cardType' => $this->getCardTypeByCode($params['card_type']),
                'accountNumber' => $params['account_number'],
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
            ),
            'subscription' => array(
                'title' => 'VCB Create Customer Token',
                'paymentMethod' => 'credit card',
            ),
            'recurringSubscriptionInfo' => array(
                'frequency' => 'on-demand',
            ),
            'paySubscriptionCreateService' => array(
                'run' => 'true',
            ),
//            'payerAuthEnrollService' => array(
//                'run' => 'true',
//            ),
        );
        // call
        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs['card']['accountNumber']);
        $this->_writeLog('['. $params['reference_code'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($inputs_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('['. $params['reference_code'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));
        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        // return
        return array('error' => $error, 'result' => $result);
    }

    public function authorizeSubcription($params)
    {
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => $params['cashin_id'],
            'purchaseTotals' => array(
                'currency' => 'VND',
                'grandTotalAmount' => $params['cashin_amount']
            ),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token']
            ),
            'ccCaptureService' => array(
                'run' => 'true',
            ),
            'payerAuthEnrollService' => array(
                'run' => 'true',
            ),
            'ccAuthService' => array(
                'run' => 'true',
            ),
        );
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        // insert log
//        $input_log = array(
//            'card_type' => $params['card_type'],
//            'card_fullname' => $params['last_name'] . ' ' . $params['first_name'],
//            'card_number' => $params['account_number'],
//            'card_month' => $params['expiration_month'],
//            'card_year' => $params['expiration_year'],
//            'user_id' => (!isset($params['customer_id']) || empty($params['customer_id']) ? 0 : $params['customer_id']),
//            'cashin_id' => str_replace('NLDSVN*', '', $params['cashin_id']),
//            'client_ip' => @$_SERVER['REMOTE_ADDR'],
//        );
//        $id_log = $this->_insertLog(__FUNCTION__, $input_log);
        // return
        $this->_writeLog('['. $params['cashin_id'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('['. $params['cashin_id'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));

        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        // update log
//        $input_log = array(
//            'id' => $id_log,
//            'response_code' => strval($result->reasonCode),
//            'response_description' => strval($result->decision),
//            'response_content' => serialize($result),
//            'refer_code' => strval($result->requestID),
//            'ip_country' => strval(@$result->afsReply->ipCountry),
//            'bin_country' => strval(@$result->afsReply->binCountry),
//            'card_scheme' => strval(@$result->afsReply->cardScheme),
//            'card_issuer' => strval(@$result->afsReply->cardIssuer),
//        );
//        $this->_updateLog(__FUNCTION__, $input_log);
        // return
        return array('error' => $error, 'result' => $result);
    }

    public function authorizeSubcription3D($params)
    {
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => $params['cashin_id'],
            'purchaseTotals' => array(
                'currency' => 'VND',
                'grandTotalAmount' => $params['cashin_amount']
            ),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token']
            ),
            'ccCaptureService' => array(
                'run' => 'true',
            ),
            'payerAuthValidateService' => array(
                'signedPARes' => str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $params['signedPARes']),
                'run' => 'true',
            ),
            'ccAuthService' => array(
                'run' => 'true',
            ),
        );
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        // insert log
//        $input_log = array(
//            'card_type' => $params['card_type'],
//            'card_fullname' => $params['last_name'] . ' ' . $params['first_name'],
//            'card_number' => $params['account_number'],
//            'card_month' => $params['expiration_month'],
//            'card_year' => $params['expiration_year'],
//            'user_id' => (!isset($params['customer_id']) || empty($params['customer_id']) ? 0 : $params['customer_id']),
//            'cashin_id' => str_replace('CBS', '', $params['cashin_id']),
//            'client_ip' => @$_SERVER['REMOTE_ADDR'],
//        );
//        $id_log = $this->_insertLog(__FUNCTION__, $input_log);
        // return
        $this->_writeLog('['. $params['cashin_id'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('['. $params['cashin_id'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));

        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        // update log
//        $input_log = array(
//            'id' => $id_log,
//            'response_code' => strval($result->reasonCode),
//            'response_description' => strval($result->decision),
//            'response_content' => serialize($result),
//            'refer_code' => strval($result->requestID),
//            'ip_country' => strval(@$result->afsReply->ipCountry),
//            'bin_country' => strval(@$result->afsReply->binCountry),
//            'card_scheme' => strval(@$result->afsReply->cardScheme),
//            'card_issuer' => strval(@$result->afsReply->cardIssuer),
//        );
//        $this->_updateLog(__FUNCTION__, $input_log);
        // return
        return array('error' => $error, 'result' => $result);
    }

    public function cancelAuthorizeCard($params)
    {
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => time(),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token']
            ),
            'paySubscriptionDeleteService' => array(
                'run' => 'true',
            ),
        );
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        // return
        $this->_writeLog(__FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog(__FUNCTION__ . '[OUTPUT]' . json_encode($result));
//        if ($result->decision == 'ACCEPT' && $result->reasonCode == '100') {
//            self::updateTokenDeleted($params['token']);
//        }
        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }

        // return
        return array('error' => $error, 'result' => $result);
    }

    public function updateCustomerInfo($params) {
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => $params['cashin_id'],
            'billTo' => array(
                'phoneNumber' => $params['phone'],
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'email' => $params['email'],
            ),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token'],
            ),
            'paySubscriptionUpdateService' => array(
                'run' => 'true',
            ),
        );
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        // return
        $this->_writeLog(__FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog(__FUNCTION__ . '[OUTPUT]' . json_encode($result));
        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        return array('error' => $error, 'result' => $result);
    }

    public static function updateTokenInfo($token, $transaction_id, $type, $status = '') {
        $params = array(
            'token' => $token,
            'cashin_id' => $transaction_id,
            'type' => $type,
        );
        if ($status != '') {
            $params['status'] = $status;
        }
        Api::call('updateCardByToken', $params);
    }
    
    public function getCardTypeByCode($card_code) {
        $card_types = array(
            'visa' => '001',
            'mastercard' => '002',
            'americanexpress' => '003',
            'jcb' => '007',
        );
        return $card_types[$card_code];
    }


    public static function checkVisaReject($result) {
        if ($result->decision == 'REJECT' && $result->reasonCode == '481') {
            //if ($result->ccAuthReply->reasonCode == '100' && $result->ccCaptureReply->reasonCode == '100') {
            if ($result->ccAuthReply->reasonCode == '100') {
                return true;
            }
        }
        return false;
    }

    public static function processVisa3D($result, $error_message, $params) {
        $xid = '';
        if ($result->decision == 'REJECT' && $result->reasonCode == '475') {
            if ($result->payerAuthEnrollReply->reasonCode == '475' && $result->payerAuthEnrollReply->acsURL != '') {
                if ($result->payerAuthEnrollReply->paReq != '' && $result->payerAuthEnrollReply->xid != '') {
                    // add info result
                    $xid = $result->payerAuthEnrollReply->xid;
                    $session_info = array(
                        'response_info' => array(
                            'acsURL' => $result->payerAuthEnrollReply->acsURL,
                            'paReq' => $result->payerAuthEnrollReply->paReq,
                            'paRes' => '',
                            'xid' => $xid,
                        ),
                        'process_info' => array(
                            'cashin_id' => $params['cashin_id'],
                            'cashin_amount' => $params['cashin_amount'],
                            'url' => $params['payment_url'] . "&xid=$xid"
                        ),
                        'card_info' => array(
                            'card_type' => $params['card_type'],
                            'last_name' => $params['last_name'],
                            'first_name' => $params['first_name'],
                            'card_number' => $params['account_number'],
                            'card_month' => $params['expiration_month'],
                            'card_year' => $params['expiration_year'],
                        ),
                        'token' => $params['token']
                    );
                    Yii::$app->cache->set('TOKEN_3D_' . $xid, self::encryptSessionInfo($session_info));
                    Yii::$app->session->set('TOKEN_3D_' . $xid, self::encryptSessionInfo($session_info));
                    $error_message = '';
                }
            }
        }
        return array('error_message' => $error_message, 'xid' => $xid);
    }

    public static function getErrorMessage($error_code)
    {
        $messages = array(
            '100' => 'Giao dịch thành công',
            '101' => 'Thông tin giao dịch bị thiếu một hoặc nhiều trường dữ liệu bắt buộc',
            '102' => 'Một hoặc nhiều trường thông tin trong giao dịch chứa dữ liệu không hợp lệ',
            '110' => 'Một phần tiền trong số tiền thanh toán đã được xử lý thành công',
            '150' => 'Lỗi hệ thống thanh toán, giao dịch chưa được xử lý',
            '151' => 'Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền',
            '152' => 'Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền và đang được xử lý',
            '200' => 'Giao dịch bị từ chối do địa chỉ nhận hàng không khớp với địa chỉ chủ thẻ đã khai báo',
            '201' => 'Giao dịch chờ xử lý do ngân hàng phát hành thẻ yêu cầu bạn phải trả lời một số câu hỏi',
            '202' => 'Thẻ đã hết hạn sử dụng, vui lòng liên hệ ngân hàng phát hành thẻ để biết thêm chi tiết',
            '203' => 'Giao dịch bị từ chối bởi ngân hàng phát hành thẻ',
            '204' => 'Số dư tài khoản thẻ không đủ hoặc thẻ đã hết hạn mức thanh toán',
            '205' => 'Thẻ bị từ chối giao dịch do chủ thẻ thông báo với ngân hàng phát hành là thẻ đã bị mất hoặc bị đánh cắp',
            '207' => 'Hệ thống ngân hàng phát hành thẻ đang bị lỗi, không thể thực hiện được giao dịch',
            //'208'	=> 'Thẻ chưa được kích hoạt hoặc không tồn tại',
            '208' => 'Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            '209' => 'Giao dịch bị từ chối thực hiện do Mã xác thực thẻ American Express (CID) không chính xác',
            '210' => 'Thẻ hết hạn mức thanh toán',
            '211' => 'Thông tin thẻ không chính xác', //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '220' => 'Bộ vi xử lý từ chối yêu cầu dựa trên một vấn đề chung với tài khoản của khách hàng.', ////
            '221' => 'The customer matched an entry on the processor\'s negative file.', ///
            '222' => 'Tài khoản thẻ đang bị đóng băng bởi ngân hàng phát hành', ///
            '230' => 'Thông tin thẻ không chính xác', //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '231' => 'Số thẻ không hợp lệ',
            '232' => 'Loại thẻ không được chấp nhận bởi hệ thống thanh toán',
            '233' => 'Hệ thống thanh toán thẻ quốc tế không chấp nhận xử lý giao dịch',
            '234' => 'Có lỗi giữa hệ thống Vietcombank với hệ thống thanh toán thẻ quốc tế',
            '235' => 'Yêu cầu xử lý giao dịch với số tiền lớn hơn số tiền khi kiểm tra thông tin thẻ',
            '236' => 'Hệ thống xử lý thẻ quốc tế đang bị lỗi, không thể thực hiện được giao dịch',
            '237' => 'Giao dịch đã được trả lại',
            '238' => 'Tài khoản thẻ của khách hàng đã bị trừ tiền',
            '239' => 'Số tiền trong yêu cầu xử lý sai khác với thông tin trong giao dịch trước đó',
            '240' => 'Bạn chọn sai loại thẻ',
            '241' => 'Request ID không chính xác',
            '242' => 'Yêu cầu thanh toán đã được gửi nhưng không thể trừ được tiền',
            '243' => 'Yêu cầu thanh toán đã được gửi thực hiện hoặc bị chuyển trả ở lần trước đó',
            '247' => 'Yêu cầu thanh toán đã bị hủy',
            '250' => 'Yêu cầu thanh toán bị trễ do đường truyền',
            '475' => 'Thẻ sử dụng mật khẩu xác thực giao dịch nên không thể liên kết',
            '476' => 'Xác thực mật khẩu thanh toán (3Dsecure) không thành công',
            '480' => 'Thẻ bị REVIEW, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            '481' => 'Giao dịch bị từ chối, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp',
            //'481'	=> 'Đơn hàng không được chấp nhận của bên quản trị rủi ro',
        );
        return (array_key_exists($error_code, $messages) ? $messages[$error_code] : 'Hệ thống thẻ Quốc tế đang bảo trì. Bạn vui lòng quay lại sau ít phút nữa');
    }

    public static function _writeLog($data, $breakLine = true, $addTime = true)
    {
        $file_name = 'cbs_stb/output/' . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    protected function _callCyberSource($params, $function)
    {
        $request = $this->_getCyberRequestMessge($params);
        $data_log = (array)$request;
        unset($data_log['card']);
        self::_writeLog('REQUEST: ' . json_encode($data_log));
        Logs::writeELKLog($data_log, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/cybersource');

        try {
            $result = $this->runTransaction($request);
            Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/cybersource');
        } catch (Exception $e) {
            Logs::writeELKLog($e->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/cybersource');

            return false;
        }
        return $result;
    }

    protected function _getCyberRequestMessge($params)
    {
        $params['merchantID'] = $this->cybersource_merchant_id;
        $params['clientLibrary'] = "PHP";
        $params['clientLibraryVersion'] = phpversion();
        $params['clientEnvironment'] = php_uname();
        return $this->_convertArrayToMessage($params);
    }

    protected function _convertArrayToMessage($params)
    {
        if (is_array($params) && !empty($params)) {
            $result = new stdClass();
            foreach ($params as $key => $value) {
                $obj = $this->_convertArrayToMessage($value);
                $result->$key = $obj;
            }
        } else {
            $result = $params;
        }
        return $result;
    }

    public static function checkVisaReview($result) {
        if ($result->decision == 'REVIEW' && $result->reasonCode == '480') {
            if ($result->ccAuthReply->reasonCode == '100') {
                return true;
            }
        } elseif ($result->decision == 'ACCEPT') {
            if (!isset($result->payerAuthEnrollReply->eci) || $result->payerAuthEnrollReply->eci == null || in_array($result->payerAuthEnrollReply->eci, ["00", "07"])) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

//    private function _insertLog($function_name, $params) {
//        $inputs = $params;
//        $inputs['bank_id'] = '73';
//        $inputs['function_name'] = $function_name;
//        $inputs['mid'] = CBS_SOAP_MERCHANT_ID;
//        $inputs['ip'] = isset($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : @$params['client_ip'];
//
//        $insertLog = Api::call('depositInsertCybersourceLog', $inputs, true);
//        if ($insertLog['error_code'] === 0) {
//            return $insertLog['response']['id'];
//        }
//        else
//            return false;
//    }

//    private function _updateLog($function_name, $params) {
//        return Api::call('depositUpdateCybersourceLog', $params);
//    }

    public function getFlexKey()
    {
        $curl = curl_init();
        //$absUrl = DESTINATION_URL.DESTINATION_RESOURCE;
        $absUrl = CBS_FLEX_SECURE_HTTPS . CBS_FLEX_HOST . "/" . CBS_FLEX_DESTINATION_RESOURCE;
        $opts = array();
        $opts [CURLOPT_POST] = 1;
        $opts [CURLOPT_POSTFIELDS] = $this->getDigestBody();
        $opts [CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        $opts [CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
        $opts [CURLOPT_URL] = $absUrl;
        $opts [CURLOPT_RETURNTRANSFER] = true;
        $opts [CURLOPT_CONNECTTIMEOUT] = 30;
        $opts [CURLOPT_TIMEOUT] = 80;
        $opts [CURLOPT_HTTPHEADER] = $this->getHeaderFlex();
        $opts [CURLOPT_HEADER] = 1;
        curl_setopt_array($curl, $opts);
        $response = curl_exec($curl);
        //loggingHelper( $response, $curl, 'testingstuff', $digestBody );
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        //printf ("\n\nResponse Status: %s\n",curl_getinfo($curl, CURLINFO_HTTP_CODE));
        //if (empty($body) == false && $body != '') {
        //$json = json_decode($body);
        //$json = json_encode($json, JSON_PRETTY_PRINT);
        //printf("Response Body : %s\n", $json);
        //echo "body: ".json_decode($body);
        $response = json_decode($body);

        if (empty($response->jwk)) {
            return false;
        }
//            print_r($response);
        $jwk = $response->jwk;
//			jdebug($jwk->kid,"KID");
        $jwk = json_encode($jwk, JSON_PRETTY_PRINT);
//			print_r($jwk);

        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl);
        return $jwk;
    }

    private function getDigestBody()
    {
        $digestBody = Array(
            "encryptionType" => CBS_FLEX_ENCRYPTION_TYPE,
            "targetOrigin" => CBS_FLEX_TARGET_ORIGIN);
        return json_encode($digestBody);
    }

    private function getHeaderFlex()
    {
        //STEP 2. Get Server Time in correct format
        $serverTime = $this->getServerTime();

        //STEP 3.  Set Headers
        $signedHeaders['host'] = CBS_FLEX_HOST;
        $signedHeaders['date'] = $serverTime;
        $signedHeaders['(request-target)'] = 'post /' . CBS_FLEX_DESTINATION_RESOURCE;
        $signedHeaders['digest'] = $this->getDigestHash();
        $signedHeaders['v-c-merchant-id'] = $this->cybersource_merchant_id;

        //Step 4.  Get the signature from the signed headers
        $signature = $this->getSignature($signedHeaders);

        //Step 5. Append all the additional data
        $signatureHeader = "";
        $signatureHeader .= "keyid=\"" . $this->cybersource_flex_key_id . "\"" . ", ";
        $signatureHeader .= "algorithm=\"" . CBS_FLEX_HMAC_SHA256 . "\"" . ", ";
        $signatureHeader .= "headers=\"" . $this->getHeadersString($signedHeaders) . "\"" . ", ";
        $signatureHeader .= "signature=\"" . $signature . "\"";

        $headers = array(
            "host: " . $signedHeaders['host'],
            "date: " . $signedHeaders['date'],
            "digest: " . $signedHeaders['digest'],
            "signature: " . $signatureHeader,
            "Content-Type: application/json; charset=utf-8",
            "v-c-merchant-id: " . $signedHeaders['v-c-merchant-id']);
        return $headers;
    }

    private function getServerTime()
    {
        return gmdate("D, d M Y H:i:s \G\M\T");
    }

    private function getDigestHash()
    {
        $digestBody = $this->getDigestBody();
        $digestHash = $this->getDigestHeader($digestBody);
        return $digestHash;
    }

    private function getHeadersString($params)
    {
        $headerStringArray = array();
        foreach ($params as $field => $value) {
            $headerStringArray[] = $field;
        }
        return implode(" ", $headerStringArray);
    }

    private function getSignature($params)
    {
        return $this->signData($this->buildDataStringToSign($params), $this->cybersource_flex_shared_secret_key);
    }

    private function signData($data, $secretKey)
    {
        //Remember, the key is provided in a base64 format, so it must be decoded before using in the hmac
        return base64_encode(hash_hmac(CBS_FLEX_SHA256, $data, base64_decode($secretKey), true));
    }

    private function buildDataStringToSign($params)
    {
        $dataStringArray = array();
        foreach ($params as $field => $value) {
            $dataStringArray[] = $field . ": " . $value;
        }
        return implode("\n", $dataStringArray);
    }

    private function getDigestHeader($params)
    {
        return "SHA-256=" . base64_encode(hash(CBS_FLEX_SHA256, $params, true));
    }

    public static function encryptSessionInfo($session_info) {
        $result = base64_encode(serialize($session_info));
        return $result;
    }

    public static function decryptSessionInfo($session_info) {
        $result = unserialize(base64_decode($session_info));
        return $result;
    }
    
    public static function _clearSessionVerifyCard($xid) {
        if (!empty(Yii::$app->session->get('TOKEN_3D_' . $xid))) {
            Yii::$app->session->remove('TOKEN_3D_' . $xid);
        }
        return true;
    }


    public static function _clearCacheVerifyCard($name_cache) {
        $result = Yii::$app->cache->flush($name_cache);
        return $result;
    }
    
    
    public static function _convertName($content) {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    public static function _processCardFullname($fullname, &$first_name = '', &$last_name = '') {
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

}