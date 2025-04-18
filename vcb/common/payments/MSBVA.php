<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 11/22/2019
 * Time: 10:09 AM
 */

namespace common\payments;

use common\components\libs\Tables;
use common\components\utils\Logs;
use common\util\Helpers;


class MSBVA
{
    const REFERENCE_NUMBER = '968668666';
    const CHANNEL_NAME = 'MSB_VA';
    const AUTH_KEY = 'tu8bSb0Du5Sa7xsQ31a99OhpNkqEHN6W';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

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

    public static function createVA($inputs, $form, $params): array
    {
        $account_number = $params['transaction_id'];
        $detail_1 = 'ORD_' . $params['transaction_id'];
        $inputs_call = [
            'accountNumber' => self::REFERENCE_NUMBER . Helpers::addZeroPrefix($account_number, 7),
            'referenceNumber' => self::REFERENCE_NUMBER,
            'name' => "NGAN LUONG",
            'beneficaryName' => "NGAN LUONG",
            'payType' => "0",
            'detail1' => $detail_1,
            'detail2' => "",
            'detail3' => "",
            'maxAmount' => "1000000000",
            'minAmount' => "1000",
            'equalAmount' => $params['transaction_amount'],
            'email' => "quangnt@nganluong.vn",
            'phone' => "0936156519",
            'expiryDate' => "",
            'regNumber' => "123456789",
            'status' => "1",
        ];
        return self::call('CREATE_VA', $inputs_call, $params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);

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

    private static function call($function, $params, $merchant_id, $partner_payment_id): array
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        if (!$merchant_config) {
            return [
                'error_code' => '105',
                'message' => 'Lỗi kết nối',
            ];
        } else {
            $data['rows'] = [$params];
            $inputs = array(
                'fnc' => $function,
                'data' => json_encode($data),
                'channel_name' => self::CHANNEL_NAME,
                'pg_user_code' => "NL",
            );

            $inputs['checksum'] = md5(json_encode($data) . self::AUTH_KEY);


            self::_writeLog('[INPUT] ' . json_encode($inputs));
            $result = self::_call($inputs);
            self::_writeLog('[OUTPUT] ' . json_encode($result));
            if (is_array($result)) {
                if ($result['status']) {
                    return array(
                        'status' => $result['status'],
                        'error_code' => $result['error_code'],
                        'message' => $result['message'],
                        'data' => $result['data'],
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
//            curl_setopt($ch, CURLOPT_USERPWD, trim(VCB_ECOM_HTTP_USER . ":" . trim(VCB_ECOM_HTTP_PASSWORD)));
//            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
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

    private static function _writeLog($data)
    {
        $file_name = 'partner_payment' . DS . 'msb_va' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        if (strpos($data, '[INPUT]') !== false && strpos($data, 'card_number') !== false) {

            $data_input = json_decode(json_decode(str_replace('[INPUT]', '', $data), true)['data'], true);
            $data_input['card_mask_number'] = self::_replaceCardNumber($data_input['card_number']);
            unset($data_input['card_number']);
            unset($data_input['partnerPassword']);
//            unset($data_input['merchant_id']);
//            unset($data_input['partnerId']);
            $arr_data_logs = array(
                'pg_user_code' => json_decode(str_replace('[INPUT]', '', $data), true)['pg_user_code'],
                'channel_name' => json_decode(str_replace('[INPUT]', '', $data), true)['channel_name'],
                'fnc' => json_decode(str_replace('[INPUT]', '', $data), true)['fnc'],
                'data' => json_encode($data_input),
            );
            Logs::create($pathinfo['dirname'], $pathinfo['basename'], '[INPUT]' . json_encode($arr_data_logs));
        } else {
            Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
        }

    }

    private static function _replaceCardNumber($cardNumber)
    {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }


}