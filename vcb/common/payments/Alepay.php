<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 10/24/2019
 * Time: 4:23 PM
 */

namespace common\payments;
use common\components\libs\Tables;
use Yii;
use common\components\utils\Logs;
use common\models\db\Transaction;


class Alepay
{
    public static $token_key = '';
    public static $url_api = '';
    public static $checksum_key = '';
    public static $receiver_email = '';
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;


    private static function _setMerchantConfig($partner_payment_id,$merchant_id) {
        self::$url_api = ALEPAY_URL;
        $partner_payment = Tables::selectOneDataTable('partner_payment_account', ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status", "merchant_id" => $merchant_id,"partner_payment_id" => $partner_payment_id, "status" => self::STATUS_ACTIVE]);
        if (!empty($partner_payment)) {
            return [
                'token_key' => $partner_payment['token_key'],
                'checksum_key' => $partner_payment['checksum_key'],
            ];
        }
        return false;
    }

    /**
     *
     * @param type $token
     * @return boolean
     */
    public static function getTransactionDetail($params,$merchant_id,$partner_payment_id) {
        $merchant_config = self::_setMerchantConfig($partner_payment_id,$merchant_id);
        if (!empty($merchant_config)) {
            if (empty($merchant_config['token_key']) || empty($merchant_config['checksum_key'])) {
                return false;
            }
        } else {
            return false;
        }
        $params = array(
            'tokenKey' => $merchant_config['token_key'],
            'transactionCode' => $params['transactionCode'],
            'checksum' => md5($params['transactionCode'] . $merchant_config['checksum_key'])
        );
        $path = '/checkout/v2/get-transaction-info';

        return self::_call($params,$path, 'getTransactionDetail');
    }


    public static function verifyResponse($response, $payment_transaction_info, &$error_message = '') {
        self::_writeLog('RESPONSE:' . json_encode($response));
        Logs::writeELKLog($response, 'nl-vietcombank-checkout', 'RESPONSE', 'verifyResponse', '', 'checkout/alepay');

        if ($response['errorCode'] == '000') {
            if ($response['data']['status'] == '155' || $response['data']['status'] == '150') {
                if ( $response['data']['orderCode'] ==  $GLOBALS['PREFIX'].$payment_transaction_info['id']) {
                    if ($response['data']['amount'] == Transaction::getPartnerPaymentAmount($payment_transaction_info)) {
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


    public static function checkout($params,$merchant_id,$partner_payment_id) {
        $merchant_config = self::_setMerchantConfig($partner_payment_id,$merchant_id);
        if (!$merchant_config){
            return [
                'errorCode' => '105'
            ];
        } else {
            if (empty($merchant_config['token_key'])) {
                return [
                    'errorCode' => '105'
                ];
            } else if (empty($merchant_config['checksum_key'])) {
                return [
                    'errorCode' => '101'
                ];
            }
        }
        $checksum = md5($params['orderCode'] . $params['amount'] . $params['checkoutType'] . $params['currency'] . $params['returnUrl'] . $merchant_config['checksum_key'] );
        $inpus = array(
            'orderCode' => $params['orderCode'],
            'amount' => $params['amount'],
            'currency' =>  $params['currency'],
            'orderDescription' => $params['orderDescription'],
            'totalItem' => $params['totalItem'],
            'checkoutType' => $params['checkoutType'],
            'installment' => $params['installment'],
            'month' => $params['month'],
            'bankCode' => $params['bankCode'],
            'paymentMethod' => $params['paymentMethod'],
            'returnUrl' => $params['returnUrl'],
            'cancelUrl' => $params['cancelUrl'],
            'buyerName' => $params['buyerName'],
            'buyerEmail' => $params['buyerEmail'],
            'buyerPhone' => $params['buyerPhone'],
            'buyerAddress' => $params['buyerAddress'],
            'buyerCity' => $params['buyerCity'],
            'buyerCountry' => $params['buyerCountry'],
            'paymentHours' => $params['paymentHours'],
            'promotionCode' => $params['promotionCode'],
            'allowDomestic' => $params['allowDomestic'],
            'language' => $params['language'],
            'tokenKey' => $merchant_config['token_key'],
            'checksum' => $checksum,
        );
        $path = '/checkout/v2/request-order';
        return self::_call($inpus, $path, 'checkout');
    }

    public static function getPaymentMethodAndBankCode($payment_method_code) {
        if(strpos($payment_method_code,'TRA-GOP')){
            $payment_method = 'VISA';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 8);
            switch ($bank_code) {
                case 'STB':
                    $bank_code_method = 'SACOMBANK';
                    break;
                case 'TCB':
                    $bank_code_method = 'TECHCOMBANK';
                    break;
                case 'BIDV':
                    $bank_code_method = 'BIDV';
                    break;
                case 'HSBC':
                    $bank_code_method = 'HSBC';
                    break;
                case 'SC':
                    $bank_code_method = 'SC';
                    break;
                case 'FE':
                    $bank_code_method = 'FE';
                    break;
                case 'CTB':
                    $bank_code_method = 'CTB';
                      break;

                default:
                    $bank_code_method = 'SACOMBANK';

            }
            return array('payment_method' => $payment_method, 'bank_code' => $bank_code_method);
        }else{
            return false;
        }



    }
    public static function getInstallmentInfo($params,$merchant_id,$partner_payment_id){
        $merchant_config = self::_setMerchantConfig($partner_payment_id,$merchant_id);
        if (!empty($merchant_config)) {
            if (empty($merchant_config['token_key']) || empty($merchant_config['checksum_key'])) {
                return false;
            }
        } else {
            return false;
        }
        $amount = number_format($params['amount'], 1, '.', '');
        $inputs = array(
            'amount' => $amount,
            'currencyCode' => $params['currency'],
            'tokenKey' => $merchant_config['token_key'],
            'checksum' => md5($amount . $params['currency'] . $merchant_config['checksum_key']),
        );
        $result = self::_call($inputs,'/checkout/v2/get-installment-info', 'getInstallmentInfo');
        if ($result['errorCode'] == '000'){
            return $result['data'];
        }else{
            return array(
                'message' => 'Không tìm thấy dữ liệu trả góp'
            );
        }


    }



    /**
     *
     * @param type $params
     * @return boolean
     */
    private static function _call($params,$path, $function) {
        self::_writeLog('INPUT|FUNCTION:' . json_encode($params).'|'.$path);
        Logs::writeELKLog($params, 'nl-vietcombank-checkout', 'INPUT', $function, '', 'checkout/alepay');

        $url = self::$url_api.$path;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if (substr(self::$url_api, 0, 5) == 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/10.0');
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            $result = curl_exec($ch);

            self::_writeLog('RESULT:' . $result);
            Logs::writeELKLog($result, 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/alepay');

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($result != '' && $status == 200) {
                $result = json_decode($result, true);
                return $result;
            }
        } catch (Exception $ex) {
            Logs::writeELKLog($ex->getMessage(), 'nl-vietcombank-checkout', 'OUTPUT', $function, '', 'checkout/alepay');

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
        $file_name = 'alepay' .DS. date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}