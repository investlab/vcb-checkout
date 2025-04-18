<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use common\models\db\Merchant;
use Yii;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\NganLuongSeamless;
use common\models\db\Transaction;
use common\components\utils\Strings;

class PartnerPaymentNganluongSeamless extends PartnerPaymentBasic {

    public function initRequest(PaymentMethodBasicForm &$form) {
        $payment_info = NganLuongSeamless::getPaymentMethodAndBankCode($form->payment_method_code);
        $inputs = array(
            'receiver_email' => $this->getPartnerPaymentAccountByCheckoutOrder($form->checkout_order, $form->partner_payment_code),
            'payment_method' => $payment_info['payment_method'],
            'bank_code' => $payment_info['bank_code'],
        );
        $response = NganLuongSeamless::getRequestField($inputs, $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
        //var_dump($response);die();
        if ($response['error_code'] == '00') {
            if (@$response['response']['bank']['bank_code'] == $payment_info['bank_code'] && isset($response['response']['bank']['payment_method'][$payment_info['payment_method']])) {
                $payment_method = $response['response']['bank']['payment_method'][$payment_info['payment_method']];
                if (isset($payment_method['field']) && !empty($payment_method['field'])) {
                    return $payment_method['field'];
                } elseif ($payment_method == 'NOT_REQUIRED') {
                    return array();
                }
            }
        }
        return false;
    }

    /**
     *
     * @param \common\payment_methods\nganluong_seamless\PaymentMethodAtmCardNganluongSeamlessForm $form
     * @param type $params : transaction_id, transaction_amount, transaction_info, card_fullname, card_number, card_month, card_year
     * @return type
     */
    public function processRequest(PaymentMethodBasicForm &$form, $params) {

        $error_message = 'Lỗi không xác định';
        $payment_url = null;
        //------------
        $payment_info = NganLuongSeamless::getPaymentMethodAndBankCode($form->payment_method_code);
        if (isset($params['return_url']) && !empty($params['return_url'])){
            $return_url = $params['return_url'];
        }else{
            $return_url = $form->_getUrlConfirmVerify($params['transaction_id']);
        }

        if (isset($params['cancel_url']) && !empty($params['cancel_url'])){
            $cancel_url = $params['cancel_url'];
        }else{
            $cancel_url = $form->_getUrlCancel();
        }
        $merchant_info = Merchant::getById($form->checkout_order['merchant_id']);

        if (isset($merchant_info) && isset($merchant_info['email_requirement']) && $merchant_info['email_requirement'] == 0 &&  $form->checkout_order['buyer_email'] == "notrequired@nganluong.vn"){
            $buyer_email = 'khachhang@nganluong.vn';
        }else{
            $buyer_email = $form->checkout_order['buyer_email'];


        }
        $inputs = array(
            'receiver_email' => $this->getPartnerPaymentAccount($params['transaction_info']),
            'cur_code' => 'VND',
            'order_code' => $GLOBALS['PREFIX'] . $params['transaction_id'],
            'total_amount' => $params['transaction_amount'],
            'payment_method' => $payment_info['payment_method'],
            'bank_code' => $payment_info['bank_code'],
            'order_description' => 'Thanh toán giao dịch ' . $params['transaction_id'] . ' cho đơn hàng ' . $form->checkout_order['order_code'],
            'tax_amount' => 0,
            'fee_shipping' => 0,
            'discount_amount' => 0,
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
            'notify_url' => $return_url,
            'buyer_fullname' => $form->checkout_order['buyer_fullname'],
            'buyer_email' => $buyer_email,
            'buyer_mobile' => $form->checkout_order['buyer_mobile'],
            'buyer_address' => $form->checkout_order['buyer_address'],
            'card_fullname' => $params['card_fullname'],
            'card_number' => $params['card_number'],
            'card_month' => $params['card_month'],
            'card_year' => $params['card_year'],
            'total_item' => 1,
        );
        if(isset($params['mobile'])){
            $inputs['mobile'] = $params['mobile'];
        }
        if(isset($params['identity_number'])){
            $inputs['identity_number'] = $params['identity_number'];
        }
        $response = NganLuongSeamless::checkout($inputs, $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
        if ($response['error_code'] == '00') {
            $error_message = '';
            $payment_url = $this->_getAuthenUrl($response, $form, $params['transaction_id']);
        } else {
            if ($response['error_code'] == '98' && trim(@$response['description']) != '') {
                $error_message = @$response['description'];
            } else {
                $error_message = NganLuongSeamless::getErrorMessage($response['error_code']);
            }
        }
        return array('error_message' => $error_message, 'response' => $response, 'payment_url' => $payment_url);
    }

    protected function _getAuthenUrl($result, PaymentMethodBasicForm $form, $transaction_id) {
        if (@$result['auth_site'] == 'NL') {
            return $form->_getUrlVerify($transaction_id);
        }
        if (@$result['auth_site'] == 'QRCODE') {
            $form->checkout_order['qrcode'] = $result['qr_data'];
            if(isset($form->checkout_order['version']) && $form->checkout_order['version']=='2.0'){
                return '';
            }
            return $form->_getUrlVerify($transaction_id);
        }
        return $result['auth_url'];
    }

    function initVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            return true;
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }

    public function processVerify(PaymentMethodBasicForm &$form, $params) {
        $error_message = 'Lỗi không xác định';
        $bank_refer_code = null;
        $partner_code = null;

        //------------
        NganLuongSeamless::$receiver_email = $this->getPartnerPaymentAccount($form->payment_transaction);
        $payment_info = NganLuongSeamless::getPaymentMethodAndBankCode($form->payment_method_code);
        $inputs = array(
            'receiver_email' => NganLuongSeamless::$receiver_email,
            'token' => $form->payment_transaction['partner_payment_method_refer_code'],
            'otp' => $form->otp,
            'auth_url' => @$form->payment_transaction['partner_payment_info']['auth_url'],
        );
        $response = NganLuongSeamless::authenTransaction($inputs, $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
        if ($response['error_code'] == '00') {
            $response = NganLuongSeamless::getTransactionDetail($form->payment_transaction['partner_payment_method_refer_code'], $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
            if (NganLuongSeamless::verifyResponse($response, $form->payment_transaction, $error_message)) {
                $error_message = '';
                $bank_refer_code = @$response['transaction_id'];
                $partner_code = isset($response['partner_code'])?@$response['partner_code']:'';



            }
        } else {
            if ($response['error_code'] == '98' && trim(@$response['description']) != '') {
                $error_message = @$response['description'];
            } else {
                $error_message = NganLuongSeamless::getErrorMessage($response['error_code']);
            }
        }
        return array('error_message' => $error_message, 'bank_refer_code' => $bank_refer_code, 'partner_code' => $partner_code);
    }

    function initConfirmVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            NganLuongSeamless::$receiver_email = $this->getPartnerPaymentAccount($form->payment_transaction);
            $result = NganLuongSeamless::getTransactionDetail($form->payment_transaction['partner_payment_method_refer_code'], $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
            if (NganLuongSeamless::verifyResponse($result, $form->payment_transaction, $error_message)) {
                if ($form->payment_transaction['status'] == Transaction::STATUS_PAYING || $form->payment_transaction['status'] == Transaction::STATUS_NEW) {
                    $inputs = array(
                        'transaction_id' => $form->payment_transaction['id'],
                        'bank_refer_code' => @$result['transaction_id'],
                        'time_paid' => time(),
                        'user_id' => 0,
                        'partner_code' => isset($result['partner_code'])?@$result['partner_code']:'',

                    );
                    $result = TransactionBusiness::paid($inputs);
                    if ($result['error_message'] === '') {
                        header('Location:' . $form->_getUrlSuccess($form->payment_transaction['id']));
                        die();
                    } else {
                        $form->error_message = $result['error_message'];
                    }
                } elseif ($form->payment_transaction['status'] == Transaction::STATUS_PAID) {
                    header('Location:' . $form->_getUrlSuccess($form->payment_transaction['id']));
                    die();
                } else {
                    $form->error_message = 'Đơn hàng không hợp lệ';
                }
            } else {
                $form->error_message = $error_message;
            }
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }

}
