<?php

namespace common\api;

use common\components\libs\Tables;
use common\models\db\Merchant;
use common\util\Helpers;

abstract class CardVoucherBasicApi
{
    public $has_encrypt = false;


    abstract public function getVersion();

    abstract protected function _isFunction($function);

    abstract public function getData($function);

    final protected function _checkMerchant($merchant_id): array
    {
        $error_code = '99';
        $merchant_info = null;
        //------
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $merchant_id]);
        if ($merchant_info) {
            if ($merchant_info['status'] == Merchant::STATUS_ACTIVE) {
                $error_code = '00';
            } else {
                $error_code = '03';
            }
        } else {
            $error_code = '03';
        }
        return array('error_code' => $error_code, 'merchant_info' => $merchant_info);
    }

    public function process($function, $has_encrypt = false)
    {
        $error_code = '0001';
        $result_data = null;
        $result_message = false;
        //-------

        if ($this->_isFunction($function)) {
            $this->has_encrypt = $has_encrypt;
            $data = $this->getData($function);
            if ($data) {
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
        if ($this->getData($function)) {
            $this->writeLog(json_encode(array('result_code' => $error_code, 'result_data' => $result_data)));
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


    function getResult($result, $result_message = false)
    {
        if ($result_message != false) {
            $result['result_message'] = $result_message;
        } else {
            $result['result_message'] = $this->getResultMessage($result['result_code']);
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    protected function _validateData(&$data): array
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

    public function getResultMessage($result_code): string
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


    final public function writeLog($data): bool
    {
        $now = time();
        $file_name = Helpers::initFolder("logs" . DS . "api" . DS . "card_voucher" . DS . "version" . $this->getVersion()) . DS . date('Ymd', $now) . '.txt';
        $fp = fopen($file_name, 'a');
        if ($fp) {
            $line = date("[H:i:s, d/m/Y]: ", $now) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }




}