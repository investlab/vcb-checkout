<?php

namespace common\payments;

use common\models\db\Merchant;
use Yii;
use common\components\utils\Logs;
use common\components\libs\Tables;


class NganLuong {

    const VERSION = '3.1';
    public static $url_api = '';
    public static $merchant_id = '';
    public static $merchant_password = '';
    public static $receiver_email = '';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    private static function _setMerchantConfig($partner_payment_id,$merchant_id) {
        self::$url_api = NGANLUONG_URL.'checkout.api.nganluong.post.php';
        $partner_payment = Tables::selectOneDataTable('partner_payment_account', ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status", "merchant_id" => $merchant_id,"partner_payment_id" => $partner_payment_id, "status" => self::STATUS_ACTIVE]);
        if (!empty($partner_payment)) {
            return [
                'merchant_id' => $partner_payment['partner_merchant_id'],
                'merchant_password' => $partner_payment['partner_merchant_password'],
            ];
        }
        return false;
    }

    /**
     *
     * @param type $token
     * @return boolean
     */
    public static function getTransactionDetail($token,$merchant_id,$partner_payment_id) {
        $merchant_config = self::_setMerchantConfig($partner_payment_id,$merchant_id);
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
    public static function verifyResponse($response, $payment_transaction_info, &$error_message = '') {

        if ($response['error_code'] == '00') {
            if ($response['transaction_status'] == '00') {
                if ($response['receiver_email'] == self::$receiver_email && $response['order_code'] ==  $GLOBALS['PREFIX'].$payment_transaction_info['id']) {
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
     * @param type $params : receiver_email, cur_code, order_code, total_amount, payment_method, bank_code, order_description, tax_amount, fee_shipping, discount_amount, return_url, cancel_url, buyer_fullname, buyer_email, buyer_mobile, buyer_address, total_item, lang_code
     */
    public static function checkout($params,$merchant_id,$partner_payment_id) {
        $exeption = Merchant::find()->select(['exception'])->where(['id'=>$merchant_id])->one()['exception'];
        $merchant_config = self::_setMerchantConfig($partner_payment_id,$merchant_id);
        $mystring = $params['cancel_url'];
        $findme   = 'cancel';
        $pos = strpos($mystring, $findme);
        if ($pos !== false && $exeption=='no_return') {
            $cancelURL = str_replace('cancel', 'transaction-destroy', $params['cancel_url']);
            //$cancelURL = $params['cancel_url'];
        } else {
            $cancelURL =  $params['cancel_url'];
        }
        $inpus = array(
            'cur_code' => $params['cur_code'],
            'function' => 'SetExpressCheckout',
            'version' => self::VERSION,
            'merchant_id' => $merchant_config['merchant_id'], //Mã merchant khai báo tại NganLuong.vn
            'receiver_email' => $params['receiver_email'],
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
            'cancel_url' => $cancelURL, //Địa chỉ website nhận "Hủy giao dịch"
            'buyer_fullname' => $params['buyer_fullname'], //Tên người mua hàng
            'buyer_email' => $params['buyer_email'], //Địa chỉ Email người mua
            'buyer_mobile' => $params['buyer_mobile'], //Điện thoại người mua
            'buyer_address' => $params['buyer_address'], //Địa chỉ người mua hàng
            'total_item' => $params['total_item'],
            'lang_code' => $params['lang_code'],
        );
        return self::_call($inpus);
    }

    public static function getPaymentMethodAndBankCode($payment_method_code) {
        $payment_method = '';
        $bank_code = '';
        if (substr($payment_method_code, -8) == 'ATM-CARD') {
            $payment_method = 'ATM_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 9);
            if ($bank_code == 'STB') {
                $bank_code = 'SCB';
            } elseif ($bank_code == 'NCB') {
                $bank_code = 'NAB';
            }
        } elseif (substr($payment_method_code, -9) == 'IB-ONLINE') {
            $payment_method = 'IB_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 10);
        } elseif (substr($payment_method_code, -6) == 'WALLET') {
            $payment_method = 'NL';
        } elseif (substr($payment_method_code, -11) == 'CREDIT-CARD') {
            $payment_method = 'VISA';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 12);
        }
        return array('payment_method' => $payment_method, 'bank_code' => $bank_code);
    }

    /**
     *
     * @param type $params
     * @return boolean
     */
    private static function _call($params) {
        $query_string = http_build_query($params);
//        echo "<pre>";
//        print_r($params);exit();
        self::_writeLog('INPUT:' . $query_string);
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
            self::_writeLog('RESULT:' . $result);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($result != '' && $status == 200) {
                $result = json_decode(json_encode(simplexml_load_string($result)), true);
                return $result;
            }
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    public static function getErrorMessage($error_code) {
        $arrCode = array(
            '00' => 'Thành công',
            '99' => 'Lỗi chưa xác minh',
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
            '140' => 'Đơn hàng không hỗ trợ thanh toán trả góp',);

        return isset($arrCode[$error_code]) ? $arrCode[$error_code] : 'Lỗi không xác định (' . $error_code . ')';
    }



    private static function _writeLog($data) {


        $file_name = 'nganluong' .DS. date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
