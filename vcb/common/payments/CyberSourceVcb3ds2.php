<?php

namespace common\payments;

use common\components\utils\Logs;
use common\components\utils\Translate;
use common\components\utils\Strings;
use common\models\db\BinAcceptV2;
use common\models\db\CheckoutOrder;
use common\models\db\PartnerPaymentAccount;
use common\models\db\Transaction;
use \stdClass;
use \DOMDocument;
use Yii;

class CyberSourceVcb3ds2 extends \SoapClient
{

    public $partner_payment_account_info = null;
    public $cybersource_merchant_id;
    public $cybersource_soap_transaction_key;

    function __construct($checkoutOrder, $options = array())
    {

        parent::__construct(CBS_SOAP_WSDL_3DS2, $options);
        $this->partner_payment_account_info = $checkoutOrder["partner_payment_account_info"];
        $this->cybersource_merchant_id = $checkoutOrder["partner_payment_account_info"]['partner_merchant_id'];
        $this->cybersource_soap_transaction_key = $checkoutOrder["partner_payment_account_info"]['transaction_key'];
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


    public function authorizeCard($params)
    {
        $description = isset($params["order_description"]) ? $params["order_description"] : 'Mo ta don hang';
        $error = 'Lỗi không xác định';
        $result = null;
        $inputs = array(
            'payerAuthEnrollService' => array(
                'run' => 'true',
                'authenticationTransactionID' => $params["ProcessorTransactionId"]
            ),
            'billTo' => array(
                'city' => $params['city'],
                'country' => $params['country'],
                'email' => $params['email'],
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'postalCode' => $params['postal_code'],
                'state' => $params['state'],
                'street1' => $params['address'],
                'phoneNumber' => $params['phone'],
                'ipAddress' => trim($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : $params['client_ip'],
            ),
            'card' => array(
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
                'accountNumber' => $params['account_number'],
                'cardType' => $this->getCardTypeByCode($params['card_type']),
                'cvNumber' => $params['cvv_code'],
            ),
            'purchaseTotals' => array(
                'currency' => $params['currency'],
                'grandTotalAmount' => $params['amount'],
            ),
            'ccAuthService' => array(
                'run' => 'true',
            ),
            'ccCaptureService' => array(
                'run' => 'true',
            ),
            'shipTo' => array(
                'city' => "Ha Noi",
                'country' => "VN",
                'postalCode' => "100000",
                'state' => "",
                'street1' => $params['buyer_address'],
            ),
            'item' => array(
                'id' => '0',
                'productCode' => $params['product_code'],
                'productName' => $params['order_code'],
                'quantity' => '1',
                'productSKU' => $description,
                'unitPrice' => $params['amount'],
            ),
            'merchantReferenceCode' => $params['order_code'],
        );

        if (isset($params['reconciliationID'])) {
            $inputs['ccAuthService']['reconciliationID'] = substr($params['reconciliationID'], 0, 25);
        }
        if (isset($params['ignore_avs']) && $params['ignore_avs']) {
            $inputs['businessRules']['ignoreAVSResult'] = true;
        }
        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs_log['card']['accountNumber']);
        $data_log = (array)$inputs_log;
        unset($data_log['card']);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($data_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($result));
        if (!empty($result)) {
            if (strval($result->reasonCode) == '100') {
                $error = '';
            } else {
                $error = self::getErrorMessage(strval($result->reasonCode));
            }
        }
        return array('error' => Translate::get($error), 'result' => $result);
    }

    public function getCardTypeByCode($card_code)
    {
        $card_types = array(
            'visa' => '001',
            'mastercard' => '002',
            'americanexpress' => '003',
            'amex' => '003',
            'jcb' => '007',
        );
        return $card_types[$card_code];
    }

    public static function getErrorMessage($error_code)
    {
        $messages = array(
            '100' => Translate::get('Giao dịch thành công'),
            '101' => Translate::get('Thông tin giao dịch bị thiếu một hoặc nhiều trường dữ liệu bắt buộc'),
            '102' => Translate::get('Một hoặc nhiều trường thông tin trong giao dịch chứa dữ liệu không hợp lệ'),
            '110' => Translate::get('Một phần tiền trong số tiền thanh toán đã được xử lý thành công'),
            '150' => Translate::get('Lỗi hệ thống thanh toán, giao dịch chưa được xử lý'),
            '151' => Translate::get('Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền'),
            '152' => Translate::get('Thông tin giao dịch đã được gửi tới Cổng thanh toán quốc tế, tuy nhiên giao dịch bị trễ do đường truyền và đang được xử lý'),
            '200' => Translate::get('Giao dịch bị từ chối do địa chỉ nhận hàng không khớp với địa chỉ chủ thẻ đã khai báo'),
            '201' => Translate::get('Giao dịch chờ xử lý do ngân hàng phát hành thẻ yêu cầu bạn phải trả lời một số câu hỏi'),
            '202' => Translate::get('Thẻ đã hết hạn sử dụng, vui lòng liên hệ ngân hàng phát hành thẻ để biết thêm chi tiết'),
            '203' => Translate::get('Giao dịch bị từ chối bởi ngân hàng phát hành thẻ'),
            '204' => Translate::get('Số dư tài khoản thẻ không đủ hoặc thẻ đã hết hạn mức thanh toán'),
            '205' => Translate::get('Thẻ bị từ chối giao dịch do chủ thẻ thông báo với ngân hàng phát hành là thẻ đã bị mất hoặc bị đánh cắp'),
            '207' => Translate::get('Hệ thống ngân hàng phát hành thẻ đang bị lỗi, không thể thực hiện được giao dịch'),
            //'208'	=> Translate::get('Thẻ chưa được kích hoạt hoặc không tồn tại'),
            '208' => Translate::get('Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp'),
            '209' => Translate::get('Giao dịch bị từ chối thực hiện do Mã xác thực thẻ American Express (CID) không chính xác'),
            '210' => Translate::get('Thẻ hết hạn mức thanh toán'),
            '211' => Translate::get('Thông tin thẻ không chính xác'), //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '220' => Translate::get('Bộ vi xử lý từ chối yêu cầu dựa trên một vấn đề chung với tài khoản của khách hàng.'), ////
            '221' => Translate::get('The customer matched an entry on the processor\'s negative file.'), ///
            '222' => Translate::get('Tài khoản thẻ đang bị đóng băng bởi ngân hàng phát hành'), ///
            '230' => Translate::get('Thông tin thẻ không chính xác'), //'Mã số xác thực thẻ (CVV/CVV2) không chính xác',
            '231' => Translate::get('Số thẻ không hợp lệ'),
            '232' => Translate::get('Loại thẻ không được chấp nhận bởi hệ thống thanh toán'),
            '233' => Translate::get('Hệ thống thanh toán thẻ quốc tế không chấp nhận xử lý giao dịch'),
            '234' => Translate::get('Có lỗi giữa hệ thống Vietcombank với hệ thống thanh toán thẻ quốc tế'),
            '235' => Translate::get('Yêu cầu xử lý giao dịch với số tiền lớn hơn số tiền khi kiểm tra thông tin thẻ'),
            '236' => Translate::get('Hệ thống xử lý thẻ quốc tế đang bị lỗi, không thể thực hiện được giao dịch'),
            '237' => Translate::get('Giao dịch đã được trả lại'),
            '238' => Translate::get('Tài khoản thẻ của khách hàng đã bị trừ tiền'),
            '239' => Translate::get('Số tiền trong yêu cầu xử lý sai khác với thông tin trong giao dịch trước đó'),
            '240' => Translate::get('Bạn chọn sai loại thẻ'),
            '241' => Translate::get('Request ID không chính xác'),
            '242' => Translate::get('Yêu cầu thanh toán đã được gửi nhưng không thể trừ được tiền'),
            '243' => Translate::get('Yêu cầu thanh toán đã được gửi thực hiện hoặc bị chuyển trả ở lần trước đó'),
            '247' => Translate::get('Yêu cầu thanh toán đã bị hủy'),
            '250' => Translate::get('Yêu cầu thanh toán bị trễ do đường truyền'),
            '475' => Translate::get('Thẻ sử dụng mật khẩu xác thực giao dịch nên không thể liên kết'),
            '476' => Translate::get('Xác thực mật khẩu thanh toán (3Dsecure) không thành công'),
            '480' => Translate::get('Thẻ bị REVIEW, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp'),
            '481' => Translate::get('Giao dịch bị từ chối, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp'),
            '666' => Translate::get('Thẻ không được hỗ trợ thanh toán'),
            //'481'	=> Translate::get('Đơn hàng không được chấp nhận của bên quản trị rủi ro'),
        );
        return (array_key_exists($error_code, $messages) ? $messages[$error_code] : 'Hệ thống thẻ Quốc tế đang bảo trì. Bạn vui lòng quay lại sau ít phút nữa');
    }

    private function _writeLog($data, $breakLine = true, $addTime = true)
    {
        $file_name = 'cbs_vcb_3ds2/output/' . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    public static function _convertName($content)
    {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    public static function _processCardFullname($fullname, &$first_name = '', &$last_name = '')
    {
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

    public function createToken($params)
    {
        $error = 'Lỗi không xác định';
        $description = isset($params["order_description"]) ? $params["order_description"] : '';
        // set inputs
        $inputs = array(
            'merchantReferenceCode' => $params['reference_code'],
            'orderNumber' => $params['reference_code'],
            'merchantDefinedData' => array(
                'field1' => substr($params['account_number'], 0, 6),
            ),
            'payerAuthEnrollService' => array(
                'run' => 'true',
                'authenticationTransactionID' => $params["ProcessorTransactionId"]
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
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
                'accountNumber' => $params['account_number'],
                'cardType' => $this->getCardTypeByCode($params['card_type']),
                'cvNumber' => $params['cvv_code'],
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
            'item' => array(
                'id' => '0',
                'productCode' => $params['product_code'],
                'productName' => $params['order_code'],
                'quantity' => '1',
                'productSKU' => $description,
                'unitPrice' => $params['amount'],
            ),
        );
        // call
        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs['card']['accountNumber']);
        $this->_writeLog('[' . $params['reference_code'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($inputs_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . $params['reference_code'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));

        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        // return
        return array('error' => Translate::get($error), 'result' => $result);
    }

    protected function _callCyberSource($params, $function)
    {
        $request = $this->_getCyberRequestMessge($params);
        $data_log = (array)$request;
//        $data_log['url_cbs'] = CBS_SOAP_WSDL_3DS2;
        unset($data_log['card']);
        $this->_writeLog(__FUNCTION__ . '[INPUT]' . json_encode($data_log));
        Logs::writeELKLog($data_log, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/cybersource_vcb');

        try {
            $result = $this->runTransaction($request);
            $this->_writeLog(__FUNCTION__ . '[OUTPUT]' . json_encode($result));
            Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/cybersource_vcb');
        } catch (\Exception $e) {
            @$this->_writeLog('[Error]' . __FUNCTION__ . '[INPUT]' . $e->getMessage());
            Logs::writeELKLog($e->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/cybersource_vcb');

            return null;
        }
        return $result;
    }

    public function authorizeSubcription($params, $turnOff3d = false)
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
            'ccAuthService' => array(
                'run' => 'true',
            ),
        );

        if ($turnOff3d) {
            $inputs['payerAuthEnrollService'] = null;
        } else {
            $inputs['payerAuthEnrollService'] = array(
                'run' => 'true',
                'referenceID' => $params['referenceID'],
            );
        }

        if (isset($params['ProcessorTransactionId']) && $params['ProcessorTransactionId'] != "") {
            $inputs['payerAuthEnrollService']['authenticationTransactionID'] = $params['ProcessorTransactionId'];
        }
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));
        return $result;
    }

    public function enrrolmentSubcription($params)
    {
        $inputs = array(
            'merchantReferenceCode' => $params['cashin_id'],
            'purchaseTotals' => array(
                'currency' => 'VND',
                'grandTotalAmount' => $params['cashin_amount']
            ),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token']
            ),
            'payerAuthEnrollService' => array(
                'run' => 'true',
                'referenceID' => $params['referenceID'],
            ),
//            'ccAuthService' => array(
//                'run' => 'true',
//            ),
        );
        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));
        return $result;
    }

    public function authSetupSubcription($params, $turnOff3d = false)
    {
        $inputs = array(
            'merchantReferenceCode' => $params['cashin_id'],
            'purchaseTotals' => array(
                'currency' => 'VND',
                'grandTotalAmount' => $params['cashin_amount']
            ),
            'recurringSubscriptionInfo' => array(
                'subscriptionID' => $params['token']
            ),
            'payerAuthSetupService' => array(
                'run' => 'true',
            ),
        );

        $input_logs = $inputs;
        unset($input_logs['recurringSubscriptionInfo']);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[INPUT]' . json_encode($input_logs));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . $params['cashin_id'] . ']' . __FUNCTION__ . '[OUTPUT]' . json_encode($result));

        if (strval($result->reasonCode) == '100') {
            $error = '';
        } else {
            $error = self::getErrorMessage(strval($result->reasonCode));
        }
        return array('error' => Translate::get($error), 'result' => $result);
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

    public static function isSuccess($result): bool
    {
        $eciSuccess = array('01', '02', '05', '06');
        if (isset($result->payerAuthEnrollReply->eci)) {
            $eci = $result->payerAuthEnrollReply->eci;
        } elseif (isset($result->payerAuthEnrollReply->eciRaw)) {
            $eci = $result->payerAuthEnrollReply->eciRaw;
        } else {
            $eci = '';
        }

        if ($result->decision == 'ACCEPT' &&
            isset($result->ccAuthReply->reasonCode) &&
            $result->ccAuthReply->reasonCode == '100' &&
            isset($result->ccAuthReply->authorizationCode) &&
            $result->ccAuthReply->authorizationCode != "" &&
            in_array($eci, $eciSuccess)) {
            return true;
        } else {
            return false;
        }
    }

    public static function canReversalTransaction($result): bool
    {
        if (isset($result->ccAuthReply->authorizationCode) &&
            $result->ccAuthReply->authorizationCode != "") {
            return true;
        } else {
            return false;
        }
    }

    public static function isReview($result): bool
    {
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

    public static function isReject($result): bool
    {
        if ($result->decision == 'REJECT' && !in_array($result->reasonCode, ['480', '100'])) {
            return true;
        }
        return false;
    }

    public static function checkVisaReviewForCardToken($result)
    {
        if (in_array($result->reasonCode, ['480', '481'])) {
            if (!isset($result->payerAuthEnrollReply->eci)) {
                return true;
            } else {
                if (in_array($result->payerAuthEnrollReply->eci, ['00', '07', '05']) || $result->payerAuthEnrollReply->eci == null) {
                    return true;
                }
            }
        }
        return false;
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
        return array('error' => Translate::get($error), 'result' => $result);
    }

    public function checkEnroll($params)
    {
        $description = isset($params["order_description"]) ? $params["order_description"] : '';
        $error = 'Lỗi không xác định';
        $result = null;
        $inputs = array(
            'payerAuthEnrollService' => array(
                'run' => 'true',
//                'authenticationTransactionID' => $params["ProcessorTransactionId"],
                'referenceID' => $params['referenceID'],
            ),
            'billTo' => array(
                'city' => $params['city'],
                'country' => $params['country'],
                'email' => $params['email'],
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'postalCode' => $params['postal_code'],
                'state' => $params['state'],
                'street1' => $params['address'],
                'phoneNumber' => $params['phone'],
                'ipAddress' => trim($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : $params['client_ip'],
//                'ipAddress' => '14.177.239.244',
            ),
            'card' => array(
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
                'accountNumber' => $params['account_number'],
                'cardType' => $params['card_type'],
//                'cvNumber' => $params['cvv_code'],
            ),
//            'ccAuthService' => array(
//                'run' => 'true',
//            ),
//            'ccCaptureService' => array(
//                'run' => 'true',
//            ),
            'purchaseTotals' => array(
                'currency' => $params['currency'],
                'grandTotalAmount' => $params['amount'],
            ),
            'merchantReferenceCode' => $params['order_code'],
        );
        if (isset($params['ignore_avs']) && $params['ignore_avs']) {
            $inputs['businessRules']['ignoreAVSResult'] = true;
        }
        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs_log['card']['accountNumber']);
        $data_log = (array)$inputs_log;
        unset($data_log['card']);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($data_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($result));
        return $result;
    }

    public function stepOneAuthSetup($params)
    {
        $description = isset($params["order_description"]) ? $params["order_description"] : '';
        $error = 'Lỗi không xác định';
        $result = null;
        $inputs = array(
            'payerAuthSetupService' => array(
                'run' => 'true',
            ),
            'billTo' => array(
                'city' => $params['city'],
                'country' => $params['country'],
                'email' => $params['email'],
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'postalCode' => $params['postal_code'],
                'state' => $params['state'],
                'street1' => $params['address'],
                'phoneNumber' => $params['phone'],
                'ipAddress' => trim($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : $params['client_ip'],
//                'ipAddress' => '14.177.239.244',
            ),
            'card' => array(
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
                'accountNumber' => $params['account_number'],
                'cardType' => $params['card_type'],
//                'cvNumber' => $params['cvv_code'],
            ),
            'purchaseTotals' => array(
                'currency' => $params['currency'],
                'grandTotalAmount' => $params['amount'],
            ),
            'merchantReferenceCode' => $params['order_code'],
        );
        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs_log['card']['accountNumber']);
        $data_log = (array)$inputs_log;
        unset($data_log['card']);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($data_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($result));
        return $result;
    }

    public static function getInvalidField($response)
    {
        if (isset($response->invalidField) && $response->invalidField != "") {
            $arr = explode("/", $response->invalidField);
            if (count($arr) > 1) {
                $rs = [];
                foreach ($arr as $key => $item) {
                    if ($key > 0) {
                        $rs[] = str_replace("c:", "", $item);
                    }
                }
                return $rs;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public static function checkChallenge($enrollmentServiceResponse): bool
    {
        if ($enrollmentServiceResponse != null) {
            if (
                isset($enrollmentServiceResponse->reasonCode)
                && $enrollmentServiceResponse->reasonCode == "475"
                && isset($enrollmentServiceResponse->payerAuthEnrollReply->acsURL)
                && isset($enrollmentServiceResponse->payerAuthEnrollReply->paReq)
                && isset($enrollmentServiceResponse->payerAuthEnrollReply->authenticationTransactionID)
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getCustomerField($inputs): array
    {
        $arr_field['merchant_define_data'] = self::getMerchantDefineData($inputs);
        return $arr_field;
    }

    public static function getMerchantDefineData($inputs): array
    {
        $arr_field = [];
        $re = '/(?<=merchant_define_data)\d+/';
        foreach ($inputs as $key => $value) {
            preg_match($re, $key, $matches, PREG_OFFSET_CAPTURE, 0);
            if (!empty($matches) && !in_array($matches[0][0], array()) && $matches[0][0] >= 6 /*1-5 not ready in Cybersource*/) {
                $arr_field[$matches[0][0]] = $value;
            }
        }
        return $arr_field;

    }

    public function authorizeCardAndCreateToken($params): array
    {
        $description = isset($params["order_description"]) ? $params["order_description"] : 'Mo ta don hang';
        $error = 'Lỗi không xác định';
        $inputs = array(
            'subscription' => array(
                'title' => 'VCB Create Customer Token',
                'paymentMethod' => 'credit card',
            ),
            'payerAuthEnrollService' => array(
                'run' => 'true',
                'authenticationTransactionID' => $params["ProcessorTransactionId"]
            ),
            'recurringSubscriptionInfo' => array(
                'frequency' => 'on-demand',
            ),
            'paySubscriptionCreateService' => array(
                'run' => 'true',
            ),
            'billTo' => array(
                'city' => $params['city'],
                'country' => $params['country'],
                'email' => $params['email'],
                'firstName' => $params['first_name'],
                'lastName' => $params['last_name'],
                'postalCode' => $params['postal_code'],
                'state' => $params['state'],
                'street1' => $params['address'],
                'phoneNumber' => $params['phone'],
                'ipAddress' => trim($params['client_ip']) == '' ? @$_SERVER['REMOTE_ADDR'] : $params['client_ip'],
            ),
            'card' => array(
                'expirationMonth' => $params['expiration_month'],
                'expirationYear' => $params['expiration_year'],
                'accountNumber' => $params['account_number'],
                'cardType' => $this->getCardTypeByCode($params['card_type']),
                'cvNumber' => $params['cvv_code'],
            ),
            'purchaseTotals' => array(
                'currency' => $params['currency'],
                'grandTotalAmount' => $params['amount'],
            ),
            'ccAuthService' => array(
                'run' => 'true',
            ),
            'ccCaptureService' => array(
                'run' => 'true',
            ),
            'shipTo' => array(
                'city' => "Ha Noi",
                'country' => "VN",
                'postalCode' => "100000",
                'state' => "",
                'street1' => $params['buyer_address'],
            ),
            'item' => array(
                'id' => '0',
                'productCode' => $params['product_code'],
                'productName' => $params['order_code'],
                'quantity' => '1',
                'productSKU' => $description,
                'unitPrice' => $params['amount'],
            ),
            'merchantReferenceCode' => $params['order_code'],
        );

        if (isset($params['merchantDefinedData'])) {
            $inputs['merchantDefinedData'] = $params['merchantDefinedData'];
        }
        if (isset($params['reconciliationID'])) {
            $inputs['ccAuthService']['reconciliationID'] = substr($params['reconciliationID'], 0, 25);
        }
        if (isset($params['ignore_avs']) && $params['ignore_avs']) {
            $inputs['businessRules']['ignoreAVSResult'] = true;
        }

        $inputs_log = $inputs;
        $inputs_log['card']['accountNumber'] = Strings::encodeCreditCardNumber($inputs_log['card']['accountNumber']);
        $data_log = (array)$inputs_log;
        unset($data_log['card']);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($data_log));
        $result = $this->_callCyberSource($inputs, __FUNCTION__);
        $this->_writeLog('[' . __FUNCTION__ . ']' . '[' . $inputs['merchantReferenceCode'] . ']' . json_encode($result));
        if (!empty($result)) {
            if (strval($result->reasonCode) == '100') {
                $error = '';
            } else {
                $error = self::getErrorMessage(strval($result->reasonCode));
            }
        }
        return array('error' => Translate::get($error), 'result' => $result);
    }

    public static function getTypeCardByFirstBINNumber($bin_number, $in_store = true)
    {
        $raw_bin = $bin_number;

        $bin_number = substr($bin_number, 0, 6);

        if ($in_store) {
            $check_bin = BinAcceptV2::find()->where(['code' => $bin_number])->exists();
        } else {
            $check_bin = true;
        }

        if ($check_bin) {
            $visa = "/^4[0-9]{12}(?:[0-9]{3})?$/";
            $masterCard = "/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/";
            $amex = "/^3[47][0-9]{13}$/";
            $jcb = "/^(3(?:088|096|112|158|337|5(?:2[89]|[3-8][0-9]))\d{12})$/";

            // Kiểm tra và trả về loại thẻ
            if (preg_match($visa, $raw_bin)) {
                return "visa";
            }
            if (preg_match($masterCard, $raw_bin)) {
                return "mastercard";
            }
            if (preg_match($amex, $raw_bin)) {
                return "amex";
            }
            if (preg_match($jcb, $raw_bin)) {
                return "jcb";
            }
            return false;
        } else {
            return false;
        }


    }

    public static function checkAutoSettle($merchant_config): bool
    {
        if (empty($merchant_config)) {
            return true;
        } else {
            if (isset($merchant_config['AUTO_SETTLE_CYBER_SOURCE']) && $merchant_config['AUTO_SETTLE_CYBER_SOURCE'] == MerchantConfig::AUTO_SETTLE_CYBER_SOURCE_ON) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function checkAutoReversal($merchant_config)
    {
        if (empty($merchant_config)) {
            return true;
        } else {
            if (isset($merchant_config['AUTO_REVERSAL_CYBER_SOURCE']) && $merchant_config['AUTO_REVERSAL_CYBER_SOURCE'] == MerchantConfig::AUTO_REVERSAL_CYBER_SOURCE_ON) {
                return true;
            } else {
                return false;
            }
        }
    }
}



