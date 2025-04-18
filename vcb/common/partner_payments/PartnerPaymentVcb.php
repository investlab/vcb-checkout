<?php
/**
 * Created by PhpStorm.
 * User: NEXTTECH
 * Date: 11/22/2019
 * Time: 9:28 AM
 */

namespace common\partner_payments;

use checkout\controllers\ToolController;
use common\components\libs\NotifySystem;
use common\components\libs\qrcode\QrCode;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\models\db\Transaction;
use common\payment_methods\PaymentMethodBasicForm;
use common\payments\VCB;
use common\components\utils\ObjInput;
use Yii;


class PartnerPaymentVcb extends PartnerPaymentBasic
{
    public function initRequest(PaymentMethodBasicForm &$form)
    {
        return array(
            'BANK_ACCOUNT', 'BANK_NAME', 'ISSUE_MONTH', 'ISSUE_YEAR'
        );

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

    public function processRequest(PaymentMethodBasicForm &$form, $params)
    {

        $payment_info = $this->getPaymentMethodAndBankCode($form->payment_method_code);

        if ($payment_info['payment_method'] == "ATM_ONLINE") {
            $inputs = array(
                'trans_id' => self::makeTransID($params['transaction_id']),
                'card_number' => $params['card_number'],
                'card_holder_name' => $params['card_fullname'],
                'card_issue_date' => $params['card_month'] . $params['card_year'],
                'amount' => $params['transaction_amount'],
                'client_ip' => $_SERVER['REMOTE_ADDR'],
                'description' => 'Thanh toán mã đơn hàng' . $params['transaction_info'] ["checkout_order_id"],

            );
            $session = Yii::$app->session;
            $session->set('old_input', $inputs);
            $response = VCB::verifyCard($inputs, $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
            //gia lap test
//        $response = [
//            'status' => true,
//            'error_code' => '1',
//            'message' => 'gio dich thanh cong',
//                'data' => [
//                    'trans_id' => $inputs['trans_id'],
//                    'otp_phone_number' => '669',
//                    'hash_code' => time(),
//                ],
//            ];
            //end gia lap
            if ($response['error_code'] == '1') {
//            $cardInfo = [
//                'card_fullname' => $params['card_fullname'],
//                'card_number' => $params['card_number'],
//                'card_month' => $params['card_month'],
//                'card_year' => $params['card_year'],
//            ];
//            Transaction::insertCardInfo($params['transaction_id'], $cardInfo);
                $error_message = '';
                $payment_url = $this->_getAuthenUrl($response, $form, $params['transaction_id']);
                $data = $response['data'];
            } else {
                $error_message = $response['message'];
                $payment_url = '';
                $data = '';

            }
        } elseif ($payment_info['payment_method'] == "QRCODE") {
            $inputs = array(
                'trans_id' => self::makeTransID($params['transaction_id']),
                'amount' => $params['transaction_amount'],
                'client_ip' => $_SERVER['REMOTE_ADDR'],
                'description' => 'Thanh toán mã đơn hàng' . $params['transaction_info'] ["checkout_order_id"],

            );
            $session = Yii::$app->session;
            $session->set('old_input', $inputs);
            $response = VCB::QrCodePayment($inputs, $form, $params);
            //gia lap test
//            $response = [
//                'status' => true,
//                'error_code' => '1',
//                'message' => 'gio dich thanh cong',
//                'data' => [
//                    'trans_id' => $inputs['trans_id'],
//                    'otp_phone_number' => '669',
//                    'hash_code' => time(),
//                ],
//            ];
            //end gia lap
            if ($response['error_code'] == '00') {
                $error_message = '';
                $payment_url = $this->_getAuthenUrl($response, $form, $params['transaction_id']);
//                $response[]['qr_data'] = $response['data']['data'];
                if ($params['version'] == '2.0') {
                    $data = [
                        'qr_data' => self::genQRcode($response['data']['data']),
                        'idQrcode' => $response['data']['idQrcode'],
                    ];
                } else {
                    $data = [
                        'qr_data' => ($response['data']['data']),
                        'idQrcode' => $response['data']['idQrcode'],
                    ];
                }


            } else {
                $error_message = $response['message'];
//                21 => Terminal invalid
                if ($response['error_code'] != 21) {
                    NotifySystem::send("Tạo QR lỗi - VCB ONus - " . $params['transaction_id'] . " - " . $error_message);
                }
                $payment_url = '';
                $data = '';

            }
        }

        return array('error_message' => $error_message, 'response' => $data, 'payment_url' => $payment_url);

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
        $card_input = (Yii::$app->session->get('old_input'));
        $input_otp_verify = array(
            'trans_id' => $form['payment_transaction']['partner_payment_info']['trans_id'],
            'card_number' => $card_input['card_number'],
            'card_holder_name' => $card_input['card_holder_name'],
            'card_issue_date' => $card_input['card_issue_date'],
            'amount' => $card_input['amount'],
            'otp' => $form['otp'],
            'hash_code' => $form['payment_transaction']['partner_payment_info']['hash_code'],
        );
        $result = VCB::VerifyOtp($input_otp_verify, $form['checkout_order']['merchant_id'], $form ["info"]["partner_payment_id"]);
        // gia lap test
//        $result = [
//            'status' => true,
//            'error_code' => '1',
//            'message' => 'gio dich thanh cong',
//            'data' => [
//                'trans_id' => $input_otp_verify['trans_id'],
//            ],
//        ];
        //end gia lap test
        if ($result['error_code'] == 1) {
            $error_message = '';
            $bank_refer_code = @$result['data']['trans_id'];
            if (empty($bank_refer_code)) {
                $error_message = 'Lỗi kết nối xác thực giao dịch (Không nhận được phản hồi trans_id từ phía bank )';
                $bank_refer_code = '';
            }

        } else {
            $error_message = $result['message'];
            $bank_refer_code = '';

        }
        return array('error_message' => $error_message, 'bank_refer_code' => $bank_refer_code);

    }

    protected function _getAuthenUrl($result, PaymentMethodBasicForm $form, $transaction_id)
    {

        $form->checkout_order['qrcode'] = @$result['data'];
        if (isset($form->checkout_order['version']) && $form->checkout_order['version'] == '2.0') {
            return '';
        }
        return $form->_getUrlVerify($transaction_id);
        //test
    }

    public function makeTransID($trans_id)
    {
        $lenght = strlen($trans_id);
        $prefix = 'NL';
        $after_fix = '';
        if ($lenght == 1) {
            $after_fix = '00000';
        } elseif ($lenght == 2) {
            $after_fix = '0000';
        } elseif ($lenght == 3) {
            $after_fix = '000';
        } elseif ($lenght == 4) {
            $after_fix = '00';
        } elseif ($lenght == 5) {
            $after_fix = '0';
        } else {
            $after_fix = '';
        }
        return $prefix . $after_fix . $trans_id;
    }

    public static function getPaymentMethodAndBankCode($payment_method_code): array
    {
        $payment_method = '';
        $bank_code = '';
        if (substr($payment_method_code, -8) == 'ATM-CARD') {
            $payment_method = 'ATM_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 9);
        } elseif (substr($payment_method_code, -9) == 'IB-ONLINE') {
            $payment_method = 'IB_ONLINE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 10);
        } elseif (substr($payment_method_code, -7) == 'QR-CODE') {
            $payment_method = 'QRCODE';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 8);
        } elseif (substr($payment_method_code, -9) == 'QRCODE247') {
            $payment_method = 'QRCODE247';
            $bank_code = substr($payment_method_code, 0, strlen($payment_method_code) - 10);
        }
        return array('payment_method' => $payment_method, 'bank_code' => $bank_code);
    }

    protected function genQRcode($qrData)
    {
        ob_start();
        QrCode::png(
            $qrData,
            $outfile = false,
            $level = 3,
            $size = 5,
            $margin = 4,
            $saveandprint = false
        );
        $imageString = base64_encode(ob_get_clean());
        header('Content-Type: text/html');
//        ob_end_clean();
        if(ob_get_length()>0) ob_end_clean();

        return $imageString;
    }


}