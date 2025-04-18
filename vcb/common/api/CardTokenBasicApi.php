<?php

namespace common\api;

use common\components\utils\Logs;
use common\components\utils\Validation;
use common\models\db\CheckoutOrder;
use common\models\db\CurrencyExchange;
use common\models\db\Merchant;
use common\payments\CyberSource;
use common\util\Helpers;

abstract class CardTokenBasicApi
{

    public $version;

    abstract protected function getVersion();

    abstract protected function isFunction($function);

    abstract protected function getRequest($function);

    abstract protected function validateRequest($request);

    abstract protected function processRequest($request);

    public function process($function)
    {
        $error_code = '0001';
        $data = [];
//        CyberSource::_writeLog('FUNC: ' . json_encode($function));
        if ($this->isFunction($function)) {
            $request = $this->getRequest($function);
//            CyberSource::_writeLog('RQ: ' . json_encode($request));
            $validate = $this->validateRequest($request);
//            CyberSource::_writeLog('VA: ' . json_encode($validate));
            if ($validate['error_code'] == '0000') {
                if ($request['function'] == 'payment') {
                    self::convertCurrency($request);
                }
                $process_request = $this->processRequest($request);
                if ($process_request['error_code'] == '0000') {
                    $error_code = '0000';
                    $data = $process_request['data'];
                } elseif ($process_request['error_code'] == '0102' && $request['function'] == 'payment') {
                    $error_code = $process_request['error_code'];
                    $data = $process_request['data'];
                } else {
                    $error_code = $process_request['error_code'];
                }
            } else {
                $error_code = $validate['error_code'];
            }
        }
        return [
            'result_code' => $error_code,
            'result_message' => $this->getErrorMessage($error_code),
            'result_data' => $data
        ];
    }

    protected function validateVersion($version)
    {
        return $version == $this->getVersion();
    }

    protected function validateMerchantId($merchant_id, &$merchant_info)
    {
        return Merchant::getApiKey($merchant_id, $merchant_info);
    }

    protected function validateFirstName($first_name)
    {
        return Validation::checkLength($first_name, 1);
    }

    protected function validateLastName($last_name)
    {
        return Validation::checkLength($last_name, 1);
    }

    protected function validateStreet($street)
    {
        return Validation::checkLength($street, 1);
    }

    protected function validateCity($city)
    {
        return Validation::checkLength($city, 1);
    }

    protected function validateState($state)
    {
        return Validation::checkLength($state, 1);
    }

    protected function validatePostalCode($postal_code)
    {
        return Validation::isNumber($postal_code);
    }

    protected function validateEmail($email)
    {
        return Validation::isEmail($email);
    }

    protected function validatePhone($phone)
    {
        return Validation::isMobile($phone);
    }

    protected function validateUrl($notify_url)
    {
        return Validation::isURL($notify_url);
    }

    protected function validateTokenMerchant($token_merchant)
    {
        return Validation::checkLength($token_merchant, 1);
    }

    protected function validateAmount($amount)
    {
        return $amount > 0;
    }

    private static function convertCurrency(&$value)
    {
        if (strtoupper($value['currency']) == "USD") {
            $currency_exchange = CurrencyExchange::find()->where(['currency_code' => 'USD'])->one();
            if ($currency_exchange) {
                $value['currency'] = "VND";
                $value['amount'] = $value['amount'] * (float)$currency_exchange->sell;
            }
        }

    }

    protected function validateCurrency($value)
    {
        return in_array($value, $GLOBALS['CURRENCY']);
    }

    protected function validateOrderCode($order_code)
    {
        return Validation::checkLength($order_code, 1);
    }

    protected function getErrorMessage($error_code)
    {
        $messages = [
            '0000' => 'Thành công',
            '0001' => 'Lỗi không xác định',
            '0002' => 'Tên hàm không hợp lệ',
            '0003' => 'merchant_id không hợp lệ hoặc không tồn tại',
            '0004' => 'version không hợp lệ',
            '0005' => 'order_code không hợp lệ',
            '0006' => 'order description không hợp lệ',
            '0007' => 'Định dạng số tiền không hợp lệ',
            '0008' => 'first_name không hợp lệ',
            '0009' => 'last_name không hợp lệ',
            '0010' => 'email không hợp lệ',
            '0011' => 'phone không hợp lệ',
            '0012' => 'street không hợp lệ',
            '0013' => 'return_url không hợp lệ',
            '0014' => 'cancel_url không hợp lệ',
            '0015' => 'notify_url không hợp lệ',
            '0016' => 'time_limit không hợp lệ',
            '0017' => 'Mã checksum không hợp lệ',
            '0018' => 'token_merchant không hợp lệ',
            '0019' => 'id không hợp lệ',
            '0020' => 'city không hợp lệ',
            '0021' => 'state không hợp lệ',
            '0022' => 'postal_code không hợp lệ',
            '0023' => 'Phương thức thanh toán không được hỗ trợ',
            '0024' => 'Lỗi thanh toán',
            '0025' => 'Huỷ mã thông báo lỗi',
            '0026' => 'customer_id không hợp lệ',
            '0027' => 'currency không hợp lệ',
            '0101' => 'Dữ liệu truyền lên đúng nhưng không thể tạo đơn hàng cho merchant này',
            '0102' => 'Số thẻ đã thanh toán vượt quá số lần cho phép',
        ];
        return array_key_exists($error_code, $messages) ? $messages[$error_code] : $messages['0001'];
    }

    public static function writeLog($data)
    {
        $file_name = 'api' . DS . 'card_token' . DS . date("Ymd") . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

    public static function writeLogCallback($data): bool
    {
        $now = time();
        $file_name = Helpers::initFolder("logs" . DS . "card_token_callback") . DS . date('Ymd', $now) . '.txt';
        $fp = fopen($file_name, 'a');
        if ($fp) {
            $line = date("[H:i:s, d/m/Y]: ", $now) . $data . " \n";
            fwrite($fp, $line);
            fclose($fp);
            return true;
        }
        return false;
    }

    protected function validateTokenCode($value, &$checkout_order_info = false)
    {
        return CheckoutOrder::checkTokenCode($value, $checkout_order_info);
    }

    protected function validateCustomerId($data)
    {
        return Validation::isNumber($data);
    }
}

