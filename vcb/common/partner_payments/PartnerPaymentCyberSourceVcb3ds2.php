<?php


namespace common\partner_payments;

use common\components\libs\NotifySystem;
use common\models\business\CardDeclineBusiness;
use common\models\business\CyberSourceTransactionBusiness;
use common\models\db\Merchant;
use common\partner_payments\PartnerPaymentBasic;
use common\payment_methods\cyber_source_vcb_3ds2\PaymentMethodCreditCardCyberSourceVcb3ds2;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\CyberSource;
use common\payments\CyberSourceVcb;
use common\models\db\Transaction;
use common\components\libs\Weblib;
use common\components\libs\Tables;
use common\components\utils\Translate;
use common\payments\CyberSourceVcb3ds2;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class PartnerPaymentCyberSourceVcb3ds2 extends PartnerPaymentBasic
{
    private $cyberSourceVcb3ds2;
    const ECI_SUCCESS = ['01', '02', '05', '06'];

    /**
     * @throws \SoapFault
     * @throws Exception
     */
    public function processRequest(PaymentMethodBasicForm &$form, $params)
    {

        $error_message = 'Lỗi không xác định';
        $result_code = '';
        $bank_trans_id = '';
        $authorizationCode = '';
        $token_cybersource = '';
        $xid = '';
        //------------
        $data = Yii::$app->request->post();
        if (isset($form->card_first_name) && isset($form->card_last_name)) {
            $card_fullname = $form->card_first_name . ' ' . $form->card_last_name;
            $this->_processCardFullname($card_fullname, $first_name, $last_name);
        } else {
            if ($form->card_fullname == null || $form->card_fullname == "") {
                $form->card_fullname = $form->checkout_order['buyer_fullname'];
            }
            $this->_processCardFullname($form->card_fullname, $first_name, $last_name);
        }
        $merchant_info = Merchant::getById($form->checkout_order['merchant_id']);
        if (isset($merchant_info) && isset($merchant_info['email_requirement']) && $merchant_info['email_requirement'] == 0 && $form->checkout_order['buyer_email'] == "notrequired@nganluong.vn") {
            $buyer_email = 'null@cybersource.com';
        } else {
            $buyer_email = $form->checkout_order['buyer_email'];
        }


        if ($form->country != null && in_array($form->country, ['US', "CA"])) {
            $postal_code = $form->zip_or_portal_code != null ? $form->zip_or_portal_code : '91356';
            $state = $form->state != null ? $form->state : '';
        } else {
            $postal_code = "";
            $state = "";
        }

        $inputs = array(
            'reference_code' => $GLOBALS['PREFIX'] . $params['transaction_id'],
            'city' => $form->city != null ? $form->city : 'Ha Noi',
            'country' => $form->country != null ? $form->country : 'Viet Nam',
            'email' => $buyer_email,
            'phone' => $form->checkout_order['buyer_mobile'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'postal_code' => $postal_code,
            'state' => $state,
            'address' => $form->billing_address != null ? $form->billing_address : $form->checkout_order['buyer_address'],
            'customer_id' => 0,
            'account_number' => $form->card_number,
            'card_type' => isset($form->card_type) ? $form->card_type : strtolower($data['card_type']),
            'expiration_month' => $form->card_month,
            'expiration_year' => $form->card_year,
            'currency' => 'VND',
            'amount' => isset($data['amount_fee']) ? $data['amount_fee'] : $params['transaction_amount'],
            'cvv_code' => $form->card_cvv,
            'product_code' => $GLOBALS['PREFIX'] . $params['transaction_id'],
            'client_ip' => @$_SERVER['REMOTE_ADDR'],
            'payment_url' => $form->_getUrlVerify($params['transaction_id']),
            'order_code' => $form->checkout_order['order_code'],
            'ProcessorTransactionId' => isset($params['ProcessorTransactionId']) ? $params['ProcessorTransactionId'] : '',
//            'referenceID' => isset($params['referenceID']) ? $params['referenceID'] : '',
            'ignore_avs' => in_array($form->checkout_order['merchant_id'], ['91', '168', '78', '192']),
//            'run_enrollment' => in_array($form->checkout_order['merchant_id'], ['78', '91', '168']), // Dai-ichi
            "buyer_address" => $form->checkout_order['buyer_address'],
            "referenceID" => isset($form->sessionId) ? $form->sessionId : '',

        );
//        Thêm trường reconciliationID theo yêu cầu của Daiichi ngày 15/06
        if ($form->checkout_order['merchant_id'] == '7') {
            $inputs['reconciliationID'] = $form->checkout_order['order_code'];
        }

        if ($inputs['ProcessorTransactionId']) {
            $cbs_3ds2 = new CyberSourceVcb3ds2($form->checkout_order);
            if ($form->checkout_order['link_card']) {
                if ($form->checkout_order['customer_field'] != "") {
                    $customer_field = json_decode($form->checkout_order['customer_field'], true);
                    if ($customer_field != null) {
                        $list_customer_field = CyberSourceVcb3ds2::getCustomerField($customer_field);
                        if (isset($list_customer_field['merchant_define_data'])) {
                            foreach ($list_customer_field['merchant_define_data'] as $key => $value) {
                                $inputs['merchantDefinedData']['field' . $key] = $value;
                            }
                        }
                    }
                }
                $result = $cbs_3ds2->authorizeCardAndCreateToken($inputs);
            } else {
                $result = $cbs_3ds2->authorizeCard($inputs);
            }



            if (isset($result['result']->payerAuthEnrollReply->eci)) {
                $eci = $result['result']->payerAuthEnrollReply->eci;
            } elseif (isset($result['result']->payerAuthEnrollReply->eciRaw)) {
                $eci = $result['result']->payerAuthEnrollReply->eciRaw;
            }
            @CyberSourceTransactionBusiness::add3DsInfo([
                'transaction_id' => $params['transaction_id'],
                'data' => $result['result']
            ]);

//            $result['result']->invalidField = "c:billTo/c:state";
            if (!empty($result['result'])) {
                if ($cbs_3ds2::isSuccess($result['result'])) {
                    $error_message = '';
                    $bank_trans_id = $result['result']->requestID;
                    $authorizationCode = @$result['result']->ccAuthReply->authorizationCode;
                    $result_code = 'ACCEPT';
                    if ($form->checkout_order['link_card']) {
                        $token_cybersource = $result['result']->paySubscriptionCreateReply->subscriptionID;
                    }
                } else {
                    if ($cbs_3ds2::isReview($result['result'])) {
                        $error_message = '';
                        $bank_trans_id = $result['result']->requestID;
                        $result_code = 'REVIEW';
                    } elseif ($cbs_3ds2::isReject($result['result'])) {
                        $result_code = 'REJECT';
                        $bank_trans_id = $result['result']->requestID;
                        $error_message = CyberSourceVcb3ds2::getErrorMessage($result['result']->reasonCode);
                        if (isset($result['result']->invalidField) && $result['result']->invalidField != "") {
                            $invalid_filed = CyberSourceVcb3ds2::getInvalidField($result['result']);
                            $error_message .= " Field Invalid: ";
                            foreach ($invalid_filed as $item) {
                                $error_message .= $item . " ";
                            }
                        }
//                      Ghi thẻ lỗi vào bảng card_decline
                        @CardDeclineBusiness::addDeclineResponse($result['result'], $inputs, $params['transaction_id']);
                    } else {
                        $error_message = CyberSourceVcb3ds2::getErrorMessage($result['result']->reasonCode);
                    }
                }
            }
        } else {
            $error_message = "Thẻ bị từ chối";
            $result_code = "REJECT";
        }


        return [
            'error_message' => $error_message,
            'result_code' => $result_code,
            'xid' => $xid,
            'bank_trans_id' => $bank_trans_id,
            'authorizationCode' => $authorizationCode,
            'reasonCode' => isset($result) && isset($result['result']->reasonCode) ? $result['result']->reasonCode : '',
            'token_info' => [
                'token_cybersource' => $token_cybersource
            ]
        ];
    }

    protected function _processCardFullname($fullname, &$first_name = '', &$last_name = '')
    {
        $fullname = trim($fullname);
        $pos = strrpos($fullname, ' ');
        if ($pos !== false) {
            $first_name = trim(substr($fullname, $pos));
            $last_name = trim(substr($fullname, 0, $pos));
        } else {
            $first_name = $fullname;
            $last_name = 'A';
        }
    }

    function cardType($card_info)
    {
        $card_types = array(
            '001' => 'visa',
            '007' => 'jcb',
            '002' => 'mastercard',
            '003' => 'amex',
        );
        return $card_types[$card_info];
    }

    function initVerify(PaymentMethodBasicForm &$form)
    {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');
        $xid = ObjInput::get('xid', 'def', '');
        CyberSource::_writeLog('InitVerify[xid]' . $xid);
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
            CyberSource::_writeLog('InitVerify[data]' . json_encode($cache_log));
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

    function processVerify(PaymentMethodBasicForm &$form, $data)
    {

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
        if (isset($inputs['account_number'])) {
            $inputs_logs['account_number_mask'] = self::_replaceCardNumber($inputs['account_number']);
            unset($inputs_logs['account_number']);

        }
        CyberSource::_writeLog('ProcessVerify[input]' . json_encode($inputs_logs));
        $cbs_stb = new CyberSourceVcb($form->checkout_order['merchant_id'], $form->partner_payment_id);
        $result = $cbs_stb->authorizeCard3Dsecure($inputs);
        CyberSource::_writeLog('ProcessVerify[result_3d]' . json_encode($result));
        if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100') {
            $eciRaw = @$result['result']->payerAuthValidateReply->eciRaw;
            $error_message = '';
            $bank_trans_id = $result['result']->requestID;
            if (!empty($eciRaw) && in_array($eciRaw, array('02', '05', '01', '06'))) { // success
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
                $result_code = 'REJECT';
                $error_message = 'Không kiểm tra được thẻ, có thể bạn chưa đăng ký chức năng giao dịch qua Internet, vui lòng liên hệ ngân hàng phát hành thẻ để trợ giúp';
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
                $form->redirectErrorPage(CyberSourceVcb::getErrorMessage($result['result']->reasonCode));
            } else {
                if (isset($form->checkout_order['cancel_url']) && !empty($form->checkout_order['cancel_url'])) {
                    header('Location:' . $form->checkout_order['cancel_url']);
                    die();

                } else {
                    $form->redirectErrorPage(CyberSourceVcb::getErrorMessage($result['result']->reasonCode));

                }


            }
        }
    }

    private function _convertName($content)
    {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    public function getTransaction($trans_id)
    {
        $result = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $trans_id]);

        return $result;
    }

    function confirmVerify(PaymentMethodBasicForm &$form)
    {
        $transaction_checksum = ObjInput::get('transaction_checksum', 'str', '');

        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            $recept_code = $form->payment_transaction['partner_payment_info']['result_code'];

            if (intval($form->payment_transaction['status']) == Transaction::STATUS_PAYING || intval($form->payment_transaction['status']) == Transaction::STATUS_NEW) {
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

    private static function _replaceCardNumber($cardNumber)
    {
        return substr($cardNumber, 0, 4) . '.' . substr($cardNumber, 4, 2) . 'xx.xxxx.' . substr($cardNumber, -4);
    }

    public function getCardAccept(PaymentMethodBasicForm &$form, $params, $method_code)
    {

        $installment = Tables::selectAllDataTable('installment_config', 'merchant_id=' . $params['merchant_id'] . ' AND status=' . ACTIVE_STATUS);
        $item_config = false;
        if (!empty($installment)) {
            $installment_card = json_decode($installment[0]['card_accept'], true);
            $installment_cycle = json_decode($installment[0]['cycle_accept'], true);
            $installment_fee_bearer = $installment[0]['fee_bearer'];
            foreach ($installment_card as $key_card => $card) {
                foreach ($installment_cycle as $key_cycle => $cycle) {
                    if ($key_card == $method_code && $key_cycle == $method_code) {
                        $item_config[] = [
                            'card' => $card,
                            'cycle' => $cycle,
                            'fee_bearer' => $installment_fee_bearer
                        ];
                    }
                }
            }
            if ($item_config == false) {
                $item_config['error_message'] = 'Ngân hàng chưa được hỗ trợ trả góp. Vui lòng chọn lại ngân hàng!';
            }
        }
        return $item_config;
    }

    public function getCardInfo()
    {
        $data = Yii::$app->request->post();
        return $data;
    }
}