<?php

namespace common\api;

use common\components\libs\Tables;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\models\db\PartnerPaymentFee;
use common\models\db\PartnerPaymentMethod;
use common\models\db\PaymentMethod;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\form\CheckoutOrderWaitRefundForm;
use Yii;
use common\models\db\Merchant;
use common\components\utils\Validation;
use common\models\business\CheckoutOrderBusiness;
use common\models\db\CheckoutOrder;
use common\components\utils\ObjInput;

/**
 * @author Administrator
 * @version 1.0
 * @created 02-Nov-2016 10:14:08 AM
 */
class RefundVersion1_0StaticApi extends RefundBasicApi
{

    public function getVersion()
    {
        return ObjInput::get('version', 'str', '1.0');
    }

    protected function _isFunction($function)
    {
        return ($function == 'setRefundRequest' || $function == 'checkRefund' || $function == 'setRefundVcbAtm' || $function == 'setRefundVcbAtmV2');
    }

    public function getData($function)
    {
        if ($function == 'setRefundRequest') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str');
            $data['checksum'] = ObjInput::get('checksum', 'str');
            $data['merchant_email'] = ObjInput::get('merchant_email', 'str');
            $data['ref_code_refund'] = ObjInput::get('ref_code_refund', 'str');
            $data['amount'] = ObjInput::get('amount', 'str');
            $data['refund_type'] = ObjInput::get('refund_type', 'str');
            $data['reason'] = ObjInput::get('reason', 'str');
            $data['callback'] = ObjInput::get('callback', 'str');
            $data['token_code'] = ObjInput::get('token_code', 'str');


            return $data;
        } elseif ($function == 'checkRefund') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'int', 0);
            $data['checksum'] = ObjInput::get('checksum', 'str', '');
            $data['merchant_email'] = ObjInput::get('merchant_email', 'str', '');
            $data['func'] = ObjInput::get('func', 'str', '');
            $data['transaction_refund_id'] = ObjInput::get('transaction_refund_id', 'str', '');
            $data['ref_code_refund'] = ObjInput::get('ref_code_refund', 'str', '');
            return $data;
        } elseif ($function == 'setRefundVcbAtm') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str');
            $data['checksum'] = ObjInput::get('checksum', 'str');
            $data['token_code'] = ObjInput::get('token_code', 'str');
            $data['amount'] = ObjInput::get('amount', 'str');
            $data['reason'] = ObjInput::get('reason', 'str');
            return $data;
        } elseif ($function == 'setRefundVcbAtmV2') {
            $data['function'] = $function;
            $data['merchant_site_code'] = ObjInput::get('merchant_site_code', 'str');
            $data['checksum'] = ObjInput::get('checksum', 'str');
            $data['token_code'] = ObjInput::get('token_code', 'str');
            $data['amount'] = ObjInput::get('amount', 'str');
            $data['reason'] = ObjInput::get('reason', 'str');
            return $data;
        }
        return false;
    }

    public function getResultMessage($result_code)
    {
        $message = array(
            '0000' => 'Thành công',
            '0001' => 'Lỗi không xác định',
            '0002' => 'Tên hàm không hợp lệ',
            '0003' => 'Mã merchant_site_code không hợp lệ hoặc không tồn tại',
            '0004' => 'Số tiền không hợp lệ',
            '0005' => 'Mã check_sum không đúng',
            '0006' => 'Mã token_code không hợp lệ',
            '0007' => 'Mã yêu cầu hoàn ref_code_refund không hợp lệ',
            '0008' => 'Mã checksum không chính xác',
            '0009' => 'merchant_email không hợp lệ hoặc không tồn tại',
            '0010' => 'Mã merchant_site_code không thuộc merchant_email',
            '0011' => 'Số tiền hoàn vượt quá số tiền thanh toán',
            '0012' => 'Loại giao dịch hoàn không hợp lệ',
            '0013' => 'Mã trans_id không hợp lệ',
            '0014' => 'Mã giao dịch yêu cầu refund không hợp lệ',
            '0015' => 'Giao dịch không hợp lệ',
            '0016' => 'Kênh thanh toán không được hỗ trợ'
        );
        return array_key_exists($result_code, $message) ? $message[$result_code] : $message['0001'];
    }


    /**
     *
     * @param type $api_key
     * @param type $data : merchant_site_code, order_code, order_description, amount, currency, buyer_fullname, buyer_email, buyer_mobile, buyer_address, return_url, cancel_url, notify_url, time_limit, language, checksum
     */
    protected function _validateDataSetRefundRequest($data)
    {

        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($this->_CheckRequireDataSetRefund($data)) {

            if ($api_key !== false) {
//            if (empty($data['me']))

                if (!empty($data['merchant_email']) || true) {
                    if (Validation::isEmail($data['merchant_email']) || true) {
                        if ($this->_validateReceiverEmail($data['merchant_email'], $data['merchant_site_code']) || true) {
                            if ($this->_validateChecksumSetRefundRequest($data, $api_key)) {
                                if ($this->_validateTokenCode($data['token_code'], $checkout_order_info)) {
                                    if ($this->_validateRefundCheckoutAmount($data['amount'], $checkout_order_info['amount'])) {
                                        if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                                            $data['checkout_order_info'] = $checkout_order_info;
                                            if ($this->_validateAmount($data['amount'])) {
                                                if ($this->_validateRefRefundCode($data['ref_code_refund'])) {
                                                    if ($this->_validateAmountRefund($data['refund_type'], $data['amount'], $checkout_order_info['amount'])) {
                                                        if ($this->_validateReundType($data['refund_type'])) {
                                                            $error_code = '0000';

                                                        } else {
                                                            $error_code = '0012';
                                                        }

                                                    } else {
                                                        $error_code = '0004';
                                                    }
                                                } else {
                                                    $error_code = '0007';
                                                }
                                            } else {
                                                $error_code = '0004';

                                            }
                                        } else {
                                            $error_code = '0003';
                                        }
                                    } else {
                                        $error_code = '0011';

                                    }

                                } else {
                                    $error_code = '0006';
                                }


                            } else {
                                $error_code = '0008';
                            }
                        } else {
                            $error_code = '0010';
                        }
                    } else {
                        $error_code = '0009';
                    }


                } else {
                    $error_code = '0009';
                }

            } else {
                $error_code = '0003';
            }
        } else {
            $error_code = '0001';
        }

        return array('error_code' => $error_code);

    }

    protected function _validateDataSetRefundVcbAtm($data)
    {
        $status = [CheckoutOrder::STATUS_PAID => CheckoutOrder::STATUS_PAID, CheckoutOrder::STATUS_REFUND_PARTIAL => CheckoutOrder::STATUS_REFUND_PARTIAL];
//        $checkout_info = Tables::selectOneDataTable("checkout_order", ["token_code = :id AND ", "id" => $data['token_code']]);
        $checkout_info = Tables::selectOneBySql("SELECT * FROM checkout_order where token_code = '" . $data['token_code'] . "' AND status IN (" . implode(',', $status) . ")");
        $error_code = '0001';
        if ($this->_validateTokenCode($data['token_code'], $checkout_order_info)) {
            if ($checkout_info) {
                $transaction = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $checkout_info['transaction_id']]);
                $payment_transaction = Transaction::setRow($transaction);
                $payment_method_code = $payment_transaction['payment_method_info']['code'];
                $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                    "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                    "payment_method_code" => $payment_method_code,
                    "transaction_type_id" => TransactionType::getPaymentTransactionTypeId(),
                    "status" => PaymentMethod::STATUS_ACTIVE
                ]);
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                    if ($refund_partner_payment_info['partner_payment_code'] == 'VCB') {
                        if ($this->_validateAmount($data['amount'])) {
                            if ($data['amount'] <= $checkout_info['amount']) {
                                $error_code = '0000';
                            } else {
                                $error_code = '0011';
                            }
                        } else {
                            $error_code = '0004';
                        }
                    } else {
                        $error_code = '0016';
                    }
                } else {
                    $error_code = '0003';
                }
            } else {
                $error_code = '0006';
            }
        } else {
            $error_code = '0006';
        }
        return array('error_code' => $error_code);
    }

    protected function _validateDataSetRefundVcbAtmV2($data)
    {
        $status = [
            CheckoutOrder::STATUS_PAID => CheckoutOrder::STATUS_PAID,
            CheckoutOrder::STATUS_REFUND_PARTIAL => CheckoutOrder::STATUS_REFUND_PARTIAL,
            CheckoutOrder::STATUS_WAIT_REFUND => CheckoutOrder::STATUS_WAIT_REFUND
        ];
//        $checkout_info = Tables::selectOneDataTable("checkout_order", ["token_code = :id AND ", "id" => $data['token_code']]);
        $checkout_info = Tables::selectOneBySql("SELECT * FROM checkout_order where token_code = '" . $data['token_code'] . "' AND status IN (" . implode(',', $status) . ")");
        $error_code = '0001';
        if ($this->_validateTokenCode($data['token_code'], $checkout_order_info)) {
            if ($checkout_info) {
                $transaction = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $checkout_info['transaction_id']]);
                $payment_transaction = Transaction::setRow($transaction);
                $payment_method_code = $payment_transaction['payment_method_info']['code'];
                $refund_payment_method_info = Tables::selectOneDataTable("payment_method", [
                    "code = :payment_method_code AND transaction_type_id = :transaction_type_id AND status = :status ",
                    "payment_method_code" => $payment_method_code,
                    "transaction_type_id" => TransactionType::getPaymentTransactionTypeId(),
                    "status" => PaymentMethod::STATUS_ACTIVE
                ]);
                $refund_partner_payment_info = PartnerPaymentMethod::getByPaymentMethodId($refund_payment_method_info['id']);
                if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                    if ($refund_partner_payment_info['partner_payment_code'] == 'VCB') {
                        if ($this->_validateAmount($data['amount'])) {
                            if ($data['amount'] <= $checkout_info['amount']) {
                                $error_code = '0000';
                            } else {
                                $error_code = '0011';
                            }
                        } else {
                            $error_code = '0004';
                        }
                    } else {
                        $error_code = '0016';
                    }
                } else {
                    $error_code = '0003';
                }
            } else {
                $error_code = '0006';
            }
        } else {
            $error_code = '0006';
        }
        return array('error_code' => $error_code);
    }

    protected function _setRefundVcbAtm($param)
    {
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["token_code = :id", "id" => $param['token_code']]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            return array('error_code' => '0006');
        }
        if ($param['amount'] < $checkout_order_info['amount']) {
            $model->refund_type = $GLOBALS['REFUND_TYPE']['PARTIAL'];
        } else {
            $model->refund_type = $GLOBALS['REFUND_TYPE']['TOTAL'];
        }
        $model->order_id = $checkout_order['id'];
        $model->refund_amount = $param['amount'];
        $model->refund_reason = $param['reason'];
        $refund_amount = $model->refund_amount;
        $refund_reason = empty($model->refund_reason) ? '' : $model->refund_reason;
        $result_refund = CheckoutOrderBusiness::processRequestRefundVcb([
            'checkout_order' => $checkout_order,
            'refund_type' => $model->refund_type,
            'refund_amount' => $refund_amount,
            'refund_reason' => $refund_reason,
            'user_id' => '0',
            'transaction_id' => $checkout_order_info['transaction_id'],
        ]);
        if ($result_refund['error_message'] === 'Hoàn tiền thành công') {
            $error_code = '0000';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['SUCCESS'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        } elseif ($result_refund['error_message'] == 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý') {
            $error_code = '0000';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));
        } elseif ($result_refund['error_message'] == 'Số tiền hoàn lại không hợp lệ') {
            $error_code = '0004';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        } else {
            $error_code = '0001';
            $error_message = 'Tạo yêu cầu hoàn tiền thất bại ' . $result_refund['error_message'];
            $result_data = array(
//                'ref_code_refund' => $param['ref_code_refund'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['FAIL'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        }
        return array('error_code' => $error_code, 'error_message' => $error_message, 'result_data' => $result_data);
    }

    protected function _setRefundVcbAtmV2($param)
    {
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["token_code = :id", "id" => $param['token_code']]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            return array('error_code' => '0006');
        }
        if ($param['amount'] < $checkout_order_info['amount']) {
            $model->refund_type = $GLOBALS['REFUND_TYPE']['PARTIAL'];
        } else {
            $model->refund_type = $GLOBALS['REFUND_TYPE']['TOTAL'];
        }
        $model->order_id = $checkout_order['id'];
        $model->refund_amount = $param['amount'];
        $model->refund_reason = $param['reason'];
        $refund_amount = $model->refund_amount;
        $refund_reason = empty($model->refund_reason) ? '' : $model->refund_reason;
        $result_refund = CheckoutOrderBusiness::processRequestRefundVcbV2([
            'checkout_order' => $checkout_order,
            'refund_type' => $model->refund_type,
            'refund_amount' => $refund_amount,
            'refund_reason' => $refund_reason,
            'user_id' => '0',
            'transaction_id' => $checkout_order_info['transaction_id'],
        ]);
        if ($result_refund['error_message'] === 'Hoàn tiền thành công') {
            $error_code = '0000';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['SUCCESS'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        } elseif ($result_refund['error_message'] == 'Tạo yêu cầu hoàn tiền thành công. Yêu cầu hoàn tiền đang được xử lý') {
            $error_code = '0000';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));
        } elseif ($result_refund['error_message'] == 'Số tiền hoàn lại không hợp lệ') {
            $error_code = '0004';
            $error_message = $this->getResultMessage($error_code);
            $result_data = array(
//                'ref_code_refund' => $result_refund['refund_transaction_id'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        } else {
            $error_code = '0001';
            $error_message = 'Tạo yêu cầu hoàn tiền thất bại ' . $result_refund['error_message'];
            $result_data = array(
//                'ref_code_refund' => $param['ref_code_refund'],
                'amount' => $model->refund_amount,
                'token_code' => $param['token_code'],
                'transaction_refund_id' => $result_refund['refund_transaction_id'],
                'transaction_status' => $GLOBALS['REFUND_STATUS']['FAIL'],
//                'checksum' => $param['checksum'],
            );
            $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

        }
        return array('error_code' => $error_code, 'error_message' => $error_message, 'result_data' => $result_data);
    }

    protected function _CheckRequireDataSetRefund($params)
    {


        if (isset($params['merchant_site_code']) && isset($params['checksum']) && isset($params['merchant_email']) && isset($params['function']) && isset($params['ref_code_refund']) && isset($params['amount'])) {
            return true;
        }
        return false;
    }

    protected function _CheckRequireDataCheckRefund($params)
    {
        if (isset($params['merchant_site_code']) && isset($params['checksum']) && isset($params['merchant_email']) && isset($params['function']) && isset($params['ref_code_refund'])) {
            return true;
        }
        return false;
    }

    protected function _validateDataCheckRefund(&$data)
    {
        $error_code = '0001';
        $api_key = Merchant::getApiKey($data['merchant_site_code'], $this->merchant_info);
        if ($this->_CheckRequireDataCheckRefund($data)) {
            if (Validation::isEmail($data['merchant_email']) || true) {
                if ($api_key !== false) {
                    if ($this->_validateChecksumCheckRefund($data, $api_key)) {


                        if ($this->_validateCheckRefRefundCode($data['ref_code_refund'], $checkout_order_info)) {
                            if ($checkout_order_info['merchant_id'] == $data['merchant_site_code']) {
                                $data['checkout_order_info'] = $checkout_order_info;
                                if ($this->_validateRefRefundCode($data['ref_code_refund'])) {
                                    $error_code = '0000';
                                } else {
                                    $error_code = '0007';
                                }

                            } else {
                                $error_code = '0003';
                            }
                        } else {
                            $error_code = '0007';
                        }


                    } else {
                        $error_code = '0008';
                    }
                } else {
                    $error_code = '0003';
                }
            } else {
                $error_code = '0009';

            }


        } else {
            $error_code = '0001';
        }


        return array('error_code' => $error_code);

    }

    protected function _validateAmountRefund($refund_type, $amount_refund, $order_amount)
    {

        if ($refund_type == 1) {
            //Hoàn toàn bộ
            if ($order_amount == $amount_refund) {
                return true;
            } else {
                return false;
            }
        } else {
            //Hoàn 1 phần
            if ($amount_refund < $order_amount) {
                return true;
            } else {
                return false;
            }
        }

    }

    protected function _validateChecksumSetRefundRequest($data, $api_key)
    {

        $str_checksum = $data['ref_code_refund'];
        $str_checksum .= ' ' . $data['token_code'];
        $str_checksum .= ' ' . $data['amount'];
        $str_checksum .= ' ' . $api_key;
        //echo($str_checksum).'<br>';
        //echo md5($str_checksum);
        $this->writeLog('[sha256 checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _validateReceiverEmail($email, $merchant)
    {
        $merchant = Tables::selectOneDataTable('user_login', ['merchant_id = :merchant_id AND email = :email ', "merchant_id" => $merchant, "email" => $email]);
        if ($merchant) {
            return true;
        }
        return false;


    }

    protected function _validateChecksumCheckRefund($data, $api_key)
    {

        $str_checksum = $data['ref_code_refund'];
        $str_checksum .= ' ' . $data['transaction_refund_id'];
        $str_checksum .= ' ' . $api_key;
        //echo($str_checksum).'<br>';
        //echo md5($str_checksum);
        $this->writeLog('[sha256 checksum]:' . $str_checksum . ' ======== ' . hash('sha256', $str_checksum));
        if ($data['checksum'] === hash('sha256', $str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _setRefundRequest($params)
    {
        $api_key = Merchant::getApiKey($params['merchant_site_code'], $this->merchant_info);
        $refund_transaction_id = '';
        $model = new CheckoutOrderWaitRefundForm();
        $checkout_order = array();
        $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['token_code = :token_code', "token_code" => $params['token_code']]);
        if ($checkout_order_info) {
            $checkout_order = CheckoutOrder::setRow($checkout_order_info);
        } else {
            return array('error_code' => '0006');
        }
        $model->order_id = $checkout_order['id'];
        $model->refund_type = $params['refund_type'];
        $model->refund_amount = $params['amount'];
        $model->refund_reason = $params['reason'];
        if ($model->validate()) {
            if ($model->refund_type == $GLOBALS['REFUND_TYPE']['TOTAL']) {
                $refund_amount = $checkout_order['amount'];
            } else {
                $refund_amount = $model->refund_amount;
            }
            $refund_reason = empty($model->refund_reason) ? '' : $model->refund_reason;
            $result_refund = CheckoutOrderBusiness::processRequestRefundAPI([
                'checkout_order' => $checkout_order,
                'refund_type' => $model->refund_type,
                'refund_amount' => $refund_amount,
                'refund_reason' => $refund_reason,
                'user_id' => '0',
                'callback' => $params['callback'],
                'ref_code_refund' => $params['ref_code_refund'],
            ]);
            if ($result_refund['error_message'] === '') {
                $refund_transaction_id = $result_refund['refund_transaction_id'];
                $error_code = '0000';
                $error_message = $this->getResultMessage($error_code);
                $result_data = array(
                    'ref_code_refund' => $params['ref_code_refund'],
                    'amount' => $model->refund_amount,
                    'token_code' => $params['token_code'],
                    'transaction_refund_id' => $result_refund['refund_transaction_id'],
                    'transaction_status' => $GLOBALS['REFUND_STATUS']['WAIT'],
                    'checksum' => hash('sha256', $params['ref_code_refund'] . ' ' . $params['token_code'] . ' ' . $refund_transaction_id . ' ' . $api_key),


                );
                $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

            } else {
                if (YII_DEBUG) {
                    echo "<pre>";
                    var_dump(11);
                    var_dump($result_refund);
                    die();
                }
                $error_code = '0007';
                $error_message = 'Tạo yêu cầu hoàn tiền thất bại ' . $result_refund['error_message'];
                $result_data = array(
                    'ref_code_refund' => $params['ref_code_refund'],
                    'amount' => $model->refund_amount,
                    'token_code' => $params['token_code'],
                    'transaction_refund_id' => $result_refund['refund_transaction_id'],
                    'transaction_status' => $GLOBALS['REFUND_STATUS']['FAIL'],
                    'checksum' => hash('sha256', $params['ref_code_refund'] . ' ' . $params['token_code'] . ' ' . $refund_transaction_id . ' ' . $api_key),


                );
                $this->writeLog('[processRequestRefundAPIprocessRequestRefundAPI]:' . json_encode($error_message));

            }
            return array('error_code' => $error_code, 'error_message' => $error_message, 'result_data' => $result_data);
        } else {
            if (YII_DEBUG) {
                echo "<pre>";
                var_dump($model->getErrors());
                die();
            }
            return array(
                'error_code' => '0007',
                'error_message' => "",
                'result_data' => []);
        }

    }


    protected function _checkRefund($params)
    {
        $error_code = '0001';
        $result_data = null;

        $result_data = CheckoutOrder::getTransactionRefund($params);
        return array('error_code' => $result_data['error_code'], 'result_data' => $result_data['result_data']);
    }


    protected function _validateChecksumCheckOrder($data, $api_key)
    {
        $str_checksum = $data['merchant_site_code'];
        $str_checksum .= '|' . $data['token_code'];
        $str_checksum .= '|' . $api_key;
        if ($data['checksum'] === md5($str_checksum)) {
            return true;
        } else {
            $tmp = ObjInput::get('ly', 'str', "");
            if ($tmp == "luonkhuon" && YII_DEBUG) {
                die(hash('sha256', $str_checksum));
            }
        }
        return false;
    }

    protected function _getLanguageId($language)
    {
        if ($language == 'en') {
            return 2;
        } else {
            return 1;
        }
    }

    protected function _getBanks($params)
    {
        $result_data = null;
        //-------------
        $error_code = '0000';
        $result_data = CheckoutOrder::getBanks($params);
        return array('error_code' => $error_code, 'result_data' => $result_data);
    }


}
