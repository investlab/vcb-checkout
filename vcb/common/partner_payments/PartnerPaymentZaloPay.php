<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use common\models\db\CheckoutOrder;
use common\payments\ZALOPAY;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\payments\NganLuongSeamless;
use common\util\QRCodeHelper;

class PartnerPaymentZaloPay extends PartnerPaymentBasic
{
    const PARTNER_PAYMENT_ID = APP_ENV == 'prod' ? 33 : 30;
    const BANK_ID = "VCB";
    const PREFIX_ORDER = 'VCBPG';

    public function processRequest(PaymentMethodBasicForm &$form, $params)
    {
        $error_message = '';

        if (isset($params['return_url']) && !empty($params['return_url'])) {
            $return_url = $params['return_url'];
        } else {
            $return_url = $form->_getUrlConfirmVerify($params['transaction_id']);
        }

        $inputs = [
            'transaction_id' => $params['transaction_id'],
            'transaction_amount' => $params['transaction_amount'],
            'return_url' => $return_url,
//            'notify_url' => 'https://59f4-14-177-239-192.ngrok-free.app' . '/api/web/partner/zalo-pay-notify',
            'notify_url' => ROOT_URL . 'api/web/partner/zalo-pay-notify',
            'transaction_info' => $params['transaction_info'],
        ];

        $result = ZALOPAY::createVA($inputs);
        if ($result['status'] && $result['error_code'] == 1) {
            $payment_url = $this->_getAuthenUrl($result, $form, $params['transaction_id'], $params['transaction_amount']);
        } else {
            $payment_url = '';
            $error_message = NganLuongSeamless::getErrorMessage($result['error_code']);
        }

        return array('error_message' => $error_message, 'response' => $result, 'payment_url' => $payment_url);
    }

    protected function _getAuthenUrl(&$result, PaymentMethodBasicForm $form, $transaction_id, $total_amount): string
    {
        if (isset($result['data']->return_code) && $result['data']->return_code == 1) {

            $data = QRCodeHelper::generateFromText($result['data']->order_url);

            $result['qr_data'] = 'data:image/png;base64, ' . $data;

            return $form->_getUrlVerify($transaction_id);

        }
        return false;
    }

    function initVerify(PaymentMethodBasicForm &$form)
    {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            return true;
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }

    public function processVerify(PaymentMethodBasicForm &$form, $params)
    {
        $error_message = 'Lỗi không xác định';
        $bank_refer_code = null;
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
            }
        } else {
            if ($response['error_code'] == '98' && trim(@$response['description']) != '') {
                $error_message = @$response['description'];
            } else {
                $error_message = NganLuongSeamless::getErrorMessage($response['error_code']);
            }
        }
        return array('error_message' => $error_message, 'bank_refer_code' => $bank_refer_code);
    }

    function initConfirmVerify(PaymentMethodBasicForm &$form)
    {
        $checkout_order = CheckoutOrder::find()
            ->where(['token_code' => $form->checkout_order['token_code']])
            ->one();
        if ($checkout_order->status == CheckoutOrder::STATUS_PAID) {
            header('Location:' . $form->_getUrlSuccess($checkout_order->transaction_id));
            die();
        } elseif ($checkout_order->status == CheckoutOrder::STATUS_FAILURE) {
            header('Location:' . $form->_getUrlCancel());
            die();
        }
    }

}
