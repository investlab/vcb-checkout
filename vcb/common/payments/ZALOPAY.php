<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 11/22/2019
 * Time: 10:09 AM
 */

namespace common\payments;

use common\components\libs\qrcode\QrCode;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\util\Helpers;
use Exception;


class ZALOPAY
{
    const REFERENCE_NUMBER = '96293';
    const CHANNEL_NAME = 'ZALOPAY';
    const PREFIX = 'VCBPG';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const AUTH_KEY = 'tu8bSb0Du5Sa7xsQ31a99OhpNkqEHN6W';

    private static function _setMerchantConfig($partner_payment_id, $merchant_id)
    {
        $partner_payment = Tables::selectOneDataTable('partner_payment_account',
            ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status",
                "merchant_id" => $merchant_id,
                "partner_payment_id" => $partner_payment_id,
                "status" => self::STATUS_ACTIVE
            ]
        );
        if (!empty($partner_payment)) {
            return [
                'partner_id' => $partner_payment['partner_payment_account'],
                'merchant_id' => $partner_payment['partner_merchant_id'],
                'merchant_password' => $partner_payment['partner_merchant_password'],
            ];
        }
        return false;
    }

    public static function createVA($params): array
    {
        $inputs_call = [
            'app_trans_id' => date('ymd') . "_" . self::PREFIX . Helpers::addZeroPrefix($params['transaction_id'], 8),
            'amount' => $params['transaction_amount'],
            'item' => json_encode([]),
            'description' => 'Thanh toan hoa don #' . $params['transaction_id'],
            'embed_data' => json_encode([
                'redirecturl' => $params['return_url']
            ]),
            'bank_code' => '',
            'device_info' => '',
            'sub_app_id' => '',
            'title' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'webNotifyUrl' => $params['notify_url'],
        ];
        return self::call('ZALOPAY_CREATE_ORDER', $inputs_call, $params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);

    }

    public static function verifyVA($params): array
    {
        $account_number = $params['transaction_id'];

        $inputs_call = [
            'vaNumber' => self::REFERENCE_NUMBER . Helpers::addZeroPrefix($account_number, 10),
            'fromDate' => $params['time_from'],
            'toDate' => $params['time_to'],
            'page' => "1",
            'rows' => "5",
        ];
        return self::call('TRANSACTION_HISTORY', $inputs_call, $params['merchant_id'], $params['partner_payment_id']);

    }

    public static function getOrderStatus($params)
    {
        $inputs_call = [
            'app_trans_id' => $params['app_trans_id'],
        ];
        return self::call('ZALOPAY_GET_ORDER_STATUS', $inputs_call, $params['merchant_id'], $params['partner_payment_id']);

    }

    private static function call($function, $params, $merchant_id, $partner_payment_id): array
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        if (!$merchant_config) {
            return [
                'error_code' => '105',
                'message' => 'Lỗi kết nối',
            ];
        } else {
            $params['app_id'] = $merchant_config['merchant_id'];
            $inputs = array(
                'fnc' => $function,
                'data' => json_encode($params),
                'channel_name' => self::CHANNEL_NAME,
                'pg_user_code' => "NL",
            );
            $inputs['checksum'] = md5(json_encode($params) . self::AUTH_KEY);

            self::_writeLog($inputs,'INPUT', $function);
            $result = self::_call($inputs);
            self::_writeLog($result, 'OUTPUT', $function);
            if (is_array($result)) {
                if ($result['status']) {
                    return array(
                        'status' => $result['status'],
                        'error_code' => $result['error_code'],
                        'message' => $result['message'],
                        'data' => json_decode($result['data']),
                        'token' => $result['request_id'],
                    );
                } else {
                    return array(
                        'status' => $result['status'],
                        'error_code' => '99',
                        'message' => $result['message'],
                        'data' => '',
                    );
                }
            } else {
                return array(
                    'status' => false,
                    'error_code' => '99',
                    'message' => 'Lỗi hệ thống',
                    'data' => '',
                );
            }


        }

    }

    private static function _call($params)
    {

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, GATEWAY_VA_ENDPOINT);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                return json_decode($result, true);
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    private static function _writeLog($data, $mode_type, $function)
    {
        $file_name = 'partner_payment' . DS . 'zalo_pay' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], "[" . $mode_type . "]" . json_encode($data));
        Logs::writeELKLogPartnerPayment($data, $mode_type, $function, '', 'zalo_pay');
    }

    public static function getErrorMessage($error_code) {
        $arrCode = array(
            '1' => 'REFUND_FAIL',
            '2' => 'APPID_INVALID',
            '53' => 'HMAC_INVALID',
            '68' => 'DUPLICATE_APPS_TRANS_ID',
            '401' => 'ILLEGAL_DATA_REQUEST',
            '402' => 'ILLEGAL_APP_REQUEST',
            '403' => 'ILLEGAL_SIGNATURE_REQUEST',
            '405' => 'ILLEGAL_CLIENT_REQUEST',
            '429' => 'LIMIT_REQUEST_REACH',
            '500' => 'SYSTEM_ERROR',
            '54' => 'TIME_INVALID',
            '63' => 'ZPW_BALANCE_NOT_ENOUGH',
            '92' => 'APPTRANSID_INVALID',
            '101' => 'ORDER_NOT_EXIST',
            '3' => 'SYSTEM_ERROR',
            '10' => 'SYSTEM_ERROR',
            '13' => 'SYSTEM_ERROR',
            '24' => 'SYSTEM_ERROR',
            '25' => 'SYSTEM_ERROR',
            '26' => 'SYSTEM_ERROR',
        );
        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }


}