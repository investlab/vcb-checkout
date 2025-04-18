<?php

namespace common\api;

use Yii;
use common\components\libs\Tables;
use common\components\utils\Validation;
use common\models\db\CheckoutOrder;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
abstract class CancelPaymentBasicApi {

    public $has_encrypt = false;
    public $merchant_info = null;

    abstract public function getVersion();

    abstract protected function _isFunction($function);

    abstract public function getData($function);

    public function process($function, $has_encrypt = false) {
        $error_code = '0001';
        $result_data = null;
        $result_message = false;
        //-------
        if ($this->_isFunction($function)) {
            $this->has_encrypt = $has_encrypt;
            $data = $this->getData($function);
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
        $this->writeLog($rs);
        return $rs;
    }

    protected function _processData($data) {
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

    protected function _validateData(&$data) {
        $error_code = '0001';
        if (is_array($data) && array_key_exists('merchant_site_code', $data)) {
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

    public function getResultMessage($result_code) {
        $message = array(
            '0000' => 'Success',
            '0001' => 'Unknown error',
            '0002' => 'Post data is invalid',
            '0003' => 'invalid merchant code',
            '0004' => 'invalid version',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    protected function _validateTimeLimit($value) {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $parts) == true) {
            $time = mktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);
            $input_time = strtotime($value);
            if ($input_time !== false) {
                return $input_time == $time;
            }
        }
        return false;
    }

    protected function _validateTokenCode($value, &$checkout_order_info = false) {
        return CheckoutOrder::checkTokenCode($value, $checkout_order_info);
    }

    protected function _validateTokenCodeAndOrderCode($token_code, $order_code, &$checkout_order_info = false) {
        return CheckoutOrder::checkTokenCodeAndOrderCode($token_code, $order_code, $checkout_order_info);
    }

    function getResult($result, $result_message = false) {
        if ($result_message != false) {
            $result['result_message'] = $result_message;
        } else {
            $result['result_message'] = $this->getResultMessage($result['result_code']);
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    function encrypt($data, $key) {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        if ($this->has_encrypt) {
            $data = $this->cryptoJsAesEncrypt($key, $data);
        }
        return $data;
    }

    function decrypt($data, $key) {
        try {
            if ($this->has_encrypt) {
                $data = $this->cryptoJsAesDecrypt($key, $data);
            }
            return json_decode($data, true);
        } catch (Exception $ex) {

        }
        return false;
    }

    protected function _validateOrderCode($value) {
        return (trim($value) != '' && strlen($value) <= 255);
    }

    function cryptoJsAesDecrypt($key, $jsonString) {
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

    function cryptoJsAesEncrypt($key, $value) {
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

    public function writeLog($data) {
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
