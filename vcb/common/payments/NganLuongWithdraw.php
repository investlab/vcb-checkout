<?php

namespace common\payments;

use common\components\utils\Logs;
use Yii;

class NganLuongWithdraw
{
    public static $url_api = '';
    public static $merchant_id = '';
    public static $merchant_password = '';
    public static $sender_email = '';

    /*
    const URL_API = 'https://sandbox.nganluong.vn:8088/nl35/withdraw.api.post.php';
    public static $merchant_id = '30439';
    public static $merchant_password = '212325';
    public static $sender_email = 'nguyencamhue@gmail.com';
    */

    private static function _setMerchantConfig()
    {
        self::$url_api = NGANLUONG_URL.'withdraw.api.post.php';
        self::$merchant_id = NGANLUONG_MERCHANT_ID;
        self::$merchant_password = NGANLUONG_MERCHANT_PASSWORD;
    }

    /**
     *
     * @param type $params : authorization_reference_code, total_amount, nganluong_accounts, bank_code, account_type, card_number, card_fullname, branch_name, zone_id, reason
     * @param nganluong_accounts: reference_code, receiver_email, amount
     * @return boolean
     */
    public static function CreateRequest($params, $write_log = true)
    {
        self::_setMerchantConfig();
        $inputs = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'receiver_email' => NGANLUONG_RECEIVER_EMAIL,
            'func' => 'SetCashoutRequest',
            'ref_code' => $params['authorization_reference_code'],
            'total_amount' => $params['total_amount'],
            'account_type' => $params['account_type'],
            'bank_code' => $params['bank_code'],
            'card_number' => $params['card_number'],
            'card_fullname' => $params['card_fullname'],
            'branch_name' => $params['branch_name'],
            'zone_id' => $params['zone_id'],
            'reason' => $params['reason'],
        );
        return self::_call($inputs, $write_log);
    }

    /**
     *
     * @param type $params: ref_code
     * @param type $write_log
     * @return type
     */
    public static function CheckRequest($params, $write_log = true)
    {
        self::_setMerchantConfig();
        $inputs = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'func' => 'CheckCashout',
            'ref_code' => $params['ref_code'],
        );
        return self::_call($inputs, $write_log);
    }

    /**
     *
     * @param type $params : sender_email, ref_code, total_amount, account_type, bank_code, card_fullname, card_number, card_month, card_year, branch_name, zone_id
     * @return boolean
     */
    /*public static function SetCashoutRequest($params, $write_log = true)
    {
        self::_setMerchantConfig();
        $inputs = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'receiver_email' => $params['sender_email'],
            'func' => 'SetCashoutRequest',
            'ref_code' => $params['ref_code'],
            'total_amount' => $params['total_amount'],
            'account_type' => $params['account_type'],
            'bank_code' => $params['bank_code'],
            'card_fullname' => $params['card_fullname'],
            'card_number' => $params['card_number'],
            'card_month' => $params['card_month'],
            'card_year' => $params['card_year'],
            'branch_name' => $params['branch_name'],
            'zone_id' => $params['zone_id'],
        );
        return self::_call($inputs, $write_log);
    }*/

    /**
     *
     * @param type $params : sender_email, ref_code, transaction_id
     */
    /*public static function CheckCashout($params, $write_log = true)
    {
        self::_setMerchantConfig();
        $inputs = array(
            'merchant_id' => self::$merchant_id,
            'merchant_password' => MD5(self::$merchant_password),
            'receiver_email' => $params['sender_email'],
            'func' => 'CheckCashout',
            'ref_code' => $params['ref_code'],
            'transaction_id' => $params['transaction_id'],
        );
        return self::_call($inputs, $write_log);
    }*/

    public static function GetZone($write_log = true)
    {
        self::_setMerchantConfig();
        $inputs = array(
            'func' => 'GetZone',
        );
        return self::_call($inputs, $write_log);
    }

    public static function getAccountTypeAndBankCode($payment_method_code)
    {
        $account_type = '';
        $bank_code = '';
        if (substr($payment_method_code, -18) == '-WITHDRAW-ATM-CARD') {
            $account_type = '2';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 18);
        } elseif (substr($payment_method_code, -20) == '-WITHDRAW-IB-OFFLINE') {
            $account_type = '3';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 20);
        }
        return array('account_type' => $account_type, 'bank_code' => $bank_code);
    }

    /**
     *
     * @param type $params
     * @return boolean
     */
    private static function _call($params, $write_log = true)
    {
        $query_string = http_build_query($params);
        if ($write_log) {
            self::_writeLog('URL:' . self::$url_api);
            self::_writeLog('INPUT:' . $query_string);
            Logs::writeELKLog($params, 'nl-vietcombank-checkout', 'INPUT', $params['func'], '', 'checkout/nganluong_withdraw');
        }
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$url_api);
            //curl_setopt($ch, CURLOPT_PORT, "8088");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if (substr(self::$url_api, 0, 5) == 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/10.0');
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            $result = curl_exec($ch);
            if ($write_log) {
                self::_writeLog('OUPUT:' . $result);
                Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $params['func'], '', 'checkout/nganluong_withdraw');
            }
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($result != '' && $status == 200) {
                $result = json_decode($result, true);
                return $result;
            }
        } catch (Exception $ex) {
            Logs::writeELKLog($ex->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $params['func'], '', 'checkout/nganluong_withdraw');

            return false;
        }
        return false;
    }

    public static function getErrorMessage($error_code)
    {
        $arrCode = array(
            '00' => 'Thành công',
            '99' => 'Lỗi không xác định',
            '01' => 'Merchant không được phép sử dụng phương thức này',
            '02' => 'Thông tin thẻ sai định dạng',
            '03' => 'Thông tin merchant không chính xác',
            '04' => 'Có lỗi trong quá trình kết nối',
            '05' => 'Số tiền không hợp lệ  ',
            '06' => 'Tên chủ thẻ không hợp lệ',
            '07' => 'Số tài khoản không hợp lệ',
            '08' => 'Ngân hàng không hỗ trợ',
            '09' => 'Bank_code không hợp lệ',
            '10' => 'Số dư tài khoản không đủ để thực hiện giao dịch',
            '11' => 'Mã tham chiếu ( ref_code ) không hợp lệ',
            '12' => 'Mã tham chiếu ( ref_code ) đã tồn tại',
            '14' => 'Function không đúng',
            '16' => 'receiver_email đang bị khóa hoặc phong tỏa không thể giao dịch',
            '17' => 'account_type không hợp lệ',

        );
        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }

    private static function _writeLog($data)
    {
        $file_name = 'nganluong/withdraw';
        Logs::create($file_name, '', $data);
    }

}
