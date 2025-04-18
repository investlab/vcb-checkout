<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use common\components\libs\NotifySystem;
use common\components\libs\Tables;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\payments\BIDVVA;
use common\payments\Momo;
use common\payments\MSBVA;
use common\util\Generator;
use phpDocumentor\Reflection\Types\Self_;
use Yii;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\NganLuongSeamless;
use common\models\db\Transaction;
use common\components\utils\Strings;
use yii\helpers\Html;
use yii\helpers\VarDumper;

class PartnerPaymentBidvVa extends PartnerPaymentBasic
{
    const PARTNER_PAYMENT_ID = APP_ENV == 'prod' ? 31 : 25;
    const BANK_ID = "BIDV";
    const PREFIX_ORDER = 'VCBPG';

    public function initRequest(PaymentMethodBasicForm &$form)
    {
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
                if (!empty($payment_method['field'])) {
                    return $payment_method['field'];
                } elseif ($payment_method == 'NOT_REQUIRED') {
                    return array();
                }
            }
        }
        return false;
    }

    public function processRequest(PaymentMethodBasicForm &$form, $params)
    {
        $data_input = [
            'account_number' => 123
        ];
        $error_message = '';

        $result = BIDVVA::createVA($data_input, $form, $params);

        if ($result['status']) {
            $payment_url = $this->_getAuthenUrl($result, $form, $params['transaction_id'],  $params['transaction_amount']);
        } else {
            NotifySystem::send("Tạo QR lỗi - BIDV_VA - " . $params['transaction_id'] . " - " . Html::encode($result['message']));
            $payment_url = '';
            $error_message = NganLuongSeamless::getErrorMessage($result['error_code']);
        }

        if (isset($params['version']) && $params['version'] == '2.0') {
            $result = [
                'qr_data' => $result['data'],
                'idQrcode' => $result['token'],
            ];
        }
        return array('error_message' => $error_message, 'response' => $result, 'payment_url' => $payment_url);
    }

    protected function _getAuthenUrl(&$result, PaymentMethodBasicForm $form, $transaction_id, $total_amount): string
    {
        $result['qr_data'] = 'data:image/png;base64, ' . $result['data'];
        $form->checkout_order['qrcode'] = @$result['data'];
        if (isset($form->checkout_order['version']) && $form->checkout_order['version'] == '2.0') {
            return '';
        }
        return $form->_getUrlVerify($transaction_id);
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
        }    }

}
