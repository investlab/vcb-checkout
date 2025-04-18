<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/24/2016
 * Time: 12:24 PM
 */

namespace common\partner_payments;

use common\components\libs\NotifySystem;
use common\models\db\CheckoutOrder;
use common\payments\VCCBVA;
use common\util\Generator;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\payments\NganLuongSeamless;
use common\util\Helpers;

class PartnerPaymentVccbVa extends PartnerPaymentBasic
{
    const BANK_ID = "VCCB";

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
                if (isset($payment_method['field']) && !empty($payment_method['field'])) {
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

        $result = VCCBVA::createVA($data_input, $form, $params);

        if ($result['status'] && $result['error_code'] == '00') {
//            NotifySystem::send("Tạo QR thành công - VCCB_VA - " . $result['message']);
            $payment_url = $this->_getAuthenUrl($result, $form, $params['transaction_id'],  $params['transaction_amount']);
        } else {
            NotifySystem::send("Tạo QR lỗi - VCCB_VA - " . $params['transaction_id'] . " - " . $result['message']);
            $payment_url = '';
            $error_message = NganLuongSeamless::getErrorMessage($result['error_code']);
        }

        return array('error_message' => $error_message, 'response' => $result, 'payment_url' => $payment_url);
    }

    protected function _getAuthenUrl(&$result, PaymentMethodBasicForm $form, $transaction_id, $total_amount): string
    {
        $gen = Generator::create()->bankId(self::BANK_ID) // BankId, bankname
                ->accountNo($result['data']["data"]['accNo'])// Account number
//                ->accountNo('8007041011503')// số tài khoản của QuangNT test :)))
                ->amount($total_amount)// Money
                ->info("VCBPG". $transaction_id . Helpers::randomStringAlphabet(2)) // Ref
                ->returnText(false) // if true, return text. If false, return image in base64
                ->logoPath(ROOT_URL . '/checkout/web/bank/center_qr/vietqr.png')
                ->generate();
        $result['qr_data'] = json_decode($gen)->data;
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

    public function generateQr($params)
    {
        $str_gen = '00020101021238';
        $account_bank = $params['bank_account'];
        $info_card = '0006' . $account_bank['bank_ref'] .
            '01' . self::getLength($account_bank['account_number']) . $account_bank['account_number'];
        $merchant_info_qr = '0010A00000072701' . self::getLength($info_card) . $info_card . '0208QRIBFTTA';
        $str_gen .= self::getLength($merchant_info_qr) . $merchant_info_qr;
        $str_gen .= '5303704';
        $amount = str_replace('.', '', $params['cashin']['amount']);
        $str_gen .= '54' . self::getLength($amount) . $amount;
        $str_gen .= '5802VN';
        $content = $account_bank['content'];
        $str_content = '08' . self::getLength($content) . $content;
        $str_gen .= '62' . self::getLength($str_content) . $str_content;
        $str_gen .= '6304';
        $str_gen .= dechex(self::CRC16Normal($str_gen));

        $qrcode = self::makeQrcode($str_gen, $params['cashin']['id']);

        return $qrcode;
    }

    private static function _generateQR($str_generate, $cashin_id)
    {
        require_once LIBS . DS . 'phpqrcode' . DS . 'qrlib.php';
        $filepath = 'napas247/' . date('dmY', time());
        $file_name = 'NL' . $cashin_id . '.png';
        $logopath = ' https://upload.nganluong.vn/public/images/vietqr_ico.png';
        $image_address = $filepath . DS . $file_name;

        $path = str_replace(ROOT_PATH, '', $filepath);
        if (self::createDirPath(PATH_LOG . $path)) {
            touch($image_address);
        }
        QRcode::png($str_generate, $image_address, QR_ECLEVEL_H, 6);

        $QR = imagecreatefrompng($image_address);
        $logo = imagecreatefromstring(file_get_contents($logopath));

        /**
         *  Fix for the transparent background
         */
        imagecolortransparent($logo, imagecolorallocatealpha($logo, 0, 0, 0, 1));
        imagealphablending($logo, false);
        imagesavealpha($logo, true);

        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);

        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        // Scale logo to fit in the QR Code
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;

        imagecopyresampled($QR, $logo, $QR_width / 2.5, $QR_height / 2.6, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

        // Save QR code again, but with logo on it
        imagepng($QR, $image_address);

        return $image_address;


    }
}
