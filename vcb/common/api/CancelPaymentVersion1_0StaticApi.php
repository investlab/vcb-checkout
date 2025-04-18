<?php

namespace common\api;

use common\models\business\TransactionBusiness;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\payments\NganLuongSeamless;
use Yii;
use common\models\db\Merchant;
use common\components\utils\Validation;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;
use common\models\business\CashoutBusiness;
use common\payments\NganLuongTransferOld;
use common\components\utils\Translate;
use common\models\db\Cashout;
use common\components\libs\Tables;

class CancelPaymentVersion1_0StaticApi extends CancelPaymentBasicApi {

    public function getVersion() {
        return '1.0';
    }

    protected function _isFunction($function) {
        return ($function == 'CancelPayment');
    }

    public function getData($function) {
        if ($function == 'CancelPayment') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['token_code'] = ObjInput::get('token_code', 'str', '');
            $data['order_code'] = ObjInput::get('order_code', 'str', '');
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code) {
        $message = array(
            '0000' => 'Thành công',
            '0001' => 'Lỗi không xác định',
            '0002' => 'Tên hàm không hợp lệ',
            '0003' => 'Mã merchant_site_code không hợp lệ hoặc không tồn tại',
            '0004' => 'Số tiền không hợp lệ',
            '0005' => 'Mã order_code không đúng',
            '0006' => 'Mã token_code không hợp lệ',
            '0008' => 'Mã checksum không chính xác',
            '0009' => 'merchant_email không hợp lệ hoặc không tồn tại',
            '0010' => 'Mã merchant_site_code không thuộc merchant_email',
            '0101' => 'Hủy giao dịch thất bại',
            '0102' => 'Đơn hàng đã được hủy trước đó',
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }

    /**
     *
     * @param type $params : merchant_site_code, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, language
     * @return array
     */
    protected function _cancelPayment($params) {
        $error_code = '0001';
        $result_data = null;
        $result_mesage = null;
        //-------------
        $checkout_order = CheckoutOrder::findOne(['token_code' => $params['token_code'],'order_code' => $params['order_code']]);
        $flag_cancel = false;
        if (!is_null($checkout_order))
        {
            if (!in_array($checkout_order->status, [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_CANCEL, CheckoutOrder::STATUS_PAYING])) {
                $error_code = '0101';
                return array(
                    'error_code'     => $error_code,
                    'result_message' => self::getResultMessage($error_code),
                    'result_data'    => $result_data
                );
            }

            if ($checkout_order->status == CheckoutOrder::STATUS_CANCEL) {
                $error_code = '0102';
                return array(
                    'error_code'     => $error_code,
                    'result_message' => self::getResultMessage($error_code),
                    'result_data'    => $result_data
                );
            }

            if(in_array($checkout_order->status, [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING])){
                //TODO Hủy trên cổng
                $flag_cancel = true;
            }

            if($flag_cancel){
                $result = self::cancelOrderAndTransaction($checkout_order);
                if($result['error_message'] == ''){
                    $error_code = '0000';
                } else{
                    $error_code = '0001';
                }
            }

            $new_checkout_order = CheckoutOrder::findOne(['token_code' => $params['token_code'],'order_code' => $params['order_code']]);
            $result_data = [
                'order_code' => $new_checkout_order->order_code,
                'status' => $new_checkout_order->status,
            ];

        }

        return array('error_code' => $error_code, 'result_message' => self::getResultMessage($error_code), 'result_data' => $result_data);
    }

    protected static function cancelOrderAndTransaction($checkout_order, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $params = array(
            'checkout_order_id' => $checkout_order['id'],
            'user_id'           => 0
        );
        $order_result = CheckoutOrderBusiness::updateStatusCancel($params);
        if ($order_result['error_message'] == '') {
            if (isset($checkout_order['transaction_id'])) {
                $params = array(
                    'transaction_id' => $checkout_order['transaction_id'],
                    'reason_id'   => 0,
                    'reason'      => 'Merchant hủy giao dịch',
                    'user_id'     => 0
                );
                $transaction_result = TransactionBusiness::cancel($params);
                if($transaction_result['error_message'] == ''){
                    $commit = true;
                    $error_message = '';
                } else{
                    @self::_writeLog('[CHECKOUT_ORDER_ID]: ' . $checkout_order['id'] . ' | TRANSACTION_CANCEL_FAIL: |  ' . json_encode($transaction_result));
                    $error_message = $transaction_result['error_message'];
                }
            } else{
                $commit = true;
                $error_message = '';
            }
        } else{
            @self::_writeLog('[CHECKOUT_ORDER_ID]: ' . $checkout_order['id'] . ' | ORDER_CANCEL_FAIL: |  ' . json_encode($order_result));
            $error_message = $order_result['error_message'];
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }

        return ['error_message' => $error_message];

    }

    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, language, checksum
     */
    protected function _validateDataCancelPayment(&$data) {

        $error_code = '0001';

        if($data['merchant_site_code'] === ''){
            $error_code = '0003'; // merchant_site_code khong hop le
            return array('error_code' => $error_code);
        }

//        $merchant_id = self::getMerchantIdFromMerchantSiteCode($data['merchant_site_code']); // với cổng Qrbank
        $merchant_id = $data['merchant_site_code'];
        $api_key = Merchant::getApiKey($merchant_id, $this->merchant_info);
        if ($api_key !== false) {
            //new
            $validate_tk_oc = $this->_validateTokenCodeAndOrderCode($data['token_code'], $data['order_code']);
            if( !$validate_tk_oc['token_code_result'] ){
                $error_code = '0006'; // sai token code
                return array('error_code' => $error_code);
            }

            if( !$validate_tk_oc['order_code_result'] ){
                $error_code = '0005';// sai order code
                return array('error_code' => $error_code);
            }

            if($validate_tk_oc['token_code_result'] && $validate_tk_oc['order_code_result']){
                //continue
                if ($this->_validateChecksumCancelPayment($data, $api_key)) {
                    $error_code = '0000';
                }else {
                    $error_code = '0008';
                }
            }

            //OLD
//            if ($this->_validateTokenCode($data['token_code'])) {
//                if ($this->_validateOrderCode($data['order_code'])) {
//                    if ($this->_validateChecksumCancelPayment($data, $api_key)) {
//                        $error_code = '0000';
//                    }else {
//                        $error_code = '0008';
//                    }
//                }else{
//                    $error_code = '0005';
//                }
//            } else {
//                $error_code = '0006';
//            }

        }else{
            $error_code = '0003';
        }

        return array('error_code' => $error_code);
    }

    protected function _validateChecksumCancelPayment($data, $api_key) {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= ' ' . $data['token_code'];
        $str_checksum .= ' ' . $data['order_code'];
        $str_checksum .= ' ' . $api_key;

        $this->writeLog('[Checksum_param]' . $data['checksum']);
        $this->writeLog('[Checksum_SYS]' . hash('sha256',$str_checksum));

        if ($data['checksum'] === hash('sha256',$str_checksum)) {
            return true;
        }else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die($str_checksum . " ====== " . hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected static function _writeLog($data) {
        $log_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'api' . DS . 'cancel_payment' . DS;
        if (is_dir($log_path) || mkdir($log_path, 0777, true)) {
            $log_file = date('Ymd') . '.txt';
            $file = fopen($log_path . $log_file, 'a+');
            if ($file) {
                fwrite($file, '[' . date('H:i:s, d/m/Y') . '] ' . $data . "\n");
                fclose($file);
                return true;
            }
        }
        return false;
    }
}
