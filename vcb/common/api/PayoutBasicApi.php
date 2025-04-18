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
abstract class PayoutBasicApi {

    public $has_encrypt = false;
    public $merchant_info = null;

    abstract public function getVersion();

    abstract protected function _isFunction($function);

    abstract public function getData($function);

    abstract public function allowIP($ip);

    public function process($function, $has_encrypt = false) {
        $error_code = '0001';
        $result_data = null;
        $result_message = false;
        $ip = false;
        //-------       
        if ($this->allowIP($ip)) {
            if ($this->_isFunction($function)) {
                $this->has_encrypt = $has_encrypt;
                $data = $this->getData($function);
                if ($data != false) {
                    $this->writeLog('===== [INPUT ' . @$data["reference_code"] . '] ' . json_encode($data));
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
            } else {
                $error_code = '0002';
            }
        } else {
            
            $result_message = "Invalid IP";
        }
        $rs = $this->getResult(array('result_code' => $error_code, 'result_data' => $result_data), $result_message);
        $this->writeLog('[OUTPUT MERCHANT ' . @$data["reference_code"] . '] ' . $rs . '======');
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

    protected function _validateVersion($value) {
        return $value == $this->getVersion() ? true : false;
    }

    protected function _validateOrderCode($value) {
        return (trim($value) != '' && strlen($value) <= 255);
    }

    protected function _validateOrderDescription($value) {
        return (strlen($value) <= 500);
    }

    protected function _validateAmount($value) {
        return ($value >= 2000);
    }

    protected function _validateCurrency($value) {
        return in_array($value, $GLOBALS['CURRENCY']);
    }

    protected function _validateBuyerFullname($value) {
        return (trim($value) != '' && strlen($value) <= 255);
    }

    protected function _validateBuyerEmail($value) {
        return Validation::isEmail($value);
    }

    protected function _validateBuyerMobile($value) {
        return Validation::isMobile($value);
    }

    protected function _validateBuyerAddress($value) {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateReturnUrl($value) {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateCancelUrl($value) {
        return (trim($value) != '' && strlen($value) <= 500);
    }

    protected function _validateNotifyUrl($value) {
        return (strlen($value) <= 500);
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
        
        $file = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'api' . DS . 'payout' . DS . 'version' . $this->getVersion() . DS. date('Ymd') . '.txt';
        $pathinfo = pathinfo($file);
        \common\components\utils\Utilities::logs($pathinfo['dirname'], $pathinfo['basename'], $data);
        
        
    }

}
