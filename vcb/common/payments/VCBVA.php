<?php
/**
 * Created by PhpStorm.
 * User: tinbt
 */

namespace common\payments;

use common\components\libs\qrcode\QrCode;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\util\Helpers;
use Exception;


class VCBVA
{
    const REFERENCE_NUMBER = '96293';
    const CHANNEL_NAME = 'VCB_VA';
    const PREFIX_1 =  APP_ENV == 'prod' ? 'CPNL1' : 'VCBNL1';
    const PREFIX_2 = APP_ENV == 'prod' ? 'CPNL2' : 'VCBNL2';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    const AUTH_KEY = 'tu8bSb0Du5Sa7xsQ31a99OhpNkqEHN6W'; // KEY CHUAN
//    const AUTH_KEY = 'tu8bSb0Du5Sa'; // KEY TEST FAIL

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
//        var_dump($form);
//        var_dump($params);die();

        if($params['transaction_info']['merchant_id'] == 2374){
            $merchant_name = 'DH NGAN HANG HCM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233,949,1263,1431,1432,2345,2346,2353,3315,3316,3317,3461])) {
            $merchant_name = 'BV BUU DIEN';
        } elseif (in_array($params['transaction_info']['merchant_id'], [193, 194, 1434])) {
        $merchant_name = 'EMART';
        } elseif (in_array($params['transaction_info']['merchant_id'], [4094])) {
            $merchant_name = 'DH TAI CHINH MKT';
        } elseif (in_array($params['transaction_info']['merchant_id'], [119])) {
            $merchant_name = 'BV QUANG NINH';
        }elseif (in_array($params['transaction_info']['merchant_id'], [1960, 3157])) {
            $merchant_name = 'BV PHUONG NAM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1129])) {
            $merchant_name = 'BV QUANG NINH TAM UNG';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1130])) {
            $merchant_name = 'BV QUANG NINH NHA THUOC';
        } elseif (in_array($params['transaction_info']['merchant_id'], [3701,3702,3703,3704,3705,3706,3707,3711,3712,3713,3724,3733,3734,3735,3736,3737])) {
            $merchant_name = 'VIET HA CHI';
        }elseif (in_array($params['transaction_info']['merchant_id'], [1387])) {
            $merchant_name = 'FUBON';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2273])) {
            $merchant_name = 'GREENTECH';
        }elseif (in_array($params['transaction_info']['merchant_id'], [3771])) {
            $merchant_name = 'VIETTOURIST';
        }elseif (in_array($params['transaction_info']['merchant_id'], [176, 154, 178, 179, 180])) {
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
//        var_dump($params['transaction_info']['merchant_id']);die();
        $inputs_call = [
            'payload' => [
                'MID' =>  'VCBNL2' . Helpers::addZeroPrefix($params['transaction_id'], 13), // GOC
//                'MID' => self::PREFIX_1 . Helpers::addZeroPrefix($params['transaction_id'], 13),
//                'MID' => Helpers::addZeroPrefix($params['transaction_id'], 13), // case ko nhap prefix
//                'MID' => '43568934', // case nhap ma ko hop le
                'merchName' => $merchant_name,
                'TID' => '',
                'qrType' => '06',
                'transId' => '',
                'productId' => '',
                'billNumber' => '',
                'amount' => [
                    'amount' => '' . $params['transaction_amount']
                ],
                'expiryTime' => '',
                'remark' => '',
                'tipAndFee' => '',
                'consumerId' => '',
                'purpose' => 'VCBPG' . $params['transaction_id'],
                'inquiryBillUrl' => GET_BILL_VCB_VA_URL,
                'paymentBillUrl' => PAY_BILL_VCB_VA_URL,
            ]
        ];
        return self::call('VCB_VA_GEN_QR', $inputs_call, $params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);

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
//            $inputs['checksum'] = sha($inputs['data'] . VCB_ECOM_AUTHEN_KEY);
            $inputs['checksum'] = hash('sha256',$inputs['data'] . self::AUTH_KEY);
            self::_writeLog('[INPUT] ' . json_encode($inputs));
            self::_writeLogV2($inputs, 'INPUT', $function);
            $result = self::_call($inputs);
            // gia lap array tao qr thanh coong // XOA TREN LIVE
//            $result = [
//                'status' =>  true,
//                'error_code' =>  '0',
//                'message' =>  '',
//                'data' => '{"payload":{"data":"00020101021238630010A000000727013300069704360119VCBNL200000069853160208QRIBFTTA5802VN540572800530370462160812VCBPG698531663046AFC"},"signature":"c4c4c0c4d4b0c6ed4a6a70fb8bd44f04","context":{"messageTime":"2023-09-15T13:08:20.3477854+07:00","routing":null,"security":null,"appId":"BE","messageId":"0bf395c0-e5a6-4fab-90a0-2a9def60d493","refMessageId":"73371932-0514-494a-9ed6-1ac32ae0e29b","responseStatus":{"status":"SUCCESS","errorCode":"0","errorMessage":"","errorDetail":null}}}',
//                'request_id' => '2215c5e9-3acb-4456-b91b-40ff7cc4f8621'
//            ];


            self::_writeLog('[OUTPUT] ' . json_encode($result));
            self::_writeLogV2($result, 'OUTPUT', $function);
//            var_dump($result);die();
            /**
             * array (size=5)
            'status' => boolean true
            'error_code' => string '0' (length=1)
            'message' => string '' (length=0)
            'data' => string '{"payload":{"data":"00020101021238630010A000000727013300069704360119VCBNL212345678901240208QRIBFTTA5802VN540510000530370462220818noi dung giao dich6304C764"},"signature":"c4c4c0c4d4b0c6ed4a6a70fb8bd44f04","context":{"messageTime":"2023-09-15T13:08:20.3477854+07:00","routing":null,"security":null,"appId":"BE","messageId":"0bf395c0-e5a6-4fab-90a0-2a9def60d493","refMessageId":"73371932-0514-494a-9ed6-1ac32ae0e29b","responseStatus":{"status":"SUCCESS","errorCode":"0","errorMessage":"","errorDetail":null}}}' (length=507)
            'request_id' => string '2215c5e9-3acb-4456-b91b-40ff7cc4f860' (length=36)
             */
            if (is_array($result)) {
                if ($result['status']) {
                    return array(
                        'status' => $result['status'],
                        'error_code' => $result['error_code'],
                        'message' => $result['message'],
//                        'data' => json_decode($result['data'])->msg->body->vietQRImage,
                        'data' => self::genQRcode(json_decode($result['data'], true)['payload']['data']),
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
        $file_name = 'partner_payment' . DS . 'vcb_va' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);

    }

    private static function _writeLogV2($data, $mode_type, $function)
    {
        $folder_name = 'vcb_va';
        $file_name = 'partner_payment' . DS . $folder_name . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], "[" . $mode_type . "]" . json_encode($data));

        if ($mode_type == 'OUTPUT') {
            if (isset($data['status']) && $data['status']) {
                $tmp = $data;
//                $tmp = json_decode($data['data'], true);
//                var_dump($tmp);die();
                if(isset($tmp['data'])){
                    unset($tmp['data']); // bo phan data trong log output genQR
                }
//                var_dump($tmp);die();
//                unset($tmp['msg']['body']['vietQRImage']);
                $data = json_encode($tmp);
            }
        }

        @Logs::writeELKLogPartnerPayment($data, $mode_type, $function, '', 'vcb_va');
    }

    private static function _replaceCardNumber($cardNumber)
    {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }

    public static function genQRcode($qrData)
    {
        ob_start();
        QrCode::png(
            $qrData,
            $outfile = false,
            $level = 3,
            $size = 5,
            $margin = 4,
            $saveandprint = false

        );
        $imageString = base64_encode(ob_get_clean());
        header('Content-Type: text/html');
        if(ob_get_length()>0) ob_end_clean();
        return $imageString;
    }

    public static function getErrorMessageGenQr($error_code) {
        $arrCode = array(
            '0' => 'Thành công',
            '1' => 'Thất bại',
            '2' => 'Format message không hợp lệ',
            '3' => 'Giao dịch đã tồn tại',
            '4' => 'Service Code không đúng',
            '5' => 'Cài đặt dịch vụ không đúng',
            '6' => 'Tài khoản trích nợ không hợp lệ',
            '7' => 'Tài khoản ghi có không hợp lệ',
            '8' => 'Thanh toán thất bại',
            '13' => 'Số dư tài khoản trích nợ không đủ',
            '14' => 'Thanh toán bị time out',
            '15' => 'Giao dịch không tồn tại',
            '17' => 'Loại tiền trích nợ không hợp lệ',
            '18' => 'Tên tài khoản ghi có không hợp lệ',
            '19' => 'Phân quyền dịch vụ không hợp lệ',
            '20' => 'TK chuyên dùng không hợp lệ',
            '21' => 'Số tiền giao dịch không hợp lệ',
            '22' => 'Số tiền giao dịch lớn hơn quy định',
            '23' => 'Số tiền giao dịch nhỏ hơn quy định',
            '24' => 'Truy vấn thông tin tài khoản thụ hưởng không thành công',
            '68' => 'Thất bại không tường minh',
            '90' => 'Signature verified not successfully!',
            '91' => 'APIGW authenticated not successfully!',
            '92' => 'Partner authorized not successfully!',
            '93' => 'Internal verifications exception error!',
            '94' => 'Routing Backend not found!',
            '99' => 'Undefined MessageRouting Error!'
        );
        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }


}