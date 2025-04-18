<?php

namespace common\payments;

use Yii;
use common\components\utils\Logs;
use common\components\libs\Tables;

class NganLuongSeamless
{

    const VERSION = '3.2';

    public static $url_api = '';
    public static $merchant_id = '';
    public static $merchant_password = '';
    public static $receiver_email = '';

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    private static function _setMerchantConfig($partner_payment_id, $merchant_id)
    {
        self::$url_api = NGANLUONG_URL . 'checkoutseamless.api.nganluong.post.php';
        $partner_payment = Tables::selectOneDataTable('partner_payment_account', ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status", "merchant_id" => $merchant_id, "partner_payment_id" => $partner_payment_id, "status" => self::STATUS_ACTIVE]);
        if (!empty($partner_payment)) {
            return [
                'merchant_id' => $partner_payment['partner_merchant_id'],
                'merchant_password' => $partner_payment['partner_merchant_password'],
                'receiver_email' => $partner_payment['partner_payment_account'],
                'merchant_id_vcb' => $partner_payment['checksum_key'],
                'terminal_id_vcb' => $partner_payment['token_key'],
                'mid_ib' => @$partner_payment['mid_ib'],
                'partner_id_vcb' => @$partner_payment['partner_id_vcb'],
                'merchant_name_vcb' => @$partner_payment['merchant_name_vcb'],
            ];
        }
        return false;
    }

    /**
     *
     * @param type $params : receiver_email, payment_method, bank_code
     */
    public static function getRequestField($params, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        $inpus = array(
            'function' => 'GetRequestField',
            'version' => self::VERSION,
            'merchant_id' => ($merchant_config['merchant_id']),
            'merchant_password' => MD5($merchant_config['merchant_password']),
            'receiver_email' => $merchant_config['receiver_email'],
            'payment_method' => $params['payment_method'], //Phương thức thanh toán, nhận một trong các giá trị 'VISA','ATM_ONLINE', 'ATM_OFFLINE' hoặc 'NH_OFFLINE'												
            'bank_code' => $params['bank_code'], //Phương thức thanh toán, nhận một trong các giá trị 'VISA','ATM_ONLINE', 'ATM_OFFLINE' hoặc 'NH_OFFLINE'												
        );
        return self::_call($inpus);
    }

    /**
     *
     * @param type $token
     * @return boolean
     */
    public static function getTransactionDetail($token, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        $params = array(
            'merchant_id' => $merchant_config['merchant_id'],
            'merchant_password' => MD5($merchant_config['merchant_password']),
            'version' => self::VERSION,
            'function' => 'GetTransactionDetail',
            'token' => $token
        );
        return self::_call($params);
    }

    /**
     *
     * @param type $response
     * @param type $payment_transaction_info
     * @param type $error_message
     * @return boolean
     */
    public static function verifyResponse($response, $payment_transaction_info, &$error_message = '')
    {

        if ($response['error_code'] == '00') {
            if ($response['transaction_status'] == '00') {
                if ($response['receiver_email'] == self::$receiver_email && $response['order_code'] == $GLOBALS['PREFIX'] . $payment_transaction_info['id']) {
                    if ($response['total_amount'] == \common\models\db\Transaction::getPartnerPaymentAmount($payment_transaction_info)) {
                        return true;
                    } else {
                        $error_message = 'Xác thực thanh toán không thành công';
                    }
                } else {
                    $error_message = 'Xác thực thanh toán không thành công';
                }
            } else {
                $error_message = 'Giao dịch chưa được thanh toán';
            }
        } else {
            $error_message = self::getErrorMessage($response['error_code']);
        }
        return false;
    }

    /**
     *
     * @param type $params : receiver_email, cur_code, order_code, total_amount, payment_method, bank_code, order_description, tax_amount, fee_shipping, discount_amount, return_url, cancel_url, notify_url, buyer_fullname, buyer_email, buyer_mobile, buyer_address, card_fullname, card_number, card_month, card_year, total_item
     */
    public static function checkout($params, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        $inpus = array(
            'cur_code' => $params['cur_code'],
            'function' => 'SetExpressCheckout',
            'version' => self::VERSION,
            'merchant_id' => $merchant_config['merchant_id'], //Mã merchant khai báo tại NganLuong.vn
            'receiver_email' => $merchant_config['receiver_email'],
            'merchant_password' => MD5($merchant_config['merchant_password']), //MD5(Mật khẩu kết nối giữa merchant và NganLuong.vn)
            'order_code' => $params['order_code'], //Mã hóa đơn do website bán hàng sinh ra
            'total_amount' => $params['total_amount'], //Tổng số tiền của hóa đơn
            'payment_method' => $params['payment_method'], //Phương thức thanh toán, nhận một trong các giá trị 'VISA','ATM_ONLINE', 'ATM_OFFLINE' hoặc 'NH_OFFLINE'												
            'bank_code' => $params['bank_code'], //Phương thức thanh toán, nhận một trong các giá trị 'VISA','ATM_ONLINE', 'ATM_OFFLINE' hoặc 'NH_OFFLINE'												
            'payment_type' => 1, //Kiểu giao dịch: 1 - Ngay; 2 - Tạm giữ; Nếu không truyền hoặc bằng rỗng thì lấy theo chính sách của NganLuong.vn
            'order_description' => $params['order_description'], //Mô tả đơn hàng
            'tax_amount' => $params['tax_amount'], //Tổng số tiền thuế
            'fee_shipping' => $params['fee_shipping'], //Phí vận chuyển
            'discount_amount' => $params['discount_amount'], //Số tiền giảm giá
            'return_url' => $params['return_url'], //Địa chỉ website nhận thông báo giao dịch thành công
            'cancel_url' => $params['cancel_url'], //Địa chỉ website nhận "Hủy giao dịch"
            'notify_url' => $params['notify_url'], //Địa chỉ website nhận "Hủy giao dịch"
            'card_fullname' => $params['card_fullname'], //Tên chủ thẻ/ Tên chủ tài khoản
            'card_number' => $params['card_number'], //Số thẻ/ Số tài khoản
            'card_month' => $params['card_month'], //Tháng phát hành thẻ
            'card_year' => $params['card_year'], //Năm phát hành thẻ
            'buyer_fullname' => $params['buyer_fullname'], //Tên người mua hàng
            'buyer_email' => $params['buyer_email'], //Địa chỉ Email người mua
//            'buyer_mobile' => $params['buyer_mobile'], //Điện thoại người mua
            'buyer_address' => $params['buyer_address'], //Địa chỉ người mua hàng
            'total_item' => $params['total_item'],
            'time_limit' => (7 * 24),

        );

        if ($params['bank_code'] == 'VIB') {
            $inpus['card_year'] = '20' . $inpus['card_year'];
        }

        if ($params['payment_method'] == 'IB_ONLINE') {
            $inpus['merchant_id_vcb'] = $merchant_config['mid_ib'];
            $inpus['partner_id_vcb'] = $merchant_config['partner_id_vcb'];
            $inpus['merchant_type_vcb'] = 'BA_BEN';

        } else {
            $inpus['merchant_id_vcb'] = $merchant_config['merchant_id_vcb'];
            $inpus['terminal_id_vcb'] = $merchant_config['terminal_id_vcb'];
            $inpus['merchant_name_vcb'] = $merchant_config['merchant_name_vcb'];
            $inpus['merchant_type_vcb'] = 'BA_BEN';
        }

        if(isset($params['mobile']) && isset($params['buyer_mobile'])){

            if ($params['mobile'] != $params['buyer_mobile']){
                $input['buyer_mobile'] =  $params['mobile'];
            }else{
                $input['buyer_mobile'] =  $params['buyer_mobile'];
            }
        }elseif (isset($params['mobile']) && !isset($params['buyer_mobile'])){
            $input['mobile'] = $params['mobile'];
            $input['buyer_mobile'] =  $params['mobile'];
        }else{
            $input['buyer_mobile'] =  $params['buyer_mobile'];
        }
        if (isset($params['identity_number'])) {
            $inpus['identification_number'] = $params['identity_number'];
        }
        if (isset($params['mobile'])) {
            $inpus['mobile'] = $params['mobile'];
        }

        /* $result = '<?xml version="1.0" encoding="UTF-8" ?><result><error_code>00</error_code><token>55882219-0d927241d8b60b42451cf1c76ec6568' . rand(1, 100000) . '</token><description></description><time_limit></time_limit><auth_site>QRCODE</auth_site><auth_url></auth_url><qr_data>iVBORw0KGgoAAAANSUhEUgAAAUUAAAFFAQMAAABBum0eAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAEVklEQVRoge1aQY6jMBBsxIFjnuCfwMeQQMrHyE94AkcOiN6qaicku3vbPUxL8YFh7CKS3e1yd7XNvu3bvu0/tJu7n7eDb0fx0yb3BW/7sE2OR4thn3Mh8ac3C9BuNto++FHYZ91ja9eAZELefe9tig7OeO0w2eIP99Uw9xUrkxBJk2LIbcCjgzVtb7Y695xIWBOvk+NBh4RrWk8nbVIiwz8xWWysHR/ShvXD7rQ/PfnnI8Uh9xUm/evjd7b5+chn684bXZOTHRygYRsL/NP+bD8eWT2QCxA7jpOlNVtYE3sPG3BOhbzNha6Jo4oz1gYEfZSdO84Xwp8LkAZ5p3/OhexuGBqLNTAkOER9zm2XC4kGigdzcLKw5gG7giQVWGAMTvpkxURIWA7v2GKYO60pC/cwpPbeO4fkQE6rbOgPIGG+u2uLMSqkf6ovG5JUQRp0cgiO4Y7GVcQhUDokOhigy5qxAKQP+uxRIoZ6O4tTIGEvRBcg+4dXsicNFv5EnMXpkBoCc4ALp5X7jHYlPwIJNtF6WCok95RmzBMY38yxzxQQMgKkzyZDtqJBDt1F7BY5Lrad62hGDJUMqUQXRlMS38fQFJQSAWGz5UIyungwfj1j2gyVaGH2CSB3TYWcizhETrorpxoEAl0qN9yvnCsHEk1bzBp3wqsO0SkW1FIMV/KRAin1Cw/xReccUsrbkxV5fNnLmkmQZpU+BspEMF/lR8VQnzyfBMloD42kAWuSC6nxxdzxE+4R8WZCKiNUbyXEoEZKYJ0ijuvUToI8mNm6JAiTQinBMsIOlzZxeXIKpLWRtSsCDA4hNQb3x9yzIXEWK3+SQi7hi/tMbyHLvtSYJEgOLSayN0XpoHj6LN2VPxFOmg3pYUjm6swNF64HKV5FjYTIVUPSKj0Ey1iAmuh6NqR0Icr9lCkRYhxFtC/Gp4jEBbBUSOqrzA0Z7akQaLup8MSHTrOXIpEGGZPda82Jea9Xzd+r2pIMea95YCNrMl5adJD5IZmyfGZSCZDB7lIkVFXSWWwSlUPoe809CzI4hHAyINPboBSVn5V3XKuUA0n11WHDnaqRRGWtB/mx+uwrN0yDJGg0dahiwVOZ2YY+nNargJYF2cY9gV3l50Vy/02ljHaN4vTLk5Mgq75ai2XkR2fywWyDlab3amAW5HzFtOuzvqlv5hK1274aMwuSvrhQITLRhzQWgqUxk1cu/kyCJCtKePDteelGJQC2qKed2ZCa9qgadJzKnW9j3JOK33nl70mQbKpdNFG7YBrC+zYhYkbkPqdDWg2LqMNWwaiW0vi1v1/oSIDU7KL8Mpb9Kjf1+jruqSRDzqxdKNuo4WxckTLdgfjIIrMga7ahQ6um89pxqtBI+M+IrNeIwj+f1wp1J+rzvk0eJGyoUF1Kq59R31RuyPs2tzkXkv5pvMca9wSmuBOFf1v2TVd9Mw0yOGRVGb3ziM2FDBFzfT+LUyC/7du+7R/bL3wuSpOJghUrAAAAAElFTkSuQmCC</qr_data></result>';*/
        //  $result = str_replace('&', '&amp;', trim($result));
        // $result = json_decode(json_encode(simplexml_load_string($result)), true);
        //return $result;
        return self::_call($inpus);
    }

    /**
     *
     * @param type $params : receiver_email, token, otp, auth_url
     * @return type
     */
    public static function authenTransaction($params, $merchant_id, $partner_payment_id)
    {
        $merchant_config = self::_setMerchantConfig($partner_payment_id, $merchant_id);
        $inpus = array(
            'function' => 'AuthenTransaction',
            'version' => self::VERSION,
            'merchant_id' => $merchant_config['merchant_id'], //Mã merchant khai báo tại NganLuong.vn
            'receiver_email' => $params['receiver_email'],
            'merchant_password' => MD5($merchant_config['merchant_password']), //MD5(Mật khẩu kết nối giữa merchant và NganLuong.vn)
            'token' => $params['token'], //Mã token
            'otp' => $params['otp'], //otp
            'auth_url' => $params['auth_url'], //Url xac thuc
        );
        return self::_call($inpus);
    }

    public static function getPaymentMethodAndBankCode($payment_method_code)
    {
        $payment_method = '';
        $bank_code = '';
        if (substr($payment_method_code, -8) == 'ATM-CARD') {
            $payment_method = 'ATM_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 9);
            if ($bank_code == 'NCB') {
                $bank_code = 'NAB';
            }
        } elseif (substr($payment_method_code, -9) == 'IB-ONLINE') {
            $payment_method = 'IB_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 10);
            if ($bank_code == 'GDB') {
                $bank_code = 'VCCB';
            }
        } elseif (substr($payment_method_code, -7) == 'QR-CODE') {
            $payment_method = 'QRCODE';
            $bank_code = 'VCB';
        }
        return array('payment_method' => $payment_method, 'bank_code' => $bank_code);
    }

    /**
     *
     * @param type $params
     * @return boolean
     */
    private static function _call($params)
    {
        $func = @$params['function'];
        $query_string = http_build_query($params);
        self::_writeLog('[INPUT][' . $func . ']:' . $query_string);
        Logs::writeELKLog($params, 'nl-vietcombank-checkout', 'INPUT', $func, '', 'checkout/nganluong_seamless');

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$url_api);
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
            self::_writeLog('[RESULT][' . $func . ']:' . $result);
            Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $func, '', 'checkout/nganluong_seamless');
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($result != '' && $status == 200) {
                $result = str_replace('&', '&amp;', trim($result));
                $result = json_decode(json_encode(simplexml_load_string($result)), true);

                return $result;
            }
        } catch (Exception $ex) {
            Logs::writeELKLog($ex->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $func, '', 'checkout/nganluong_seamless');

            return false;
        }
        return false;
    }

    public static function getErrorMessage($error_code)
    {
        $arrCode = array(
            '00' => 'Thành công',
            '99' => 'Lỗi chưa xác minh',
            '98' => 'Lỗi khi thực hiện giao dịch trên ngân hàng',
            '06' => 'Mã merchant không tồn tại hoặc bị khóa',
            '02' => 'Địa chỉ IP truy cập bị từ chối',
            '03' => 'Mã checksum không chính xác, truy cập bị từ chối',
            '04' => 'Tên hàm API do merchant gọi tới không hợp lệ (không tồn tại)',
            '05' => 'Sai version của API',
            '07' => 'Sai mật khẩu của merchant',
            '08' => 'Địa chỉ email tài khoản nhận tiền không tồn tại',
            '09' => 'Tài khoản nhận tiền đang bị phong tỏa giao dịch',
            '10' => 'Mã đơn hàng không hợp lệ',
            '11' => 'Số tiền giao dịch lớn hơn hoặc nhỏ hơn quy định',
            '12' => 'Loại tiền tệ không hợp lệ',
            '29' => 'Token không tồn tại',
            '80' => 'Không thêm được đơn hàng',
            '81' => 'Đơn hàng chưa được thanh toán',
            '110' => 'Địa chỉ email tài khoản nhận tiền không phải email chính',
            '111' => 'Tài khoản nhận tiền đang bị khóa',
            '113' => 'Tài khoản nhận tiền chưa cấu hình là người bán nội dung số',
            '114' => 'Giao dịch đang thực hiện, chưa kết thúc',
            '115' => 'Giao dịch bị hủy',
            '118' => 'tax_amount không hợp lệ',
            '119' => 'discount_amount không hợp lệ',
            '120' => 'fee_shipping không hợp lệ',
            '121' => 'return_url không hợp lệ',
            '122' => 'cancel_url không hợp lệ',
            '123' => 'items không hợp lệ',
            '124' => 'transaction_info không hợp lệ',
            '125' => 'quantity không hợp lệ',
            '126' => 'order_description không hợp lệ',
            '127' => 'affiliate_code không hợp lệ',
            '128' => 'time_limit không hợp lệ',
            '129' => 'buyer_fullname không hợp lệ',
            '130' => 'buyer_email không hợp lệ',
            '131' => 'buyer_mobile không hợp lệ',
            '132' => 'buyer_address không hợp lệ',
            '133' => 'total_item không hợp lệ',
            '134' => 'payment_method, bank_code không hợp lệ',
            '135' => 'Lỗi kết nối tới hệ thống ngân hàng',
            '140' => 'Đơn hàng không hỗ trợ thanh toán trả góp',
            '141' => 'Thông tin token không chính xác',
            '142' => 'Thông tin Authen Url không đúng',
            '32' => 'Không hỗ trợ thanh toán nội dung số',
        );
        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }

    private static function _writeLog($data)
    {
        $file_name = 'nganluong_seamless' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        if (strpos($data, '[INPUT][SetExpressCheckout]:') !== false && strpos($data, 'card_number') !== false) {

            parse_str(str_replace('[INPUT][SetExpressCheckout]:', '', $data), $data_input);

            $data_input['card_mask_number'] = self::_replaceCardNumber($data_input['card_number']);
            unset($data_input['card_number']);

            Logs::create($pathinfo['dirname'], $pathinfo['basename'], '[INPUT]' . http_build_query($data_input));
        } else {
            Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
        }
    }

    private static function _replaceCardNumber($cardNumber)
    {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }

}
