<?php


namespace common\partner_payments;

use common\models\business\CheckoutOrderBusiness;
use common\models\business\CheckoutOrderCallbackBusiness;
use common\models\db\Merchant;
use common\partner_payments\PartnerPaymentBasic;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\CyberSource;
use common\payments\CyberSourceVcb;
use common\models\db\Transaction;
use common\components\libs\Weblib;
use common\components\libs\Tables;
use common\components\utils\Translate;
use Yii;

class PartnerPaymentCyberSourceVcb extends PartnerPaymentBasic
{
    public function processRequest(PaymentMethodBasicForm &$form, $params) {
        $error_message = 'Lỗi không xác định';
        $result_code = '';
        $bank_trans_id = '';
        $xid = '';
        //------------
        $card_fullname = $this->_convertName($form->card_fullname);
        $merchant_info = Merchant::getById($form->checkout_order['merchant_id']);

        if (isset($merchant_info) && isset($merchant_info['email_requirement']) && $merchant_info['email_requirement'] == 0 &&  $form->checkout_order['buyer_email'] == "notrequired@nganluong.vn"){
            $buyer_email = 'null@cybersource.com';
        }else{
            $buyer_email = $form->checkout_order['buyer_email'];


        }
        $this->_processCardFullname($card_fullname, $first_name, $last_name);
        $inputs = array(
            'reference_code' => $GLOBALS['PREFIX'] . $params['transaction_id'],
            'city' => 'Ha Noi',
            'country' => 'Viet Nam',
            'email' => $buyer_email,
            'phone' => $form->checkout_order['buyer_mobile'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'postal_code' => '91356',
            'state' => '',
            'address' => $form->checkout_order['buyer_address'],
            'customer_id' => 0,
            'account_number' => $form->card_number,
            'card_type' => $form->card_type,
            'expiration_month' => $form->card_month,
            'expiration_year' => $form->card_year,
            'currency' => 'VND',
            'amount' => $params['transaction_amount'],
            'cvv_code' => $form->card_cvv,
            'product_code' => $GLOBALS['PREFIX'] . $params['transaction_id'],
            'client_ip' => @$_SERVER['REMOTE_ADDR'],
            'payment_url' => $form->_getUrlVerify($params['transaction_id']),
            'order_code' => $form->checkout_order['order_code'],

        );
        $cbs_stb = new CyberSourceVcb($form->checkout_order['merchant_id'], $form->partner_payment_id);
        if (!empty($cbs_stb->partner_payment_account_info)) {
            $result = $cbs_stb->authorizeCard($inputs);
            if (!empty($result['result'])) {
                if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100') {
                    $error_message = '';
                    $bank_trans_id = $result['result']->requestID;
                    $result_code = 'ACCEPT';
                } else {
                    if (CyberSourceVcb::checkVisaReview($result['result'])) {
                        $error_message = '';
                        $bank_trans_id = $result['result']->requestID;
                        $result_code = 'REVIEW';
                    } elseif (CyberSourceVcb::checkVisaReject($result['result'])) {
                        $error_message = CyberSourceVcb::getErrorMessage($result['result']->reasonCode);
                    } else {
                        $check3D = CyberSourceVcb::processVisa3D($result['result'],
                            CyberSourceVcb::getErrorMessage($result['result']->reasonCode),
                            $inputs);
                        if ($check3D['error_message'] == '') {
                            $xid = $check3D['xid'];
                            $error_message = '';
                            $result_code = '3D';
                        } else {
                            $error_message = $check3D['error_message'];
                        }
                    }
                }
            }
        } else {
            $error_message = 'Tài khoản kênh thanh toán không tồn tại';
        }
        return array('error_message' => $error_message, 'result_code' => $result_code, 'xid' => $xid, 'bank_trans_id' => $bank_trans_id, 'reasonCode' => $result['result']->reasonCode);
    }

    protected function _processCardFullname($fullname, &$first_name = '', &$last_name = '') {
        $fullname = trim($fullname);
        $pos = strrpos($fullname, ' ');
        if ($pos !== false) {
            $first_name = trim(substr($fullname, $pos));
            $last_name = trim(substr($fullname, 0, $pos));
        } else {
            $first_name = $fullname;
            $last_name = '';
        }
    }

    function initVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        $xid = ObjInput::get('xid', 'def', '');
        CyberSource::_writeLog('InitVerify[xid]'. $xid);
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            // Get token 3d info
            $token = Yii::$app->cache->get('TOKEN_3D_' . $xid);
            if (empty($token)) {
                $token = Yii::$app->session->get('TOKEN_3D_' . $xid);
            }

            $this->cyber_info = CyberSourceVcb::decryptSessionInfo($token);
            //Write log card
            $cache_log = $this->cyber_info;
            unset($cache_log['card_info']);
            CyberSource::_writeLog('InitVerify[data]'. json_encode($cache_log));
            $this->verify_url = Yii::$app->urlManager->createAbsoluteUrl(['call-back/cyber-source-vcb',
                'token_code' => $form->checkout_order['token_code'],
                'transaction_checksum' => $form->_getTransactionChecksum($form->payment_transaction['id']),
                'xid' => $xid], HTTP_CODE);
            if (!empty($this->cyber_info) && !empty($this->cyber_info['process_info']['reference_code']) &&
                ($this->cyber_info['process_info']['reference_code'] == $GLOBALS['PREFIX'] . $form->payment_transaction['id'])) {
                if (!empty($this->cyber_info['response_info']['paRes'])) {
                    $this->processVerify($form, $this->cyber_info);
                } else {
                    $form->error_message = '';
                }
            } else {
                $form->redirectErrorPage('Phiên thanh toán hết hạn. Vui lòng tạo đơn hàng mới');
            }
        } else {
            $form->redirectErrorPage('Giao dịch không hợp lệ');
        }
        return true;
    }

    function processVerify(PaymentMethodBasicForm &$form, $data) {

        $xid = ObjInput::get('xid', 'def', '');
        $error_message = 'SYSTEM_ERROR';
        $result_code = '';
        $bank_trans_id = '';
        //------------
        $inputs = array(
            'reference_code' => $data['process_info']['reference_code'],
            'city' => $data['card_info']['city'],
            'country' => $data['card_info']['country'],
            'email' => $data['card_info']['email'],
            'first_name' => $data['card_info']['first_name'],
            'last_name' => $data['card_info']['last_name'],
            'postal_code' => $data['card_info']['postal_code'],
            'state' => $data['card_info']['state'],
            'address' => $data['card_info']['address'],
            'phone' => $data['card_info']['phone'],
            'client_ip' => @$_SERVER['REMOTE_ADDR'],
            'account_number' => $data['card_info']['account_number'],
            'card_type' => $data['card_info']['card_type'],
            'expiration_month' => $data['card_info']['expiration_month'],
            'expiration_year' => $data['card_info']['expiration_year'],
            'cvv_code' => $data['card_info']['cvv_code'],
            'currency' => $data['process_info']['currency'],
            'amount' => $data['process_info']['amount'],
            'signedPARes' => $data['response_info']['paRes'],
            'product_code' => $data['card_info']['product_code'],
            'order_code' => $form->checkout_order['order_code'],
        );
        $inputs_logs = $inputs;
        if (isset($inputs['account_number'])){
            $inputs_logs['account_number_mask'] = self::_replaceCardNumber($inputs['account_number']);
            unset($inputs_logs['account_number']);

        }


        CyberSource::_writeLog('ProcessVerify[input]'. json_encode($inputs_logs));
        $cbs_stb = new CyberSourceVcb($form->checkout_order['merchant_id'], $form->partner_payment_id);
        $result = $cbs_stb->authorizeCard3Dsecure($inputs);
        CyberSource::_writeLog('ProcessVerify[result_3d]'. json_encode($result));
        $eciRaw = @$result['result']->payerAuthValidateReply->eciRaw;
        $error_message = '';
        $bank_trans_id = $result['result']->requestID;
        if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100' && !empty($eciRaw) && in_array($eciRaw, array('02', '05', '01', '06'))) {
                $result_code = 'ACCEPT';
                Yii::$app->cache->delete('TOKEN_3D_' . $xid);
                Yii::$app->session->remove('TOKEN_3D_' . $xid);

                $inputs = array(
                    'transaction_id' => $form->payment_transaction['id'],
                    'time_paid' => time(),
                    'bank_refer_code' => $bank_trans_id,
                    'user_id' => 0,
                );
                $result = TransactionBusiness::paid($inputs);
                if ($result['error_message'] === '') {
                    $error_message = '';
                    $payment_url = $form->_getUrlSuccess($form->payment_transaction['id']);
                    header('Location:' . $payment_url);
                    die();
                } else {
                    $error_message = $result['error_message'];
                    $form->redirectErrorPage($error_message);
                }
        } else {
            if (CyberSourceVcb::checkVisaReview($result['result'])) {
                $error_message = '';
                $bank_trans_id = $result['result']->requestID;
                $result_code = 'REVIEW';
                $inputs = array(
                    'transaction_id' => $form->payment_transaction['id'],
                    'time_paid' => time(),
                    'bank_refer_code' => $bank_trans_id,
                    'user_id' => 0,
                );
                $result = TransactionBusiness::updateReview($inputs);
                if ($result['error_message'] === '') {
                    $error_message = '';
                    $payment_url = $form->_getUrlReview($form->payment_transaction['id']);
                    header('Location:' . $payment_url);
                    die();
                } else {
                    $error_message = $result['error_message'];
                }
            } elseif (CyberSourceVcb::checkVisaReject($result['result'])) {
                $inputs = array(
                    'checkout_order_id' => $form->checkout_order['id'],
                    'user_id' => '0',
                );
                $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs, false);
                if ($result['error_message'] === '') {
                    $inputs_callback = [
                        'checkout_order_id' => $form->checkout_order['id'],
                        'notify_url' => $form->checkout_order['notify_url'],
                        'time_process' => time(),
                    ];
                    if ($form->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
                        $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                    }
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $payment_url = $this->_getUrlFailure($form->checkout_order['token_code']);
                        header('Location:' . $payment_url);
                        die();
                    } else {
                        $error_message = $result['error_message'];
                        $form->redirectErrorPage($error_message);
                    }
                } else {
                    $error_message = Translate::get($result['error_message']);
                    $form->redirectErrorPage($error_message);

                }
            } else {
                if (isset($form->checkout_order['cancel_url']) && !empty($form->checkout_order['cancel_url'])){
                    $model = Transaction::findOne(["id" => $form->payment_transaction['id']]);
                    $dataError = json_decode($model['partner_payment_info'],true);
                    $dataError['error_message'] = $result['error'];

                    $inputs = array(
                        'transaction_id' =>$form->payment_transaction['id'],
                        'reason_id' => @$result['result']->reasonCode,
                        'reason' => $dataError['error_message'],
                        'user_id' => 0,
                    );
                    TransactionBusiness::cancel($inputs);
                    $inputs_failure = array(
                        'checkout_order_id' => $form->checkout_order['id'],
                        'user_id' => '0',
                    );
                    $result = CheckoutOrderBusiness::updateCheckoutOrderStatusFailure($inputs_failure, false);
                    if ($result['error_message'] === '') {
                        $inputs_callback = [
                            'checkout_order_id' => $form->checkout_order['id'],
                            'notify_url' => $form->checkout_order['notify_url'],
                            'time_process' => time(),
                        ];
                        if ($form->checkout_order["merchant_info"]['order_status'] == Merchant::CALLBACK_FAILURE_STATUS_ENABLE) {
                            $result = CheckoutOrderCallbackBusiness::addFailure($inputs_callback, false);
                        }
                        if ($result['error_message'] == '') {
                            $error_message = '';
                            $payment_url = $this->_getUrlFailure($form->checkout_order['token_code']);
                            header('Location:' . $payment_url);
                            die();
                        } else {
                            $error_message = $result['error_message'];
                            $form->redirectErrorPage($error_message);
                        }
                    } else {
                        $error_message = Translate::get($result['error_message']);
                        $form->redirectErrorPage($error_message);

                    }


                }else{
                    $form->redirectErrorPage(CyberSourceVcb::getErrorMessage($result['result']->reasonCode));

                }


            }
        }
    }

    private function _convertName($content) {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    public function getTransaction($trans_id){
        $result = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $trans_id]);

        return $result;
    }

    function confirmVerify(PaymentMethodBasicForm &$form) {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');

        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            $recept_code = $form->payment_transaction['partner_payment_info']['result_code'];

            if (intval($form->payment_transaction['status']) == Transaction::STATUS_PAYING ||intval($form->payment_transaction['status']) == Transaction::STATUS_NEW) {
                $inputs = array(
                    'transaction_id' => $form->payment_transaction['id'],
                    'transaction_type_id' => 1,
                    'bank_refer_code' => $form->payment_transaction['partner_payment_info']['bank_trans_id'],
                    'time_paid' => time(),
                    'user_id' => 0,
                    'payment_info' => json_encode($form->payment_transaction),
                );
                if ($recept_code == 'REVIEW') {
                    $result = TransactionBusiness::reviewCyberSource($inputs);
                } elseif ($recept_code == 'ACCEPT') {
                    $result = TransactionBusiness::paid($inputs);
                }

                if (isset($result) && $result['error_message'] === '') {
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
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }

    public function _getUrlFailure($token_code)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/failure', 'token_code' => $token_code], HTTP_CODE);
    }
    private static function _replaceCardNumber($cardNumber) {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }
}