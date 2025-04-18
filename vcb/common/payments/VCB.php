<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 11/22/2019
 * Time: 10:09 AM
 */

namespace common\payments;

use checkout\controllers\CallBackController;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Strings;


class VCB
{
    public static $pg_user_code = '';
    public static $authenKey = '';
    public static $http_user = '';
    public static $http_password = '';
    public static $url_api = '';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

//echo 1234;

    private static function _setMerchantConfig($partner_payment_id, $merchant_id)
    {
        self::$url_api = VCB_ECOM_ENDPOINT;
        $partner_payment = Tables::selectOneDataTable('partner_payment_account', ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status", "merchant_id" => $merchant_id, "partner_payment_id" => $partner_payment_id, "status" => self::STATUS_ACTIVE]);
        if (!empty($partner_payment)) {
            return [
                'partner_id' => $partner_payment['partner_payment_account'],
                'merchant_id' => $partner_payment['partner_merchant_id'],
                'merchant_password' => $partner_payment['partner_merchant_password'],
                'terminal_id' => $partner_payment['partner_id_vcb'],

            ];
        }
        return false;
    }

    public static function verifyCard($params, $merchant_id, $partner_payment_id)
    {
        return self::call('VerifyCard', $params, $merchant_id, $partner_payment_id);
    }

    public static function QrCodePayment($inputs, $form, $params)
    {
//        var_dump($params);
//        var_dump($form);die();
        $merchant_config = self::_setMerchantConfig($params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);
        if (in_array($params['transaction_info']['merchant_id'], [193, 194, 1434])) {
            $merchant_name = 'EMART';
        } elseif (in_array($params['transaction_info']['merchant_id'], [119])) {
            $merchant_name = 'BV QUANG NINH';
        }elseif (in_array($params['transaction_info']['merchant_id'], [1960, 3157])) {
            $merchant_name = 'BV PHUONG NAM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1129])) {
            $merchant_name = 'BV QUANG NINH TAM UNG';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2838])) {
            $merchant_name = 'MEDLATEC TAY HO';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2839])) {
            $merchant_name = 'MEDLATEC THANH XUAN';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2915])) {
            $merchant_name = 'MEDLATEC BA DINH';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2748])) {
            $merchant_name = 'TRUONG DT BD CB';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2374])) {
            $merchant_name = 'DH NGAN HANG HCM';
        } elseif (in_array($params['transaction_info']['merchant_id'], [4094])) {
            $merchant_name = 'DH TAI CHINH MKT';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1130])) {
            $merchant_name = 'BV QUANG NINH NHA THUOC';
        } elseif (in_array($params['transaction_info']['merchant_id'], [1387])) {
            $merchant_name = 'FUBON';
        } elseif (in_array($params['transaction_info']['merchant_id'], [3771])) {
            $merchant_name = 'VIETTOURIST';
        } elseif (in_array($params['transaction_info']['merchant_id'], [2273])) {
            $merchant_name = 'GREENTECH';
        }  elseif (in_array($params['transaction_info']['merchant_id'], [3701,3702,3703,3704,3705,3706,3707,3711,3712,3713,3724,3733,3734,3735,3736,3737])) {
            $merchant_name = 'VIET HA CHI';
        } elseif (in_array($params['transaction_info']['merchant_id'], [203,267])) {
            $merchant_name = 'BV HUNG VUONG';
        } elseif (in_array($params['transaction_info']['merchant_id'], [176, 154, 178, 179, 180])) {
            $merchant_name = 'BV XANH PON';
        } elseif (in_array($params['transaction_info']['merchant_id'], [185])) {
            $merchant_name = 'BV VINH PHUC';
        } elseif (in_array($params['transaction_info']['merchant_id'], [3353])) {
            $merchant_name = 'HV BAO CHI';
        } elseif (in_array($params['transaction_info']['merchant_id'], [204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233,949,1263,1431,1432,2345,2346,2353,3315,3316,3317,3461])) {
            $merchant_name = 'BV BUU DIEN';
        } elseif (in_array($params['transaction_info']['merchant_id'], [3119])) {
            $merchant_name = 'CTY TNHH KDTM XNK HA VY';
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
        } elseif (in_array($params['transaction_info']['merchant_id'], [3682])) {
            $merchant_name = 'DH NGOAI NGU HUE';
        }
        else {
            $merchant_name = Strings::_convertToVCBSMS($form->checkout_order['merchant_info']['name']);
        }
//        $merchant_name = Strings::_convertToVCBSMS($form->checkout_order['merchant_info']['name']);
        $trans_id = '' . $GLOBALS['PREFIX'] . $params['transaction_id'];
        $bill_no = '' . $GLOBALS['PREFIX'] . $params['transaction_id'];
//        $pay_method  = '01';
        $pay_method = '03';

        $amount = '' . $params['transaction_amount'];
        $fee_amount = '';
        $purpose = 'Thong tin hoa don';
        $merchant_code = $form->checkout_order['merchant_info']['merchant_code'];
        if($params['transaction_info']['merchant_id'] == 7){
            $inputs_call = [
                'merchant_name' => $merchant_name,
                'terminal_id' => $merchant_config['terminal_id'],
                'trans_id' => $trans_id,
                'bill_no' => $bill_no,
                'pay_method' => $pay_method,
                'amount' => $amount,
                'fee_amount' => $fee_amount,
                'purpose' => $purpose,
                'merchant_code' => $merchant_code,
                'expire_date' => date('ymdHi', time() + 60)
            ];
        } else{
            $inputs_call = [
                'merchant_name' => $merchant_name,
                'terminal_id' => $merchant_config['terminal_id'],
                'trans_id' => $trans_id,
                'bill_no' => $bill_no,
                'pay_method' => $pay_method,
                'amount' => $amount,
                'fee_amount' => $fee_amount,
                'purpose' => $purpose,
                'merchant_code' => $merchant_code
            ];
        }


        return self::callQR('QrCodePayment', $inputs_call, $params['transaction_info']['merchant_id'], $params['transaction_info']['partner_payment_id']);
    }

    public static function VerifyOtp($params, $merchant_id, $partner_payment_id)
    {
        return self::call('VerifyOtp', $params, $merchant_id, $partner_payment_id);

    }

    public static function query($params, $merchant_id, $partner_payment_id)
    {
        return self::call('Query', $params, $merchant_id, $partner_payment_id);
    }

    public static function refund($params, $merchant_id, $partner_payment_id)
    {
        return self::call('Refund', $params, $merchant_id, $partner_payment_id);
    }

    private static function call($function, $params, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        if (!$merchant_config) {

            return [
                'error_code' => '105',
                'message' => 'Lỗi kết nối',
            ];
        } else {
            if (empty($merchant_config['merchant_id'])) {

                return [
                    'error_code' => '105',
                    'message' => 'Lỗi kết nối',

                ];
            } else {
//                $file_name = 'data/logs/vcb_ecom/' . date("Ymd", time()) . ".txt";
                $params['merchant_id'] = $merchant_config['merchant_id'];
//                $params['partnerId'] = $merchant_config['partner_id'];
//                $params['partnerPassword'] = $merchant_config['merchant_password'];
                $inputs = array(
                    'pg_user_code' => VCB_ECOM_PG_USER_CODE,
                    'channel_name' => VCB_ECOM_CHANNEL_NAME,
                    'fnc' => $function,
                    'data' => json_encode($params),
                );
                $inputs['checksum'] = md5($inputs['data'] . VCB_ECOM_AUTHEN_KEY);
                self::_writeLog('[INPUT] ' . json_encode($inputs));
                Logs::writeELKLog($inputs, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/vcb');

                $result = self::_call($inputs, $merchant_config);

                self::_writeLog('[OUTPUT] ' . json_encode($result));
                Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/vcb');
                if ($result != false) {
                    if ($result['status'] == true) {

                        return array(
                            'status' => $result['status'],
                            'error_code' => $result['error_code'],
                            'message' => $result['message'],
                            'data' => json_decode($result['data'], true),
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
                        'message' => 'Có lỗi trong quá trình xử lý với Ngân hàng phát hành',
                        'data' => '',
                    );
                }


            }

        }

    }

    private static function callQR($function, $params, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        if (!$merchant_config) {

            return [
                'error_code' => '105',
                'message' => 'Lỗi kết nối',
            ];
        } else {
            if (empty($merchant_config['merchant_id'])) {

                return [
                    'error_code' => '105',
                    'message' => 'Lỗi kết nối',

                ];
            } else {
//                $file_name = 'data/logs/vcb_ecom/' . date("Ymd", time()) . ".txt";
                $params['merchant_code'] = $merchant_config['partner_id'];
                $params['terminal_id'] = $merchant_config['terminal_id'];
//                $params['partnerId'] = $merchant_config['partner_id'];
//                $params['partnerPassword'] = $merchant_config['merchant_password'];

                $inputs = array(
                    'pg_user_code' => VCB_QR_GATEWAY_PG_USER_CODE,
                    'channel_name' => VCB_QR_GATEWAY_CHANNEL_NAME,
                    'fnc' => $function,
                    'data' => json_encode($params),
                );
                $inputs['checksum'] = md5($inputs['data'] . VCB_QR_GATEWAY_AUTHEN_KEY);
                self::_writeLog('[INPUT] ' . json_encode($inputs));
                Logs::writeELKLog($inputs, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/vcb');

                $result = self::_callQR($inputs, $merchant_config);

                self::_writeLog('[OUTPUT] ' . json_encode($result));
                Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/vcb');
                if ($result != false) {
                    if ($result['status'] == true) {

                        return array(
                            'status' => $result['status'],
                            'error_code' => $result['error_code'],
                            'message' => $result['message'],
                            'data' => json_decode($result['data'], true),
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
                        'message' => 'Có lỗi trong quá trình xử lý với Ngân hàng phát hành',
                        'data' => '',
                    );
                }


            }

        }

    }

    private static function _call($params, $config)
    {

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, VCB_ECOM_ENDPOINT);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, trim(VCB_ECOM_HTTP_USER . ":" . trim(VCB_ECOM_HTTP_PASSWORD)));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            self::_writeLog('[URL][CURL_ERROR] ' . VCB_ECOM_ENDPOINT . json_encode($result));


//            if(isset($_GET['debug']) && $_GET['debug'] =='duclm'){
//
//            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                return json_decode($result, true);
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    private static function _callQR($params, $config)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, VCB_QR_GATEWAY_ENDPOINT);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, trim(VCB_ECOM_HTTP_USER . ":" . trim(VCB_ECOM_HTTP_PASSWORD)));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            self::_writeLog('[URL][CURL_ERROR] ' . VCB_QR_GATEWAY_ENDPOINT . json_encode($result));


//            if(isset($_GET['debug']) && $_GET['debug'] =='duclm'){
//
//            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                return json_decode($result, true);
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }


    //getMessage không dùng nữa

    public static function getVerifyMessage($code, $lang = 'vn')
    {
        $lang = ($lang == 'en') ? 'en' : 'vn';

        $errors = [
            'vn' => [
                'NL' => 'Lỗi hệ thống',
                '1' => 'Thành công',
                '2' => 'Thiếu tham số',
                '3' => 'Mã giao dịch không chính xác',
                '5' => 'Số tiền hoàn lại lớn hơn số tiền thanh toán',
                '6' => 'Đơn vị tiền tệ hoàn lại khác đơn vị thanh toán',
                '7' => 'Giao dịch trước đó thất bại hoặc đã được hoàn lại',
                '8' => 'Lỗi hoàn lại',
                '9' => 'Lỗi hệ thống',
                '11' => 'Sai thông tin đối tác',
                '12' => 'Số tiền không hợp lệ',
                '13' => 'Số tiền giao dịch không nằm trong khoảng quy định',
                '14' => 'Lỗi hệ thống',
                '15' => 'Không tìm thấy giao dịch',
                '16' => 'Giao dịch thất bại',
                '17' => 'Số dư tài  khoản không đủ',
                '18' => 'Trạng thái tài khoản không hợp lệ',
                '19' => 'Không xác định được số tài khoản',
                '20' => 'Giao dịch thất bại',
                '21' => 'Giao dịch thất bại',
                '22' => 'Không xác định được chi nhánh ứng với TK',
                '24' => 'Ngày giao dịch không xác định',
                '40' => 'Giao dịch hợp lệ',
                '50' => 'Hệ thống đang bảo trì dịch vụ',
                '61' => 'Thẻ đang bị khóa hoặc trạng thái thẻ không hợp lệ',
                '62' => 'Thông tin thẻ không đúng',
                '63' => 'Chỉ chấp nhận thẻ Vietcombank Connect24',
                '64' => 'Tài  khoản chưa đăng ký Internet Banking',
                '65' => 'Sai mã khách hàng',
                '66' => 'Số tiền thanh  toán không hợp lệ',
                '67' => 'Đơn vị tiền tệ không chính xác',
                '68' => 'Lỗi giải mã',
                '69' => 'Lỗi xác thực chữ ký',
                '70' => 'Chữ ký không chính xác',
                '98' => 'Lỗi xác thực chữ ký',
                '99' => 'Lỗi không xác định',
            ],
            'en' => [
                'NL' => 'System error',
                '1' => 'Successful',
                '2' => 'Missing parameters',
                '3' => 'Wrong transaction id',
                '5' => 'Refund amount was greater than payment amount',
                '6' => 'Refund currency was not the same as payment currency',
                '7' => 'Previous transaction was failed or refunded',
                '8' => 'Refund error',
                '11' => 'Wrong partner information',
                '12' => 'Invalid amount',
                '13' => 'Exceed quota limit',
                '14' => 'System error',
                '15' => 'Unknown transaction',
                '16' => 'Transaction failed',
                '17' => 'Invalid ID or time out',
                '18' => 'Account status is not valid',
                '19' => 'Account number unknown',
                '20' => 'Transaction failed',
                '21' => 'Transaction failed',
                '22' => 'Could not specify bank branch',
                '23' => 'OTP was incorrect',
                '24' => 'Transaction date unknown',
                '40' => 'Valid transaction',
                '50' => 'System is under maintenance',
                '61' => 'Your card is currently blocked or is in invalid status',
                '62' => 'Card information was not correct',
                '63' => 'Only accept Vietcombank Connect 24 card',
                '64' => 'Your account is not registered for Internet Banking',
                '65' => 'Wrong merchant id',
                '66' => 'Invalid amount',
                '67' => 'Wrong currency',
                '68' => 'Decryption error',
                '69' => 'Verify Signature error',
                '70' => 'Wrong signature',
                '98' => 'Verify signature error',
                '99' => 'Unknown Error',
            ],
        ];

        if (array_key_exists($code, $errors[$lang])) {
            return $errors[$lang][$code];
        } else {
            if ($lang == 'en') return 'Transaction failed. Please contact the bank for more information.';

            return 'Giao dịch không thành công. Vui lòng liên hệ ngân hàng để biết thêm chi tiết.';
        }
    }

    public static function getNotifyMessage($code, $lang = 'vn')
    {
        $lang = ($lang == 'en') ? 'en' : 'vn';

        $errors = [
            'vn' => [
                'NL' => 'Lỗi hệ thống',
                '1' => 'Thành công',
                '12' => 'Dữ liệu không hợp lệ',
                '13' => 'Số tiền giao dịch không nằm trong khoảng quy định',
                '14' => 'Không có email liên lạc',
                '15' => 'User không tồn tại',
                '16' => 'Merchant không tồn tại',
                '17' => 'ID không hợp  lệ hoặc thời gian truy cập đã hết',
                '18' => 'Thời gian thực hiện giao dịch đã hết',
                '19' => 'Không xác định được số tài khoản',
                '20' => 'Không xác định được chi nhánh ứng với số TK',
                '21' => 'Không kiểm tra được số dư  hoặc số dư không đủ',
                '22' => 'Lỗi hệ thống', //Không gọi được WS bên One Pay
                '23' => 'Lỗi xác thực OTP',
                '24' => 'Lỗi hệ thống', //Call partner service false
                '25' => 'Giao dịch được được thanh  toán trước đó',
                '26' => 'Không xác định được số điện thoại mặc định nhận OTP',
                '27' => ' Loại  ngoại tệ ứng với tài khoản không được chấp nhận thanh toán',
                '28' => 'Không có quyền truy cập  hoặc thời gian truy cập đã hết',
                '77' => 'Giao dịch không thành công. Vui lòng liên hệ ngân hàng để biết thêm chi tiết.',
                '99' => 'Lỗi không xác định',
            ],
            'en' => [
                'NL' => 'System error',
                '12' => 'Invalid amount',
                '13' => 'Exceed quota limit',
                '14' => 'System error',
                '15' => 'Unknown transaction',
                '16' => 'Transaction failed',
                '17' => 'Invalid ID or time out',
                '18' => 'Account status is not valid',
                '19' => 'Account number unknown',
                '20' => 'Transaction failed',
                '21' => 'Transaction failed',
                '22' => 'Could not specify bank branch',
                '23' => 'OTP was incorrect',
                '24' => 'Transaction date unknown',
                '25' => 'Transaction paid before',
                '26' => 'Could not determine OTP-receive phone number',
                '27' => 'Currency was not allowed',
                '28' => 'Transaction has not been completed',
                '77' => 'Transaction failed. Please contact the bank for more information.',
                '99' => 'Unknown Error',
            ]
        ];

        if (array_key_exists($code, $errors[$lang])) {
            return $errors[$lang][$code];
        } else {
            if ($lang == 'en') return 'System error.';

            return 'Có lỗi trong quá trình xử lý với Ngân hàng phát hành';
        }
    }

    private static function _writeLog($data)
    {
        $file_name = 'vcb' . DS . date("Ymd", time()) . ".txt";
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