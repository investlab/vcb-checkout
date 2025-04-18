<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use Yii;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\NganLuong;
use common\models\db\Transaction;

class PartnerPaymentNganluong extends PartnerPaymentBasic
{

    /**
     *
     * @param
     * @param type $params : transaction_id, transaction_amount, transaction_info
     * @return type
     */
    public function processRequest(PaymentMethodBasicForm &$form, $params)
    {
        $error_message = 'Lỗi không xác định';
        $payment_url = null;
        //------------
        $payment_info = NganLuong::getPaymentMethodAndBankCode($form->payment_method_code);
        $inputs = array(
            'receiver_email' => $this->getPartnerPaymentAccount($params['transaction_info']),
            'cur_code' => 'VND',
            'order_code' => $GLOBALS['PREFIX'].$params['transaction_id'],
            'total_amount' => $params['transaction_amount'],
            'payment_method' => $payment_info['payment_method'],
            'bank_code' => $payment_info['bank_code'],
            'order_description' => 'Thanh toán giao dịch ' . $params['transaction_id'] . ' cho đơn hàng ' . $form->checkout_order['order_code'],
            'tax_amount' => 0,
            'fee_shipping' => 0,
            'discount_amount' => 0,
            'return_url' => $form->_getUrlConfirmVerify($params['transaction_id']),
            'cancel_url' => $form->_getUrlCancel(),
            'buyer_fullname' => $form->checkout_order['buyer_fullname'],
            'buyer_email' => $form->checkout_order['buyer_email'],
            'buyer_mobile' => $form->checkout_order['buyer_mobile'],
            'buyer_address' => $form->checkout_order['buyer_address'],
            'total_item' => 1,
            'lang_code' => (Yii::$app->language=='vi-VN'?'vi':'en'),
        );
        $response = NganLuong::checkout($inputs,$form['checkout_order']['merchant_id'],$form ["info"]["partner_payment_id"]);
        if ($response['error_code'] == '00') {
            $error_message = '';
            $payment_url = $this->_getPaymentUrlLanguage($response['checkout_url']);
        } else {
            $error_message = $response['description'];
        }
        return array('error_message' => $error_message, 'response' => $response, 'payment_url' => $payment_url);
    }

    private function _getPaymentUrlLanguage($payment_url)
    {
        return $payment_url;
    }

    function initConfirmVerify(PaymentMethodBasicForm &$form)
    {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            NganLuong::$receiver_email = $this->getPartnerPaymentAccount($form->payment_transaction);
            $result = NganLuong::getTransactionDetail($form->payment_transaction['partner_payment_method_refer_code'],$form['checkout_order']['merchant_id'],$form ["info"]["partner_payment_id"]);
            if (NganLuong::verifyResponse($result, $form->payment_transaction, $error_message)) {
                if ($form->payment_transaction['status'] == Transaction::STATUS_PAYING || $form->payment_transaction['status'] == Transaction::STATUS_NEW) {
                    $inputs = array(
                        'transaction_id' => $form->payment_transaction['id'],
                        'bank_refer_code' => @$result['transaction_id'],
                        'time_paid' => time(),
                        'user_id' => 0,
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
    }
}
