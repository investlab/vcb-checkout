<?php


namespace common\partner_payments;

use common\partner_payments\PartnerPaymentBasic;
use common\payment_methods\PaymentMethodBasicForm;
use common\components\utils\ObjInput;
use common\models\business\TransactionBusiness;
use common\payments\CyberSource;
use common\models\db\Transaction;
use common\components\libs\Weblib;
use common\components\libs\Tables;
use common\components\utils\Translate;
use Yii;

class PartnerPaymentCyberSource extends PartnerPaymentBasic
{

    public function initRequest(PaymentMethodBasicForm &$form) {
        $connect_cbs_flex_error = 'Thông tin kết nối không hợp lệ';
        $fields = ['BANK_ACCOUNT', 'BANK_NAME', 'EXPIRED_MONTH', 'EXPIRED_YEAR', 'CARD_CVV'];
        $cbs_stb = new CyberSource($form->checkout_order['merchant_id'], $form->partner_payment_id);
        $is_null = false;
        foreach ($cbs_stb as $key => $val) {
            if (empty($val)) {
                $is_null = true;
            }
        }
        if ($is_null) {
            return array('error_message' => $connect_cbs_flex_error );
        }

        $flex_key = $cbs_stb->getFlexKey();
        if (empty($flex_key)) {
            return array('error_message' => $connect_cbs_flex_error );
        }else{
            $form->checkout_order['flex_key'] = $flex_key;

            return $fields;
        }
    }

    public function processRequest(PaymentMethodBasicForm &$form, $params) {
        $error_message = 'Lỗi không xác định';
        $result_code = '';
        $bank_trans_id = '';
        $xid = '';
        $params = [
            'fullname' => strtoupper($form->card_fullname),
            'token' => $params['flex_response']['token'],
            'cashin_id' => $form->checkout_order['id'],
            'phone' => $form->checkout_order['buyer_mobile'],
            'email' => $form->checkout_order['buyer_email'],
            'cashin_amount' => $form->checkout_order['amount'],
            'expiration_month' => $form->card_month,
            'expiration_year' => $form->card_year,
            'card_type' => $params['flex_response']['cardType'],
            'account_number' => $params['flex_response']['maskedPan'],
            'payment_url' => $form->_getUrlVerify($params['transaction_id'])
        ];
        $token = $params['token'];
        //------------
        $card_fullname = $this->_convertName($params['fullname']);
        $this->_processCardFullname($card_fullname, $first_name, $last_name);
        $params['first_name'] = $first_name;
        $params['last_name'] = $last_name;
        $cbs_stb = new CyberSource($form->checkout_order['merchant_id'], $form->partner_payment_id);
        $cbs_stb->updateCustomerInfo($params);
        $inputs_authorize = $params;
        $result = $cbs_stb->authorizeSubcription($inputs_authorize);
        if ($result['result']->decision == 'ACCEPT' && $result['result']->reasonCode == '100') {
            //check thẻ 2D
            $error_message = '';
            $bank_trans_id = $result['result']->requestID;
            $result_code = 'ACCEPT';
//            CyberSource::updateTokenInfo($token, $params['cashin_id'], '2D', $GLOBALS['TOKEN_STATUS']['PAYMENT_SUCCESS']);
        } else {
            if (CyberSource::checkVisaReview($result['result'])) {
                $error_message = '';
                $bank_trans_id = $result['result']->requestID;
                $result_code = 'REVIEW';
//                    CyberSource::updateTokenInfo($token, $params['cashin_id'], '2D');
            } elseif (CyberSource::checkVisaReject($result['result'])) {
                $error_message = CyberSource::getErrorMessage($result['result']->reasonCode);
                $cbs_stb->cancelAuthorizeCard(array('token' => $token));
                $result_code = 'REJECT';
            } else {
                $params['option_cache'] = 0;
                $check3D = CyberSource::processVisa3D($result['result'], CyberSource::getErrorMessage($result['result']->reasonCode), $params);
                if ($check3D['error_message'] == '') {
                    $xid = $check3D['xid'];
                    $error_message = '';
                    $result_code = '3D';
                } else {
                    $error_message = $check3D['error_message'];
                    $cbs_stb->cancelAuthorizeCard(array('token' => $token));
                }
            }
        }
        return array('error_message' => $error_message, 'result_code' => $result_code, 'xid' => $xid, 'bank_trans_id' => $bank_trans_id, 'payment_url' => $params['payment_url']);
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
        if ($form->_getTransactionChecksum($form->payment_transaction['id']) == $transaction_checksum) {
            $token = Yii::$app->cache->get('TOKEN_3D_'.$form->payment_transaction['partner_payment_info']['xid']);
            $this->cyber_info = CyberSource::decryptSessionInfo($token);
            $this->verify_url = $form->_getUrlCallback('cyber_source');

            if ($this->_checkSessionVerifyCard()) {
                if (isset($this->form->data['cache'])) {
                    if ($this->form->data['cache']['response_info']['paRes'] !== '') {
                        $result = $this->processVerify($transaction_type);
                        if ($result['error_message'] == '') {
                            self::confirmVerify($form);
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = '';
                    }
                }
            } else {
                self::confirmVerify($form);
            }
            return true;
        } else {
            $form->error_message = 'Giao dịch không hợp lệ';
        }
        return false;
    }


    private function _convertName($content) {
        $utf82abc = array('à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e', 'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i', 'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o', 'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u', 'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y', 'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U', 'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', '̀' => '', '́' => '', '̉' => '', '̃' => '', '̣' => '');
        return str_replace(array_keys($utf82abc), array_values($utf82abc), $content);
    }

    protected function _checkSessionVerifyCard() {
        $xid = ObjInput::get('xid', 'def', '');
        $cache_verify = Yii::$app->cache->get('TOKEN_3D_' . $xid);
        if (!empty($cache_verify)) {
            $this->form->data['cache'] = CyberSource::decryptSessionInfo($cache_verify);
            if (isset($this->form->data['cache']['process_info']['cashin_id'])) {
                return true;
            } else {
                return false;
            }
        }
        return false;
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
}