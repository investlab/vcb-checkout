<?php

namespace common\models\business;

use Yii;
use common\models\db\PaymentTransaction;
use common\models\db\PaymentTransactionReceipt;
use common\components\libs\Tables;
use common\models\db\Bill;
use common\models\db\PaymentMethod;
use common\models\db\PaymentMethodFee;
use common\models\db\Invoice;
use common\models\business\BillBusiness;
use common\models\business\PaymentTransactionReceiptBusiness;
use common\models\db\InstallmentTransaction;
use common\models\db\Transaction;
use common\models\db\CheckoutOrder;

class PaymentTransactionBusiness
{

    public static function getById($id)
    {
        return PaymentTransaction::findOne(['id' => $id]);
    }

    public static function getByIDToArray($id)
    {
        $data = PaymentTransaction::findOne(['id' => $id]);
        if ($data != null) {
            return $data->toArray();
        }
        return $data;
    }

    public static function getByBillId($id)
    {
        return PaymentTransaction::findOne(['bill_id' => $id]);
    }

    public static function getComboTableArray($bill_id)
    {
        $payment_transaction = PaymentTransaction::find()
            ->select('
                payment_transaction.id,
                payment_transaction.amount,
                payment_transaction.time_paid,
                payment_method.name
            ')
            ->leftJoin('payment_method', '`payment_method`.`id` = `payment_transaction`.`payment_method_id`')
            ->where('
                payment_transaction.`type` = 1
                AND payment_transaction.bill_id = ' . $bill_id . '
                AND ( payment_transaction.`status` = 4 OR  payment_transaction.`status` = 6 )
            ')
            ->orderBy('payment_method.name DESC')
            ->asArray()->all();
        return $payment_transaction;
    }

    /**
     *
     * @param type $params : id, partner_payment_method_receipt, user_created, user_id
     * @param type $rollback
     * @return type
     */
    static function edit($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = self::getByID($params['id']);
        if ($payment_transaction != null) {
            if (trim($params['partner_payment_method_receipt']) != '') {
                $payment_transaction->partner_payment_method_receipt = trim($params['partner_payment_method_receipt']);
            }

            if (trim($params['user_created']) != '') {
                $payment_transaction->user_created = trim($params['user_created']);
            }
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                if (trim($params['partner_payment_method_receipt']) != '') {
                    $payment_transaction_receipt = PaymentTransactionReceiptBusiness::editPPMReceipt($params, false);
                    if ($payment_transaction_receipt['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $payment_transaction_receipt['error_message'];
                    }
                } else {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : type, merchant_id, checkout_order_id, bill_id, invoice_id, payment_method_id, partner_payment_id, partner_payment_info, amount, currency,
     * payer_fullname, payer_email, payer_mobile, payer_address, payer_zone_id, time_limit, installment_bank_id, installment_period, installment_amount, user_id
     * @return type
     */
    public static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //------------       
        if (intval($params['bill_id']) != 0) {
            $result = self::_addForBill($params, false);
            if ($result['error_message'] == '') {
                $id = $result['id'];
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } elseif (intval($params['invoice_id']) != 0) {
            $result = self::_addForInvoice($params, false);
            if ($result['error_message'] == '') {
                $id = $result['id'];
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : type, merchant_id, checkout_order_id, bill_id, payment_method_id, partner_payment_id, partner_payment_info, amount, currency,
     * payer_fullname, payer_email, payer_mobile, payer_address, payer_zone_id, payer_id_number, payer_id_type, time_limit, installment_bank_id, installment_period, installment_amount, user_id
     * @return type
     */
    private static function _addForBill($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //------------       
        $now = time();
        $bill_info = Tables::selectOneDataTable("bill", "id = " . $params['bill_id']);
        if ($bill_info != false) {
            if (Bill::hasSupportPaymentMethodId($bill_info, $params['payment_method_id'])) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", "id = " . $params['payment_method_id'] . " AND status = " . PaymentMethod::STATUS_ACTIVE);
                if ($payment_method_info != false) {
                    $payment_method_payer_fee = PaymentMethodFee::getFee($params['payment_method_id'], $params['amount'], $now, $payment_method_fee_info, $partner_payment_id);
                    if ($payment_method_payer_fee !== false) {
                        if ($params['installment_period'] == 0) {
                            $installment_bank_id = 0;
                            $installment_period = 0;
                            $installment_amount = 0;
                        } else {
                            $installment_bank_id = $params['installment_bank_id'];
                            $installment_period = $params['installment_period'];
                            $installment_amount = $params['installment_amount'];
                        }
                        $payment_transaction = new PaymentTransaction();
                        $payment_transaction->merchant_id = $params['merchant_id'];
                        $payment_transaction->checkout_order_id = $params['checkout_order_id'];
                        $payment_transaction->type = $params['type'];
                        $payment_transaction->bill_id = $params['bill_id'];
                        $payment_transaction->invoice_id = 0;
                        $payment_transaction->invoice_code = '';
                        $payment_transaction->payment_method_id = $params['payment_method_id'];
                        $payment_transaction->payment_method_fee = $payment_method_payer_fee;
                        $payment_transaction->partner_payment_id = $params['partner_payment_id'];
                        $payment_transaction->partner_payment_info = $params['partner_payment_info'];
                        $payment_transaction->partner_payment_method_fee = 0;
                        $payment_transaction->amount = $params['amount'] + $payment_method_payer_fee;
                        $payment_transaction->currency = $params['currency'];
                        $payment_transaction->payer_fullname = $params['payer_fullname'];
                        $payment_transaction->payer_email = $params['payer_email'];
                        $payment_transaction->payer_mobile = $params['payer_mobile'];
                        $payment_transaction->payer_address = $params['payer_address'];
                        $payment_transaction->payer_zone_id = $params['payer_zone_id'];
                        $payment_transaction->payer_id_number = $params['payer_id_number'];
                        $payment_transaction->payer_id_type = $params['payer_id_type'];
                        $payment_transaction->time_limit = $params['time_limit'];
                        $payment_transaction->installment_bank_id = $installment_bank_id;
                        $payment_transaction->installment_period = $installment_period;
                        $payment_transaction->installment_amount = $installment_amount;
                        $payment_transaction->status = PaymentTransaction::STATUS_NOT_PAYMENT;
                        $payment_transaction->time_created = $now;
                        $payment_transaction->user_created = $params['user_id'];
                        if ($payment_transaction->validate() && $payment_transaction->save()) {
                            $id = $payment_transaction->getDb()->getLastInsertID();
                            if ($payment_method_info['code'] == 'COD') {
                                $inputs = array(
                                    'bill_id' => $params['bill_id'],
                                    'type' => Bill::TYPE_COD,
                                    'user_id' => $params['user_id'],
                                );
                                $result = BillBusiness::updateType($inputs, false);
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = '';
                                $commit = true;
                            }
                        } else {
                            $error_message = 'Có lỗi khi thêm giao dịch thanh toán';
                        }
                    } else {
                        $error_message = 'Phương thức thanh toán không có cấu hình phí hợp lệ';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không hợp lệ';
                }
            } else {
                $error_message = 'Đơn hàng không hỗ trợ thanh toán bằng hình thức được chọn';
            }
        } else {
            $error_message = 'Đơn hàng không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : type, merchant_id, invoice_id, payment_method_id, partner_payment_id, partner_payment_info, amount, currency,
     * time_limit, user_id
     * @return type
     */
    private static function _addForInvoice($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //------------
        $now = time();
        $invoice_info = Tables::selectOneDataTable("invoice", "id = " . $params['invoice_id']);
        if ($invoice_info != false) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", "id = " . $params['payment_method_id'] . " AND status = " . PaymentMethod::STATUS_ACTIVE);
            if ($payment_method_info != false) {
                $payment_method_fee = PaymentMethodFee::getFee($params['payment_method_id'], $params['amount'], $now, $payment_method_fee_info, $partner_payment_id);
                if ($payment_method_fee !== false) {
                    $payment_transaction = new PaymentTransaction();
                    $payment_transaction->type = $params['type'];
                    $payment_transaction->merchant_id = $params['merchant_id'];
                    $payment_transaction->bill_id = 0;
                    $payment_transaction->invoice_id = $params['invoice_id'];
                    $payment_transaction->invoice_code = $invoice_info['code'];
                    $payment_transaction->payment_method_id = $params['payment_method_id'];
                    $payment_transaction->payment_method_fee = $payment_method_fee;
                    $payment_transaction->partner_payment_id = $params['partner_payment_id'];
                    $payment_transaction->partner_payment_info = $params['partner_payment_info'];
                    $payment_transaction->partner_payment_method_fee = 0;
                    $payment_transaction->amount = $params['amount'];
                    $payment_transaction->currency = $params['currency'];
                    $payment_transaction->time_limit = $params['time_limit'];
                    $payment_transaction->status = PaymentTransaction::STATUS_NOT_PAYMENT;
                    $payment_transaction->time_created = $now;
                    $payment_transaction->user_created = $params['user_id'];
                    if ($payment_transaction->validate() && $payment_transaction->save()) {
                        $id = $payment_transaction->getDb()->getLastInsertID();
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi thêm giao dịch thanh toán';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không có cấu hình phí hợp lệ';
                }
            } else {
                $error_message = 'Phương thức thanh toán không hợp lệ';
            }
        } else {
            $error_message = 'Hóa đơn không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }


    /**
     *
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_info, amount, currency,
     * time_limit, installment_bank_id, installment_period, installment_amount, user_id
     * @return type
     */
    public static function addForCheckoutOrder($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //------------       
        $now = time();
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", "id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrder::STATUS_NEW);
        if ($checkout_order_info != false) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", "id = " . $params['payment_method_id'] . " AND status = " . PaymentMethod::STATUS_ACTIVE);
            if ($payment_method_info != false) {
                $payment_method_fee = PaymentMethodFee::getFee($params['payment_method_id'], $params['amount'], $now, $payment_method_fee_info, $partner_payment_id);
                if ($payment_method_fee !== false) {
                    if ($params['installment_period'] == 0) {
                        $installment_bank_id = 0;
                        $installment_period = 0;
                        $installment_amount = 0;
                    } else {
                        $installment_bank_id = $params['installment_bank_id'];
                        $installment_period = $params['installment_period'];
                        $installment_amount = $params['installment_amount'];
                    }
                    $payment_transaction = new PaymentTransaction();
                    $payment_transaction->merchant_id = $checkout_order_info['merchant_id'];
                    $payment_transaction->checkout_order_id = $params['checkout_order_id'];
                    $payment_transaction->type = PaymentTransaction::TYPE_PAYMENT;
                    $payment_transaction->bill_id = $checkout_order_info['sale_order_id'];
                    $payment_transaction->invoice_id = 0;
                    $payment_transaction->invoice_code = '';
                    $payment_transaction->payment_method_id = $params['payment_method_id'];
                    $payment_transaction->payment_method_fee = $payment_method_fee;
                    $payment_transaction->partner_payment_id = $params['partner_payment_id'];
                    $payment_transaction->partner_payment_info = $params['partner_payment_info'];
                    $payment_transaction->partner_payment_method_fee = 0;
                    $payment_transaction->amount = $params['amount'];
                    $payment_transaction->currency = $params['currency'];
                    $payment_transaction->payer_fullname = $checkout_order_info['payer_fullname'];
                    $payment_transaction->payer_email = $checkout_order_info['payer_email'];
                    $payment_transaction->payer_mobile = $checkout_order_info['payer_mobile'];
                    $payment_transaction->payer_address = $checkout_order_info['payer_address'];
                    $payment_transaction->payer_zone_id = 0;
                    $payment_transaction->payer_id_number = $checkout_order_info['payer_id_number'];
                    $payment_transaction->payer_id_type = $checkout_order_info['payer_id_type'];
                    $payment_transaction->time_limit = $checkout_order_info['time_limit'];
                    $payment_transaction->installment_bank_id = $installment_bank_id;
                    $payment_transaction->installment_period = $installment_period;
                    $payment_transaction->installment_amount = $installment_amount;
                    $payment_transaction->status = PaymentTransaction::STATUS_NOT_PAYMENT;
                    $payment_transaction->time_created = $now;
                    $payment_transaction->user_created = $params['user_id'];
                    if ($payment_transaction->validate() && $payment_transaction->save()) {
                        $id = $payment_transaction->getDb()->getLastInsertID();
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi thêm giao dịch thanh toán';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không có cấu hình phí hợp lệ';
                }
            } else {
                $error_message = 'Phương thức thanh toán không hợp lệ';
            }
        } else {
            $error_message = 'Đơn thanh toán không tồn tại';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param params : id, receipt, bin_code, card_type, request_token, time_paid, user_id
     * @param rollback
     */
    static function paid($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $installment_transaction_id = null;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_NOT_PAYMENT . "," . PaymentTransaction::STATUS_PAYING . "," . PaymentTransaction::STATUS_VERIFY . ") ")->one();
        if ($payment_transaction != null) {
            $inputs = array(
                'payment_transaction_id' => $params['id'],
                'receipt' => $params['receipt'],
            );
            $result = PaymentTransactionReceiptBusiness::add($inputs, false);
            if ($result['error_message'] == '') {
                $payment_transaction->status = PaymentTransaction::STATUS_PAID;
                $payment_transaction->partner_payment_method_receipt = trim($params['receipt']);
                $payment_transaction->partner_payment_info = PaymentTransaction::encryptPartnerPaymentInfo($payment_transaction->partner_payment_info, @$params['bin_code'], @$params['card_type'], @$params['request_token']);
                $payment_transaction->time_paid = $params['time_paid'];
                $payment_transaction->user_paid = $params['user_id'];
                if ($payment_transaction->validate() && $payment_transaction->save()) {
                    if ($payment_transaction->type == PaymentTransaction::TYPE_PAYMENT) {
                        $all = true;
                        if (intval($payment_transaction->bill_id) != 0) {
                            $bill_info = Tables::selectOneDataTable("bill", "id = " . $payment_transaction->bill_id . " AND payment_status IN (" . Bill::PAYMENT_STATUS_NOT_PAYMENT . "," . Bill::PAYMENT_STATUS_PAYING . ") ");
                            if ($bill_info != false) {
                                $inputs = array(
                                    'bill_id' => $payment_transaction->bill_id,
                                    'user_id' => $params['user_id'],
                                    'time_paid' => $params['time_paid'],
                                );
                                $result = BillBusiness::updatePartialAmount($inputs, false);
                                if ($result['error_message'] != '') {
                                    $all = false;
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $all = false;
                                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
                            }
                        }
                        if ($all && intval($payment_transaction->checkout_order_id) != 0) {
                            $checkout_order_info = Tables::selectOneDataTable("checkout_order", "id = " . $payment_transaction->checkout_order_id . " AND payment_transaction_id = " . $payment_transaction->id . " AND status IN (" . CheckoutOrder::STATUS_NEW . "," . CheckoutOrder::STATUS_PAYING . ") ");
                            if ($checkout_order_info != false) {
                                $inputs = array(
                                    'checkout_order_id' => $checkout_order_info['id'],
                                    'time_paid' => $params['time_paid'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaid($inputs, false);
                                if ($result['error_message'] != '') {
                                    $all = false;
                                    $error_message = $result['error_message'];
                                } else {
                                    $inputs = array(
                                        'checkout_order_id' => $payment_transaction->checkout_order_id,
                                        'cybersource_request_id' => $params['receipt'],
                                    );
                                    $result = CheckoutOrderNotifyBusiness::addPaymentSuccessNotify($inputs);
                                    if ($result['error_message'] != '') {
                                        $all = false;
                                        $error_message = $result['error_message'];
                                    }
                                }
                            }
                        }
                        if ($all) {
                            if (intval($payment_transaction->installment_bank_id) != 0) {
                                $partner_payment_info = json_decode($payment_transaction->partner_payment_info, true);
                                $inputs = array(
                                    'payment_transaction_id' => $payment_transaction->id,
                                    'bin_code' => @$params['bin_code'],
                                    'card_type' => @$params['card_type'],
                                    'card_holder_id_number' => @$partner_payment_info['card_holder_id_number'],
                                    'card_holder_name' => @$partner_payment_info['card_holder_name'],
                                    'card_holder_mobile' => @$partner_payment_info['card_holder_mobile'],
                                    'user_id' => $params['user_id'],
                                );
                                $result = InstallmentTransactionBusiness::add($inputs, false);
                                if ($result['error_message'] == '') {
                                    $installment_transaction_id = $result['id'];
                                } else {
                                    $all = false;
                                    $error_message = $result['error_message'];
                                }
                            }
                        }
                        if ($all) {
                            $error_message = '';
                            $commit = true;
                        }
                    } elseif ($payment_transaction->type == PaymentTransaction::TYPE_REFUND) {
                        $error_message = '';
                        $commit = true;
                    } elseif ($payment_transaction->type == PaymentTransaction::TYPE_PAYMENT_INVOICE) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Lỗi không xác định';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
                }
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'installment_transaction_id' => $installment_transaction_id);
    }

    /**
     *
     * @param params : id, user_id
     * @param rollback
     */
    static function paying($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_NOT_PAYMENT . "," . PaymentTransaction::STATUS_PAYING . ") ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->status = PaymentTransaction::STATUS_PAYING;
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate()) {
                if ($payment_transaction->save()) {
                    $all = true;
                    if (intval($payment_transaction->bill_id) != 0) {
                        $inputs = array(
                            'bill_id' => $payment_transaction->bill_id,
                            'user_id' => $params['user_id'],
                        );
                        $result = BillBusiness::paying($inputs, false);
                        if ($result['error_message'] != '') {
                            $all = false;
                            $error_message = $result['error_message'];
                        }
                    }
                    if ($all && intval($payment_transaction->checkout_order_id) != 0) {
                        $inputs = array(
                            'checkout_order_id' => $payment_transaction->checkout_order_id,
                            'payment_transaction_id' => $payment_transaction->id,
                        );
                        $result = CheckoutOrderBusiness::updateStatusPaying($inputs, false);
                        if ($result['error_message'] != '') {
                            $all = false;
                            $error_message = $result['error_message'];
                        }
                    }
                    if ($all) {
                        $error_message = '';
                        $commit = true;
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param params : id, reason_id, reason, user_id
     * @param rollback
     */
    static function cancel($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_NOT_PAYMENT . "," . PaymentTransaction::STATUS_PAYING . ") ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->status = PaymentTransaction::STATUS_CANCEL;
            $payment_transaction->reason_id = $params['reason_id'];
            $payment_transaction->reason = $params['reason'];
            $payment_transaction->time_cancel = time();
            $payment_transaction->user_cancel = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param params : id, receipt, bin_code, card_type, request_token, user_id
     * @param rollback
     */
    static function review($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status = " . PaymentTransaction::STATUS_PAYING . " ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->status = PaymentTransaction::STATUS_REVIEW;
            $payment_transaction->partner_payment_method_receipt = trim($params['receipt']);
            $payment_transaction->partner_payment_info = PaymentTransaction::encryptPartnerPaymentInfo($payment_transaction->partner_payment_info, @$params['bin_code'], @$params['card_type'], @$params['request_token']);
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $params_review_notify = [
                    'checkout_order_id' => $payment_transaction->checkout_order_id,
                    'cybersource_request_id' => $params['receipt'],
                ];
                $addPaymentReview = CheckoutOrderNotifyBusiness::addPaymentReviewNotify($params_review_notify);
                if ($addPaymentReview['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $addPaymentReview['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params bill_id, reason_cancel_id, reason_cancel, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelByBill($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction_info = Tables::selectAllDataTable("payment_transaction", "bill_id = " . $params['bill_id'] . " AND status = " . PaymentTransaction::STATUS_NOT_PAYMENT);
        if ($payment_transaction_info != false) {
            $inputs = array(
                'reason_id' => $params['reason_cancel_id'],
                'reason' => $params['reason_cancel'],
                'user_id' => $params['user_id'],
            );
            $all = true;
            foreach ($payment_transaction_info as $row) {
                $inputs['id'] = $row['id'];
                $result = self::cancel($inputs, false);
                if ($result['error_message'] != '') {
                    $error_message = $result['error_message'];
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $error_message = '';
                $commit = true;
            }
        } else {
            $error_message = '';
            $commit = true;
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : payment_transaction_id, refund_amount, reason_refund_id, reason_refund, time_refund, receipt, user_id
     * @param type $rollback
     * @return type
     */
    static function refund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['payment_transaction_id'] . " AND type = " . PaymentTransaction::TYPE_PAYMENT . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_REFUND . ")")->one();
        if ($payment_transaction != null) {
            if ($payment_transaction->amount >= $params['refund_amount']) {
                $installment_transaction_info = Tables::selectOneDataTable("installment_transaction", "payment_transaction_id = " . $params['payment_transaction_id'] . " ");
                if ($installment_transaction_info == false || in_array($installment_transaction_info['status'], array(InstallmentTransaction::STATUS_NOT_REQUEST, InstallmentTransaction::STATUS_REJECT, InstallmentTransaction::STATUS_CANCEL))) {
                    $result = self::addAndPaidTransactionRefund($params, false);
                    if ($result['error_message'] == '') {
                        $refund_transaction_id = $result['id'];
                        $payment_transaction->reason_id = $params['reason_refund_id'];
                        $payment_transaction->reason = $params['reason_refund'];
                        $payment_transaction->status = PaymentTransaction::STATUS_REFUND;
                        $payment_transaction->user_created = $params['user_id'];
                        if ($payment_transaction->validate() && $payment_transaction->save()) {
                            $all = true;
                            // check transaction voucher payment
                            $transaction_info = Tables::selectAllDataTable("transaction", "payment_transaction_id = " . $params['payment_transaction_id'] . " AND status = " . Transaction::STATUS_PERFORM . " AND type IN (" . Transaction::TYPE_PAYMENT_VOUCHER . "," . Transaction::TYPE_PAYMENT_VOUCHER_PROMOTION . ") ");
                            if ($transaction_info != false) {
                                foreach ($transaction_info as $row) {
                                    $inputs = array(
                                        'transaction_id' => $row['id'],
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = TransactionBusiness::refundPaymentVoucher($inputs, false);
                                    if ($result['error_message'] != '') {
                                        $error_message = $result['error_message'];
                                        $all = false;
                                        break;
                                    }
                                }
                            }
                            if ($all) {
                                // cancel installment transaction
                                if ($installment_transaction_info != false && $installment_transaction_info['status'] == InstallmentTransaction::STATUS_NOT_REQUEST) {
                                    $inputs = array(
                                        'id' => $installment_transaction_info['id'],
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = InstallmentTransactionBusiness::cancel($inputs, false);
                                    if ($result['error_message'] != '') {
                                        $error_message = $result['error_message'];
                                        $all = false;
                                    }
                                }
                            }
                            if ($all && intval($payment_transaction->checkout_order_id) != 0) {
                                $inputs = array(
                                    'checkout_order_id' => $payment_transaction->checkout_order_id,
                                    'refund_transaction_id' => $refund_transaction_id,
                                    'refund_amount' => $params['refund_amount'],
                                    'reason' => $params['reason_refund'],
                                );
                                $addPaymentRefund = CheckoutOrderNotifyBusiness::addPaymentRefundNotify($inputs);
                                if ($addPaymentRefund['error_message'] != '') {
                                    $all = false;
                                    $error_message = $addPaymentRefund['error_message'];
                                }
                            }
                            if ($all) {
                                if (intval($payment_transaction->bill_id) != 0) {
                                    $bill_info = Tables::selectOneDataTable("bill", "id = " . $payment_transaction->bill_id . " ");
                                    if ($bill_info != false) {
                                        $inputs = array(
                                            'bill_id' => $payment_transaction->bill_id,
                                            'user_id' => $params['user_id'],
                                            'time_paid' => time(),
                                        );
                                        $result = BillBusiness::updatePartialAmount($inputs, false);
                                        if ($result['error_message'] == '') {
                                            $inputs = array(
                                                'bill_id' => $payment_transaction->bill_id,
                                                'reason_refund_id' => $params['reason_refund_id'],
                                                'reason_refund' => $params['reason_refund'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = BillBusiness::updatePaymentStatusRefund($inputs, false);
                                            if ($result['error_message'] == '') {
                                                $error_message = '';
                                                $commit = true;
                                            } else {
                                                $error_message = $result['error_message'];
                                            }
                                        } else {
                                            $error_message = $result['error_message'];
                                        }
                                    } else {
                                        $error_message = 'Đơn hàng không tồn tại';
                                    }
                                } else {
                                    $error_message = '';
                                    $commit = true;
                                }
                            }
                        } else {
                            $error_message = 'Có lỗi khi cập nhật giao dịch';
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Giao dịch đã phát sinh yêu cầu chuyển đổi trả góp. Vui lòng xác nhận với ngân hàng kết quả chuyển đổi.';
                }
            } else {
                $error_message = 'Số tiền muốn hoàn không hợp lệ';
            }
        } else {
            $error_message = 'Giao dịch không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param params : payment_transaction_id, receipt, refund_amount, reason_refund, reason_refund_id, time_refund, user_id
     * @param rollback
     */
    static function addTransactionRefund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //------------
        $now = time();
        $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $params['payment_transaction_id'] . " AND type = " . PaymentTransaction::TYPE_PAYMENT . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_REFUND . ") ");
        if ($payment_transaction_info != false) {
            if ($params['refund_amount'] <= $payment_transaction_info['amount']) {
                $payment_transaction = new PaymentTransaction();
                $payment_transaction->merchant_id = $payment_transaction_info['merchant_id'];
                $payment_transaction->type = PaymentTransaction::TYPE_REFUND;
                $payment_transaction->bill_id = $payment_transaction_info['bill_id'];
                $payment_transaction->invoice_id = $payment_transaction_info['invoice_id'];
                $payment_transaction->invoice_code = $payment_transaction_info['invoice_code'];
                $payment_transaction->payment_method_id = $payment_transaction_info['payment_method_id'];
                $payment_transaction->payment_method_fee = $payment_transaction_info['payment_method_fee'];
                $payment_transaction->partner_payment_id = $payment_transaction_info['partner_payment_id'];
                $payment_transaction->partner_payment_method_fee = 0;
                $payment_transaction->amount = $params['refund_amount'];
                $payment_transaction->currency = $payment_transaction_info['currency'];
                $payment_transaction->time_limit = time() + 86400;
                $payment_transaction->reason = $params['reason_refund'];
                $payment_transaction->reason_id = $params['reason_refund_id'];
                $payment_transaction->related_payment_transaction_id = $params['payment_transaction_id'];
                $payment_transaction->status = PaymentTransaction::STATUS_NOT_PAYMENT;
                $payment_transaction->time_created = $now;
                $payment_transaction->user_created = $params['user_id'];
                if ($payment_transaction->validate() && $payment_transaction->save()) {
                    $id = $payment_transaction->getDb()->getLastInsertID();
                    $inputs = array(
                        'refund_transaction_id' => $id,
                        'user_id' => $params['user_id'],
                    );
                    $result = PaymentTransactionRefundBusiness::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi thêm giao dịch hoàn tiền';
                }
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param params : payment_transaction_id, receipt, refund_amount, reason_refund, reason_refund_id, time_refund, user_id
     * @param rollback
     */
    static function addAndPaidTransactionRefund($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $result = self::addTransactionRefund($params, false);
        if ($result['error_message'] == '') {
            $id = $result['id'];
            $inputs = array(
                'id' => $id,
                'receipt' => $params['receipt'],
                'time_paid' => $params['time_refund'],
                'user_id' => $params['user_id']
            );
            $result = self::paid($inputs, false);
            if ($result['error_message'] == '') {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = $result['error_message'];
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : id, bank_receipt, user_id
     * @param type $rollback
     * @return type
     */
    static function updateBankReceipt($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status = " . PaymentTransaction::STATUS_PAID)->one();
        if ($payment_transaction != null) {
            $payment_transaction->bank_receipt = $params['bank_receipt'];
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, partner_payment_info, user_id
     * @param type $rollback
     * @return type
     */
    static function updatePartnerPaymentInfo($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_PAYING . "," . PaymentTransaction::STATUS_NOT_PAYMENT . ") ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->partner_payment_info = $params['partner_payment_info'];
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, partner_payment_info,installment_bank_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function updatePaymentInfoIntransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }

        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'])->one();
        if ($payment_transaction != null) {
            $partner_payment_info = array();
            if (trim($payment_transaction->partner_payment_info) != '') {
                $partner_payment_info = json_decode($payment_transaction->partner_payment_info, true);
            }
            if (!is_array($params['partner_payment_info'])) {
                $params['partner_payment_info'] = json_decode($params['partner_payment_info'], true);
            }
            $partner_payment_info = array_merge($partner_payment_info, $params['partner_payment_info']);
            $payment_transaction->partner_payment_info = json_encode($partner_payment_info, true);
            $payment_transaction->installment_bank_refer_code = $params['installment_bank_refer_code'];
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];

            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, period, user_id
     * @param type $rollback
     * @return type
     */
    static function updateInstallmentPeriod($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_PAYING . "," . PaymentTransaction::STATUS_NOT_PAYMENT . ") ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->installment_period = $params['period'];
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, installment_bank_id, installment_period, installment_bank_refer_code, bin_code, card_type, user_id
     * @param type $rollback
     * @return type
     */
    static function convertInstallmentTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------        
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status = " . PaymentTransaction::STATUS_PAID . " ")->one();
        if ($payment_transaction != null) {
            $installment_transaction_info = Tables::selectOneDataTable("installment_transaction", "payment_transaction_id = " . $params['id']);
            if ($installment_transaction_info == false) {
                $payment_transaction->installment_bank_id = $params['installment_bank_id'];
                $payment_transaction->installment_period = $params['installment_period'];
                $payment_transaction->installment_bank_refer_code = $params['installment_bank_refer_code'];
                $payment_transaction->installment_amount = $payment_transaction->amount;
                $payment_transaction->time_updated = time();
                $payment_transaction->user_updated = $params['user_id'];
                if ($payment_transaction->validate()) {
                    if ($payment_transaction->save()) {
                        if ($payment_transaction->partner_payment_info != '') {
                            $partner_payment_info = json_decode($payment_transaction->partner_payment_info, true);
                        } else {
                            $partner_payment_info = array();
                        }
                        $inputs = array(
                            'payment_transaction_id' => $params['id'],
                            'bin_code' => $params['bin_code'],
                            'card_type' => $params['card_type'],
                            'card_holder_id_number' => @$partner_payment_info['card_holder_id_number'],
                            'card_holder_name' => @$partner_payment_info['card_holder_name'],
                            'card_holder_mobile' => @$partner_payment_info['card_holder_mobile'],
                            'user_id' => $params['user_id'],
                        );
                        $result = InstallmentTransactionBusiness::add($inputs, false);
                        if ($result['error_message'] == '') {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Đã phát sinh giao dịch trả góp cho giao dịch thanh toán này';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, payer_fullname, payer_mobile, payer_id_number, payer_email, payer_address, partner_payment_info, user_id
     * @param type $rollback
     * @return type
     */
    static function updatePayerInfo($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_PAYING . "," . PaymentTransaction::STATUS_NOT_PAYMENT . ") ")->one();
        if ($payment_transaction != null) {
            $payment_transaction->payer_fullname = $params['payer_fullname'];
            $payment_transaction->payer_mobile = $params['payer_mobile'];
            $payment_transaction->payer_email = $params['payer_email'];
            $payment_transaction->payer_address = $params['payer_address'];
            $payment_transaction->payer_id_number = $params['payer_id_number'];
            $payment_transaction->partner_payment_info = $params['partner_payment_info'];
            $payment_transaction->time_updated = time();
            $payment_transaction->user_updated = $params['user_id'];
            if ($payment_transaction->validate() && $payment_transaction->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param params : id, user_id
     * @param rollback
     */
    static function verify($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransaction::getDb()->beginTransaction();
        }
        //----------
        $payment_transaction = PaymentTransaction::findBySql("SELECT * FROM payment_transaction WHERE id = " . $params['id'] . " AND status IN (" . PaymentTransaction::STATUS_NOT_PAYMENT . "," . PaymentTransaction::STATUS_PAYING . "," . PaymentTransaction::STATUS_VERIFY . "," . PaymentTransaction::STATUS_PAID . ") ")->one();
        if ($payment_transaction != null) {
            if ($payment_transaction->status == PaymentTransaction::STATUS_VERIFY || $payment_transaction->status == PaymentTransaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $payment_transaction->status = PaymentTransaction::STATUS_VERIFY;
                $payment_transaction->time_updated = time();
                $payment_transaction->user_updated = $params['user_id'];
                if ($payment_transaction->validate()) {
                    if ($payment_transaction->save()) {
                        /*$inputs = array(
                            'bill_id' => $payment_transaction->bill_id,
                            'user_id' => $params['user_id'],
                        );
                        $result = BillBusiness::updateSaleStatusAccept($inputs, false);
                        if ($result['error_message'] == '') {*/
                        $error_message = '';
                        $commit = true;
                        /*} else {
                            $error_message = $result['error_message'];
                        }*/
                    } else {
                        $error_message = 'Có lỗi khi cập nhật giao dịch thanh toán';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            }
        } else {
            $error_message = 'Giao dịch thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }
}