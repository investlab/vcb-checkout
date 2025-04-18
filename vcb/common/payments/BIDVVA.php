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
use Exception;
use yii\helpers\Url;


class BIDVVA
{
    const REFERENCE_NUMBER = '96293';
    const SERVICE_ID = '019005';
    const CHANNEL_NAME = 'BIDV_VA';
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

    public static function createVA($inputs, $form, $params): array
    {
        if($params['transaction_info']['merchant_id'] == 2374){
            $merchant_name = 'DH NGAN HANG HCM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233,949,1263,1431,1432,2345,2346,2353,3315,3316,3317,3461])) {
            $merchant_name = 'BV BUU DIEN';
        } elseif (in_array($params['transaction_info']['merchant_id'], [193, 194, 1434])) {
            $merchant_name = 'EMART';
        }elseif (in_array($params['transaction_info']['merchant_id'], [4094])) {
            $merchant_name = 'DH TAI CHINH MKT';
        } elseif (in_array($params['transaction_info']['merchant_id'], [119])) {
            $merchant_name = 'BV QUANG NINH';
        }elseif (in_array($params['transaction_info']['merchant_id'], [1960, 3157])) {
            $merchant_name = 'BV PHUONG NAM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1129])) {
            $merchant_name = 'BV QUANG NINH TAM UNG';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1130])) {
            $merchant_name = 'BV QUANG NINH NHA THUOC';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1387])) {
            $merchant_name = 'FUBON';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2273])) {
            $merchant_name = 'GREENTECH';
        } elseif (in_array($params['transaction_info']['merchant_id'], [176, 154, 178, 179, 180])) {
            $merchant_name = 'BV XANH PON';
        } elseif (in_array($params['transaction_info']['merchant_id'], [185])) {
            $merchant_name = 'BV VINH PHUC';
        }elseif (in_array($params['transaction_info']['merchant_id'], [168])) {
            $merchant_name = 'A08 HA NOI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [325])) {
            $merchant_name = 'A08 HCM';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [442])) {
            $merchant_name = 'CA HA NOI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [443])) {
            $merchant_name = 'CA HAI PHONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [444])) {
            $merchant_name = 'CA DA NANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [445])) {
            $merchant_name = 'CA CAN THO';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [446])) {
            $merchant_name = 'CA THANH HOA';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [447])) {
            $merchant_name = 'CA AN GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [448])) {
            $merchant_name = 'CA BA RIA VUNG TAU';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [449])) {
            $merchant_name = 'CA BAC GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [450])) {
            $merchant_name = 'CA BAC KAN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [451])) {
            $merchant_name = 'CA BAC LIEU';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [452])) {
            $merchant_name = 'CA BAC NINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [453])) {
            $merchant_name = 'CA BEN TRE';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [454])) {
            $merchant_name = 'CA BINH DINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [455])) {
            $merchant_name = 'CA BINH DUONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [456])) {
            $merchant_name = 'CA BINH PHUOC';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [459])) {
            $merchant_name = 'CA BINH THUAN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [460])) {
            $merchant_name = 'CA CA MAU';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [461])) {
            $merchant_name = 'CA CAO BANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [462])) {
            $merchant_name = 'CA DAK LAK';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [463])) {
            $merchant_name = 'CA DAK NONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [464])) {
            $merchant_name = 'CA DIEN BIEN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [465])) {
            $merchant_name = 'CA DONG NAI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [466])) {
            $merchant_name = 'CA GIA LAI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [467])) {
            $merchant_name = 'CA DONG THAP';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [468])) {
            $merchant_name = 'CA HA GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [469])) {
            $merchant_name = 'CA HA NAM';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [470])) {
            $merchant_name = 'CA HA TINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [471])) {
            $merchant_name = 'CA HAI DUONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [472])) {
            $merchant_name = 'CA HAU GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [473])) {
            $merchant_name = 'CA HOA BINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [474])) {
            $merchant_name = 'CA HUNG YEN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [475])) {
            $merchant_name = 'CA KHANH HOA';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [476])) {
            $merchant_name = 'CA KIEN GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [477])) {
            $merchant_name = 'CA KON TUM';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [478])) {
            $merchant_name = 'CA LAI CHAU';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [479])) {
            $merchant_name = 'CA LAM DONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [480])) {
            $merchant_name = 'CA LANG SON';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [481])) {
            $merchant_name = 'CA LAO CAI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [482])) {
            $merchant_name = 'CA LONG AN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [483])) {
            $merchant_name = 'CA NAM DINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [484])) {
            $merchant_name = 'CA NGHE AN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [485])) {
            $merchant_name = 'CA NINH BINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [486])) {
            $merchant_name = 'CA NINH THUAN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [3771])) {
            $merchant_name = 'VIETTOURIST';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [487])) {
            $merchant_name = 'CA PHU THO';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [488])) {
            $merchant_name = 'CA QUANG BINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [489])) {
            $merchant_name = 'CA QUANG NAM';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [490])) {
            $merchant_name = 'CA QUANG NGAI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [491])) {
            $merchant_name = 'CA QUANG TRI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [492])) {
            $merchant_name = 'CA QUANG NINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [493])) {
            $merchant_name = 'CA SOC TRANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [494])) {
            $merchant_name = 'CA SON LA';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [495])) {
            $merchant_name = 'CA TAY NINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [496])) {
            $merchant_name = 'CA THAI NGUYEN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [497])) {
            $merchant_name = 'CA THAI BINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [498])) {
            $merchant_name = 'CA THUA THIEN HUE';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [499])) {
            $merchant_name = 'CA TIEN GIANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [500])) {
            $merchant_name = 'CA TRA VINH';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [501])) {
            $merchant_name = 'CA TUYEN QUANG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [502])) {
            $merchant_name = 'CA VINH LONG';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [503])) {
            $merchant_name = 'CA YEN BAI';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [504])) {
            $merchant_name = 'CA PHU YEN';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [505])) {
            $merchant_name = 'CA VINH PHUC';
        }
        elseif (in_array($params['transaction_info']['merchant_id'], [506])) {
            $merchant_name = 'CA HO CHI MINH';
        }elseif (in_array($params['transaction_info']['merchant_id'], [3682])) {
            $merchant_name = 'DH NGOAI NGU HUE';
        }
        else{
            $merchant_name = 'NGANLUONG';
        }

        $inputs_call = [
            'header' => [
                'requestId' => "VCBPG" . $params['transaction_id']
            ],
            'body' => [
                'serviceId' => self::SERVICE_ID,
                'code' => "VCBPG" . $params['transaction_id'],
                'name' => $merchant_name,
                'amount' => $params['transaction_amount'],
                'description' => "VCBPG" . $params['transaction_id'],
                'extraInfo1' => '',
                'extraInfo2' => '',
                'extraInfo3' => '',
                'extraInfo4' => '',
                'extraInfo5' => '',
            ],
            'getBillUrl' => ROOT_URL . 'api/web/partner/bidv-va-get-bill',
            'payBillUrl' => ROOT_URL . 'api/web/partner/bidv-va-pay-bill',
        ];
        return self::call('BIDV_VA_OPENAPI_GEN_VIETQR', $inputs_call, $params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);

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
            $inputs = array(
                'fnc' => $function,
                'data' => json_encode($params),
                'channel_name' => self::CHANNEL_NAME,
                'pg_user_code' => "NL",
            );

            $inputs['checksum'] = md5(json_encode($params) . self::AUTH_KEY);

            self::_writeLog($inputs, 'INPUT', $function);
            $result = self::_call($inputs);
            self::_writeLog($result, 'OUTPUT', $function);
            if (is_array($result)) {
                if ($result['status']) {
                    return array(
                        'status' => $result['status'],
                        'error_code' => $result['error_code'],
                        'message' => $result['message'],
                        'data' => json_decode($result['data'])->msg->body->vietQRImage,
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

    private static function _writeLog($data, $mode_type, $function)
    {
        $folder_name = 'bidv_va';
        $file_name = 'partner_payment' . DS . $folder_name . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], "[" . $mode_type . "]" . json_encode($data));

        if ($mode_type == 'OUTPUT') {
            if (isset($data['status']) && $data['status']) {
                $tmp = json_decode($data['data'], true);
                unset($tmp['msg']['body']['vietQR']);
                unset($tmp['msg']['body']['vietQRImage']);
                $data = json_encode($tmp);
            }
        }

        Logs::writeELKLogPartnerPayment($data, $mode_type, $function, '', 'bidv_va');
    }

    private static function _replaceCardNumber($cardNumber)
    {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }


}