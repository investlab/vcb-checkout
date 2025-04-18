<?php

namespace common\payments;

use common\components\utils\Logs;
use Yii;
use common\components\libs\NusoapClient;

class NganLuongTransfer {

    /*const URL_API = 'https://sandbox.nganluong.vn:8088/nl30/payoutTranfer.php?wsdl';

    public static $merchant_id = '30439';
    public static $merchant_password = '212325';
    public static $sender_email = 'nguyencamhue@gmail.com';*/

    public static $url_api = '';
    public static $merchant_id = '';
    public static $merchant_password = '';
    public static $sender_email = '';
    public static $receiver_email = '';

    private static function _setMerchantConfig()
    {
        self::$url_api = NGANLUONG_URL.'payoutTranfer.php?wsdl';
        self::$merchant_id = NGANLUONG_MERCHANT_ID;
        self::$merchant_password = NGANLUONG_MERCHANT_PASSWORD;
        self::$receiver_email = NGANLUONG_RECEIVER_EMAIL;

    }

    /**
     *
     * @param type $params: email
     * @return boolean
     */
    public static function getInfo($params) {
        self::_setMerchantConfig();
        $params = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'user_email' => $params['email'],
        );
        return self::_call(__FUNCTION__, $params);
    }

    /**
     *
     * @param type $params: email
     * @return boolean
     */
    public static function getBalance($params) {
        self::_setMerchantConfig();
        return self::_call(__FUNCTION__, $params);
    }

    public static function getPayoutTransaction($params) {
        self::_setMerchantConfig();

        return self::_call(__FUNCTION__, $params);
    }


    /**
     *
     * @param type $params : sender_email, receive_email, amount, reference_code
     */
    public static function tranfer($params) {
        self::_setMerchantConfig();
        return self::_call(__FUNCTION__, $params);
    }

    /**
     *
     * @param type $params : sender_email, time_created_from, time_created_to, type_filter, value_filter, type, status
     */
    public static function getTransaction($params) {
        self::_setMerchantConfig();
        $params = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'email' => $params['sender_email'],
            'time_created_from' => $params['time_created_from'],
            'time_created_to' => $params['time_created_to'],
            'type_filter' => $params['type_filter'],
            'value_filter' => $params['value_filter'],
            'type' => $params['type'],
            'status' => $params['status'],
        );
        return self::_call(__FUNCTION__, $params);
    }

    /**
     *
     * @param type $params
     * @return boolean
     */
    private static function _call($function, $params) {
        self::_writeLog('['.$function.']INPUT:' . json_encode($params).'|URL:'. self::$url_api);
        Logs::writeELKLog($params, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/nganluong_tranfer');

        try {
            $client = new NusoapClient(['url' => self::$url_api]);
            $result = $client->call($function, $params);
            if ($result != false && is_array($result)) {
                self::_writeLog('['.$function.']RESULT:' . json_encode($result));
                Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/nganluong_tranfer');

                return $result;
            }
        } catch (Exception $ex) {
            Logs::writeELKLog($ex->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/nganluong_tranfer');

            return false;
        }
        return false;
    }

    public static function getErrorMessage($error_code) {
        $arrCode = array(
            'E00' => 'Thành công',
            'E99' => ' Lỗi không xác định',
            'E03' => 'Email người chuyển chưa được cấu hình cho phép chuyển tiền',
            'E04' => 'Chuyển tiền không thành công',
            'E10' => 'Thời gian cho phép 3 tháng',
            'E11' => 'Thiếu tham số',
            'E12' => 'Email không tồn tại trên ngân lượng',
            'E13' => 'Thời gian không đúng',
            'E14' => 'Tài khoản người nhận đang trùng với tài khoản chuyển tiền',
            'E15' => 'Không có dữ liệu',
            'E16' => 'Tài khoản chuyển tiền không đủ tiền',
            'E98' => 'Sai thông tin merchant',
        );
        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }


    private static function _writeLog($data)
    {
        $file_name = 'nganluong/transfer';
        Logs::create($file_name, '', $data);
    }


}
