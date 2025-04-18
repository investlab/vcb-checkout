<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\payment_methods\vccb_va;

use common\components\utils\Logs;
use common\models\db\CheckoutOrder;
use common\models\db\PaymentMethod;
use common\payment_methods\PaymentMethodQrCodeForm;
use common\components\utils\Strings;
use common\models\business\CheckoutOrderBusiness;
use common\models\business\TransactionBusiness;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\partner_payments\PartnerPaymentBasic;
use const common\payment_methods\momo\ROOT_URL;

class PaymentMethodQrCodeVccbVaMomoQrCodeForm extends PaymentMethodQrCodeForm {

    function initRequest(PartnerPaymentBasic &$partner_payment) {
        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info) {
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => $this->getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                );
                $result = $this->partner_payment->processRequest($this, $inputs);
                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => $result['response']['token'],
                        'partner_payment_info' => json_encode($result['response']),
                        'user_id' => 0,
                    );
                    $result = TransactionBusiness::paying($inputs);
                    if ($result['error_message'] == '') {
                        if (!empty($payment_url)) {
                            header('Location:' . $payment_url);
                            die();
                        }
                    } else {
                        $this->error_message = $result['error_message'];
                    }
                } else {
                    $this->error_message = $result['error_message'];
                }
            }
        } else {
            $this->error_message = $result['error_message'];
        }
        return true;
    }

    function initVerify(PartnerPaymentBasic &$partner_payment) {
        $partner_payment->initConfirmVerify($this);
        return true;
    }
    function initRequestSeamless(PartnerPaymentBasic &$partner_payment)
    {
        $inputs = array(
            'checkout_order_id' => $this->checkout_order['id'],
            'payment_method_id' => $this->payment_method_id,
            'partner_payment_id' => $this->partner_payment_id,
            'partner_payment_method_refer_code' => '',
            'user_id' => 0,
        );
        $result = CheckoutOrderBusiness::requestPayment($inputs);
        if ($result['error_message'] == '') {
            $transaction_id = $result['transaction_id'];
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
            if ($transaction_info != false) {
                //------------
                $inputs = array(
                    'transaction_id' => $transaction_id,
                    'transaction_amount' => $this->getPartnerPaymentAmount($transaction_info),
                    'transaction_info' => $transaction_info,
                    'card_fullname' => '',
                    'card_number' => '',
                    'card_month' => '',
                    'card_year' => '',
                    'return_url' => ROOT_URL.'vi/checkout/version_1_0/confirm-verify/'.$this->checkout_order['token_code'].'?transaction_checksum='.$this->_getTransactionChecksum($transaction_id),
                    'notify_url' => ROOT_URL.'vi/checkout/version_1_0/confirm-verify/'.$this->checkout_order['token_code'].'?transaction_checksum='.$this->_getTransactionChecksum($transaction_id),
                    'cancel_url' =>  ROOT_URL.'vi/checkout/version_1_0/cancel/'.$this->checkout_order['token_code'],
                );
                $result = $this->partner_payment->processRequest($this, $inputs);

                if ($result['error_message'] == '') {
                    $payment_url = $result['payment_url'];
                    $inputs = array(
                        'transaction_id' => $transaction_id,
                        'partner_payment_method_refer_code' => $result['response']['token'],
                        'partner_payment_info' => json_encode($result['response']),
                        'user_id' => 0,
                    );
                    $result_paying = TransactionBusiness::paying($inputs);
                    if ($result_paying['error_message'] == '') {
                        return [
                            'error_code' => 0,
                            'error_message' => '',
                            'response' => [
                                'qrData' => $result['response']['qr_data'],
                                'amount' => $this->getPartnerPaymentAmount($transaction_info),
                                'id' => $this->checkout_order['id'],
                                'status' => CheckoutOrder::STATUS_PAYING,
                                'order_code' => $this->checkout_order['order_code'],
                                'sender_fee' => $transaction_info['sender_fee'],
                                'payment_method_info' => PaymentMethod::getPaymentMethodById($this->payment_method_id),
                            ],
                        ];
                    } else {
                        return [
                            'error_code' => 10007,
                            'error_message' => $result_paying['error_message'],
                            'response' => [

                            ],
                        ];
                    }
                } else {
                    return [
                        'error_code' => 10008,
                        'error_message' => $result['error_message'],
                        'response' => [

                        ],
                    ];
                }
            }
        } else {
            return [
                'error_code' => 10009,
                'error_message' => $result['error_message'],
                'response' => [

                ],
            ];
        }
    }

    public function processVerify() {
        return true;
    }

}
