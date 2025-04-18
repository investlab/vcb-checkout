<?php
/**
 * Author: tinbt
 * Project: vietcombank-checkout
 * Time: 10/10/2023 08:49
 **/


namespace common\partner_payments;

use common\components\libs\NotifySystem;
use common\components\libs\Tables;
use common\components\utils\Logs;
use common\components\utils\Translate;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\PartnerPayment;
use common\models\db\PartnerPaymentAccount;
use common\payments\BIDVVA;
use common\payments\Momo;
use common\payments\MSBVA;
use common\payments\VCBVA;
use common\payments\VCCBVA;
use common\util\Generator;
use phpDocumentor\Reflection\Types\Self_;
use Yii;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\NganLuongSeamless;
use common\models\db\Transaction;
use common\components\utils\Strings;
use yii\helpers\VarDumper;

class PartnerPaymentVcbVa extends PartnerPaymentBasic
{
    const PARTNER_PAYMENT_ID = APP_ENV == 'prod' ? 32 : 25;
    const BANK_ID = "VCB";
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

        $result = VCBVA::createVA($data_input, $form, $params);
//        var_dump($result['data']);
        if ($result['status']) {
            $payment_url = $this->_getAuthenUrl($result, $form, $params['transaction_id'],  $params['transaction_amount']);
//            if (isset($params['transaction_info']['merchant_id']) && $params['transaction_info']['merchant_id'] == 7){
//                NotifySystem::send("PAYMENT_URL - VCB_VA - " . json_encode($payment_url));
//
//            }
        } else {
            $payment_url = '';
            NotifySystem::send("Tạo QR lỗi - VCB_VA - " . $params['transaction_id'] . " - " . $result['message']);

            #regioon Update Failure
            $update_fail = TransactionBusiness::failure([
                'transaction_id' => $params['transaction_id'],
                'transaction_type_id' => $params['transaction_info']['transaction_type_id'],
                'bank_refer_code' => $params['transaction_info']['bank_refer_code'],
                'time_paid' => time(),
                'user_id' => 0,
//                'month' => $month,
                'payment_info' => '',
                'reason_id' => $result['error_code'],
                'reason' => VCBVA::getErrorMessageGenQr($result['error_code']),
            ]);
            if ($update_fail['error_message'] == '') {
                $inputs = array(
                    'checkout_order_id' => $params['transaction_info']['checkout_order_id'],
                    'user_id' => '0',
                );
                $update_checkout_order_failure = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
//                $update_checkout_order_failure['error_message'] = 'LOI DAY';
                if ($update_checkout_order_failure['error_message'] === '') {
                    $error = '';
                    self::_writeLog(LOG_PATH . 'partner_payment' . DS . 'vcb_va' . DS .  date('Ymd', time()) . '.txt', '[ERROR_GEN_QR]: ' .  $error);
                } else {
                    $error = 'Lỗi cập nhật failure: ' . Translate::get($update_checkout_order_failure['error_message']);
                    self::_writeLog(LOG_PATH . 'partner_payment' . DS . 'vcb_va' . DS .  date('Ymd', time()) . '.txt', '[ERROR_GEN_QR]: ' .  $error);

                }
                // notify merchant
            } else {
                $error = 'Lỗi cập nhật failure: ' . $update_fail['error_message'];
                self::_writeLog(LOG_PATH . 'partner_payment' . DS . 'vcb_va' . DS .  date('Ymd', time()) . '.txt', '[ERROR_GEN_QR]: ' .  $error);
            }
            #endregion

            $url = Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/failure', 'token_code' =>  $form->checkout_order['token_code'],], HTTP_CODE);
            header('Location:' . $url);
            die();
            // redirect luon  ve trang that bai

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
