<?php

namespace common\payments;

use common\models\db\PartnerPaymentAccount;
use common\components\utils\Logs;

class NganLuongMultiRefund {
    
    const NGANLUONG_API_MULTI_REFUND_URL = NGANLUONG_URL . 'service/multiRefund';

    public $nl_merchant_id;
    public $nl_merchant_password;
    public $nl_receiver_email;
    public $merchant_id;
    public $partner_merchant_id;
    public $log_id;

    public function __construct($merchant_id, $partner_payment_id) {
        $this->merchant_id = $merchant_id;
        $this->partner_payment_id = $partner_payment_id;
        $this->log_id = uniqid();
        $this->getConnectParams();
    }

    private function getConnectParams() {
        $partner_payment_account_info = PartnerPaymentAccount::getByMerchantIdAndPartnerPaymentId($this->merchant_id, $this->partner_payment_id);
        if (!empty($partner_payment_account_info)) {
            $this->nl_merchant_id = $partner_payment_account_info['partner_merchant_id'];
            $this->nl_merchant_password = $partner_payment_account_info['partner_merchant_password'];
            $this->nl_receiver_email = $partner_payment_account_info['partner_payment_account'];
        }
    }
    
    // tao yeu cau hoan tien
    public function setRefundRequest($params) {
        $error_message = 'Lỗi không xác định';
        $data = [];
        $request = [
            'merchant_id' => $this->nl_merchant_id,
            'merchant_email' => $this->nl_receiver_email,
            'func' => 'SetRefundRequest',
            'ref_code_refund' => $params['ref_code_refund'],
            'amount' => $params['amount'],
            'transaction_id' => $params['transaction_id'],
            'reason' => $params['reason'],
        ];
        $request['checksum'] = md5($params['ref_code_refund'] . ' ' 
                . $params['transaction_id'] . ' ' 
                . $params['amount'] . ' '
                . $this->nl_merchant_password);
        $response = $this->curl(self::NGANLUONG_API_MULTI_REFUND_URL, $request, true);
        if (!empty($response['response'])) {
            $data = $response['response'];
            if ($data['error_code'] == '00') {
                $error_message = '';
            } else {
                $error_message = $this->getErrorMessage($data['error_code']);
            }
        }
        return ['error_message' => $error_message, 'data' => $data];
    }
    
    // kiem tra trang thai giao dich hoan tien
    public function checkRefund($params) {
        $error_message = 'Lỗi không xác định';
        $data = [];
        $request = [
            'merchant_id' => $this->nl_merchant_id,
            'merchant_email' => $this->nl_receiver_email,
            'func' => 'CheckRefund',
            'ref_code_refund' => $params['ref_code_refund'],
        ];
        $request['checksum'] = md5($params['ref_code_refund'] . ' ' 
                . '' . ' ' 
                . $this->nl_merchant_password);
        $response = $this->curl(self::NGANLUONG_API_MULTI_REFUND_URL, $request, true);
        if (!empty($response['response'])) {
            $data = $response['response'];
            if ($data['error_code'] == '00') {
                $error_message = '';
            } else {
                $error_message = $this->getErrorMessage($data['error_code']);
            }
        }
        return ['error_message' => $error_message, 'data' => $data];
    }
    
    public function getErrorMessage($response_code) {
        $error_message_default = 'Lỗi không xác định';
        $error_list = [
            '00' => 'Thành công',
            '99' => 'Lỗi không xác định',
            '01' => 'Merchant không được phép sử dụng phương thức này',
            '02' => 'Thông tin kết nối không hợp lệ (merchant_email và merchant_id không hợp lệ)',
            '03' => 'Số tiền hoàn lại không hợp lệ',
            '04' => 'Tổng tiền hoàn lại vượt quá số tiền thanh toán của giao dịch',
            '05' => 'Có lỗi trong quá trình kết nối',
            '06' => 'Mã tham chiếu (ref_code_refund) không hợp lệ',
            '07' => 'Mã tham chiếu (ref_code_refund) đã tồn tại',
            '08' => 'Function không đúng',
            '09' => 'Merchant_email không tồn tại trên nganluong.vn',
            '10' => 'Merchant_email đang bị khóa hoặc phong tỏa không thể giao dịch',
            '11' => 'Mã giao dịch thanh toán (transaction_id) không tồn tại',
            '12' => 'Mã giao dịch hoàn tiền (transaction_refund_id) không tồn tại',
            '13' => 'Số dư tài khoản không đủ để thực hiện giao dịch',
            '14' => 'Trạng thái giao dịch thanh toán (transaction_id) không hợp lệ',
            '15' => 'Mã checksum không chính xác',
            '16' => 'Merchant_id không tồn tại hoặc chưa được kích hoạt'
        ];
        return (array_key_exists($response_code, $error_list)) ? $error_list[$response_code] : $error_message_default;
    }
    
    private function curl($url, $data = array(), $is_post = false) {
        $this->writeLog('[URL] ' . $url);
        $this->writeLog('[REQUEST] ' . json_encode($data));
        Logs::writeELKLog($data, 'nl-vietcombank-checkout', 'INPUT', $data['func'], '', 'checkout/nganluong_multi_refund');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($is_post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $output = [
            'response' => json_decode($result, true),
            'http_code' => $http_code,
            'curl_error' => $curl_error
        ];
        $this->writeLog('[RESPONSE] ' . json_encode($output));
        Logs::writeELKLog($output, 'nl-vietcombank-checkout', 'OUTPUT', $data['func'], '', 'checkout/nganluong_multi_refund');

        return $output;
    }
    
    private function writeLog($data) {
        $file_name = 'nganluong_multi_refund' . DS . date("Ymd", time()) . ".txt";
        $pathinfo = pathinfo($file_name);
        $data = $this->log_id . ' ' . $data;
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}