<?php

namespace common\api;

use common\components\utils\Logs;
use common\models\db\CurrencyExchange;
use common\models\db\Merchant;
use Yii;
use common\components\libs\Tables;
use common\components\utils\Validation;
use common\models\db\CheckoutOrder;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
abstract class CheckoutBasicApi
{

    public $has_encrypt = false;
    public $merchant_info = null;

    abstract public function getVersion();

    abstract protected function _isFunction($function);

    abstract public function getData($function);

    public function process($function, $has_encrypt = false)
    {
        $error_code = '0001';
        $result_data = null;
        $result_message = false;
        $req_id = uniqid();
        //-------

        if ($this->_isFunction($function)) {
            $this->has_encrypt = $has_encrypt;
            $data = $this->getData($function);
            Logs::writeELKLogNew($data, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/api', 'web', $req_id);
            if ($data != false) {
                $this->writeLog(json_encode($data));
                $check = $this->_validateData($data);
                if ($check['error_code'] === '0000') {
                    $result = $this->_processData($data);
                    if ($result['error_code'] == '0000') {
                        $error_code = '0000';
                        $result_data = $result['result_data'];
                    } else {
                        $error_code = $result['error_code'];
                    }
                } else {
                    $error_code = $check['error_code'];
                }
            } else {
                $error_code = '0002';
            }
        }

        $rs = $this->getResult(array('result_code' => $error_code, 'result_data' => $result_data), $result_message);
        Logs::writeELKLogNew($rs, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/api', 'web', $req_id);
        if ($this->getData($function) != false) {
            $this->writeLog($rs);
        }
        return $rs;
    }

    protected function _processData($data)
    {
        $error_code = '0001';
        $result_data = null;
        $method_name = '_' . lcfirst($data['function']);
        if (method_exists($this, $method_name)) {
            $result = $this->$method_name($data);
            if ($result['error_code'] == '0000') {
                $error_code = '0000';
                $result_data = $result['result_data'];
            } else {
                $error_code = $result['error_code'];
            }
        }
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }

    protected function _validateData(&$data)
    {
        $error_code = '0001';
        if (is_array($data) && array_key_exists('merchant_site_code', $data) || $data['function'] != 'CreateOrder') {
            $method_name = '_validateData' . ucfirst($data['function']);
            if (method_exists($this, $method_name)) {
                $check = $this->$method_name($data);
                if ($check['error_code'] == '0000') {
                    $error_code = '0000';
                } else {
                    $error_code = $check['error_code'];
                }
            }
        } else {
            $error_code = '0003';
        }
        return array('error_code' => $error_code);
    }

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Success',
            '0001' => 'Unknown error',
            '0002' => 'Post data is invalid',
            '0003' => 'invalid merchant code',
            '0004' => 'invalid version',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    protected function _validateVersion($value)
    {
        return $value == $this->getVersion() ? true : false;
    }

    protected function _validateOrderCode($value)
    {
        return (trim($value) != '' && strlen($value) <= 255);
    }

    protected function _validateMerchantSiteCode($merchant_id)
    {
        return Merchant::find()
            ->where(['id' => $merchant_id])
            ->andWhere(['status' => Merchant::STATUS_ACTIVE])
            ->one();
    }

    protected function _validateOrderDescription($value)
    {
        return (strlen($value) <= 500);
    }

    protected function _validateAmount(&$value)
    {
        if (strtoupper($value['currency']) == "VND") {
            return (intval($value['amount']) >= 2000);
        } elseif (strtoupper($value['currency']) == "USD") {
            $currency_exchange = CurrencyExchange::find()->where(['currency_code' => 'USD'])->one();
            if ($currency_exchange) {
                $value['currency'] = "VND";
                $params_currency_exchange = [
                    'currency_code' => $currency_exchange->currency_code,
                    'buy' => (float)$currency_exchange->buy,
                    'transfer' => (float)$currency_exchange->transfer,
                    'sell' => (float)$currency_exchange->sell,
                ];
                $value['currency_exchange'] = json_encode($params_currency_exchange);
                $value['amount'] = $value['amount'] * (float)$currency_exchange->transfer;
                return true;
            }
            return false;
        }
        return false;
    }

    protected function _validateCurrency($value)
    {
        return in_array($value, $GLOBALS['CURRENCY']);
    }

    protected function _validateBuyerFullname($value)
    {
        return (trim($value) != '' && strlen($value) <= 255);
    }

    protected function _validateBuyerEmail($value)
    {
        return Validation::isEmail($value);
    }

    protected function _validateBuyerEmailV2($value, $merchant_site_code)
    {
        if (in_array($merchant_site_code, $GLOBALS['MERCHANT_BCA'])) {
            return true;
        }
        return Validation::isEmail($value);
    }

    protected function _validateBuyerMobile($value, $merchant_site_code)
    {
        $valid_merchant_site_codes = [
            '7', '1320', '89', '19', '120', '91', '79', '154', '176', '178', '179', '180',
            '169', '184', '160', '270', '205', '204', '206', '207', '208', '209', '210',
            '211', '212', '213', '214', '215', '216', '217', '218', '219', '220', '221',
            '222', '223', '224', '225', '226', '227', '228', '229', '230', '231', '232',
            '233', '263', '858', '949', '956', '1129', '1130', '1263', '1431', '1432',
            '1618', '2345', '2346', '2353', '2311', '2748', '4094', '3032', '3315', '3316', '3317',
            '3353', '3461',
            '192', // nguyen phat
            '376', // nguyen phat
            '2882', // nguyen phat
        ];
        if (in_array($merchant_site_code, $valid_merchant_site_codes)) {
            return true;
        }
        if (in_array($merchant_site_code, $GLOBALS['MERCHANT_BCA'])) {
            return true;
        }


        if ($value == "0987654321") {
            return false; // minh chứng cho sự cố chấp
        }

        return Validation::isPhoneNumberVN($value);
    }

    protected function _validateBuyerAddress($value)
    {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateReturnUrl($value)
    {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateCancelUrl($value)
    {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateNotifyUrl($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return (strlen($value) <= 500);
        } else {
            return false;
        }

    }

    protected function _validateNotifyUrlSeamless($value)
    {
        return (strlen($value) <= 500);


    }

    protected function _validateNotifyUrlV1($value, $merchant_id)
    {
        if ($merchant_id == '154' || $merchant_id == '7') {
            return true;
        } else {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return (strlen($value) <= 500);
            } else {
                return false;
            }
        }

    }

    protected function _validateTimeLimit($value)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $parts) == true) {
            $time = mktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);
            $input_time = strtotime($value);
            if ($input_time !== false) {
                return $input_time == $time;
            }
        }
        return false;
    }

    protected function _validatePaymentMethodCode($payment_method_code): bool
    {
        $payment_method_code_accept = [
            'CREDIT-CARD'
        ];
        return in_array($payment_method_code, $payment_method_code_accept);
    }

    protected function _validateBankCode($bank_code): bool
    {
        $bank_code_accept = [
            'VISA',
            'MASTERCARD',
            'JCB',
            'AMEX',
        ];
        return in_array($bank_code, $bank_code_accept);
    }

    protected function _validateTokenCode($value, &$checkout_order_info = false)
    {
        return CheckoutOrder::checkTokenCode($value, $checkout_order_info);
    }

    function getResult($result, $result_message = false)
    {
        if ($result_message != false) {
            $result['result_message'] = $result_message;
        } else {
            $result['result_message'] = $this->getResultMessage($result['result_code']);
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    function encrypt($data, $key)
    {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        if ($this->has_encrypt) {
            $data = $this->cryptoJsAesEncrypt($key, $data);
        }
        return $data;
    }

    function decrypt($data, $key)
    {
        try {
            if ($this->has_encrypt) {
                $data = $this->cryptoJsAesDecrypt($key, $data);
            }
            return json_decode($data, true);
        } catch (Exception $ex) {

        }
        return false;
    }

    function cryptoJsAesDecrypt($key, $jsonString)
    {
        $jsondata = json_decode($jsonString, true);
        if (is_array($jsondata)) {
            try {
                $salt = hex2bin(@$jsondata["s"]);
                $iv = hex2bin(@$jsondata["iv"]);
            } catch (Exception $e) {
                return null;
            }
            $ct = base64_decode(@$jsondata["ct"]);
            $concatedPassphrase = $key . $salt;
            $md5 = array();
            $md5[0] = md5($concatedPassphrase, true);
            $result = $md5[0];
            for ($i = 1; $i < 3; $i++) {
                $md5[$i] = md5($md5[$i - 1] . $concatedPassphrase, true);
                $result .= $md5[$i];
            }
            $key = substr($result, 0, 32);
            $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
            return json_decode($data, true);
        }
        return null;
    }

    function cryptoJsAesEncrypt($key, $value)
    {
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx . $key . $salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        return json_encode(array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt)));
    }

    public function writeLog($data)
    {
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'api' . DS . 'checkout' . DS . 'version' . $this->getVersion() . DS;
        if (is_dir($log_path) || mkdir($log_path, 0777, true)) {
            $log_file = date('Ymd') . '.txt';
            $file = fopen($log_path . $log_file, 'a+');
            if ($file) {
                fwrite($file, '[' . date('H:i:s, d/m/Y') . '] ' . $data . "\n");
                fclose($file);
                return true;
            }
        }
        return false;
    }

}
