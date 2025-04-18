<?php

namespace common\models\business;

use common\components\libs\NotifySystem;
use common\models\db\CardVoucherRequirement;
use common\payments\CyberSource;
use Exception;
use Yii;
use common\components\utils\Logs;
use common\components\libs\Tables;
use common\models\db\Transaction;
use common\models\db\CheckoutOrder;
use common\models\db\PaymentMethod;
use common\models\db\Merchant;
use common\models\db\Partner;
use common\models\db\MerchantFee;
use common\models\db\PartnerPaymentFee;
use common\models\db\TransactionType;
use common\models\db\Cashout;
use common\models\db\Account;
use common\models\db\PartnerPaymentAccount;

class TransactionBusiness
{

    const PAYMENT_METHOD_IGNORE = [
        'VCB-ATM-CARD',
        'VISA-CREDIT-CARD',
        'MASTERCARD-CREDIT-CARD',
        'JCB-CREDIT-CARD',
        'MPOS-SWIPE-CARD',
        'MPOS-REFUND-SWIPE-CARD',
        'AMEX-CREDIT-CARD',
        'ABB-QRCODE_OFFLINE', 'ACB-QRCODE_OFFLINE', 'AGB-QRCODE_OFFLINE', 'AIRPAY-QRCODE_OFFLINE', 'BAB-QRCODE_OFFLINE', 'BIDC-QRCODE_OFFLINE', 'BIDV-QRCODE_OFFLINE', 'EXB-QRCODE_OFFLINE',
        'GDB-QRCODE_OFFLINE', 'HDB-QRCODE_OFFLINE', 'ICB-QRCODE_OFFLINE', 'IVB-QRCODE_OFFLINE', 'MB-QRCODE_OFFLINE', 'MOMO-QRCODE_OFFLINE', 'MSB-QRCODE_OFFLINE', 'NAB-QRCODE_OFFLINE',
        'NVB-QRCODE_OFFLINE', 'OCB-QRCODE_OFFLINE', 'OJB-QRCODE_OFFLINE', 'PVCOMBANK-QRCODE_OFFLINE', 'SCB-QRCODE_OFFLINE', 'SEA-QRCODE_OFFLINE', 'SGB-QRCODE_OFFLINE',
        'SHB-QRCODE_OFFLINE', 'SMARTPAY-QRCODE_OFFLINE', 'STB-QRCODE_OFFLINE', 'TCB-QRCODE_OFFLINE', 'TPB-QRCODE_OFFLINE', 'VAB-QRCODE_OFFLINE', 'VB-QRCODE_OFFLINE',
        'VCB-QRCODE_OFFLINE', 'VCBPAY-QRCODE_OFFLINE', 'VIB-QRCODE_OFFLINE', 'VIETTELPAY-QRCODE_OFFLINE', 'VIETTELPOST-QRCODE_OFFLINE', 'VINID-QRCODE_OFFLINE',
        'VPB-QRCODE_OFFLINE', 'WCP-QRCODE_OFFLINE', 'WRB-QRCODE_OFFLINE'
    ];

    private static function _getSenderAccountId($transaction_type_id, $merchant_info, $currency)
    {
        if (TransactionType::isPaymentTransactionType($transaction_type_id) || TransactionType::isInstallmentTransactionType($transaction_type_id)) {
            return Account::getMasterAccountId($currency);
        } elseif (TransactionType::isWithdrawTransactionType($transaction_type_id)) {
            return Account::getAccountIdByMerchantId($merchant_info['id'], $currency);
        } elseif (TransactionType::isRefundTransactionType($transaction_type_id)) {
            return Account::getAccountIdByMerchantId($merchant_info['id'], $currency);
        } elseif (TransactionType::isDepositTransactionType($transaction_type_id)) {
            return Account::getMasterAccountId($currency);
        }
        return false;
    }

    private static function _getReceiverAccountId($transaction_type_id, $merchant_info, $currency)
    {
        if (TransactionType::isPaymentTransactionType($transaction_type_id) || TransactionType::isInstallmentTransactionType($transaction_type_id)) {
            return Account::getAccountIdByMerchantId($merchant_info['id'], $currency);
        } elseif (TransactionType::isWithdrawTransactionType($transaction_type_id)) {
            return Account::getMasterAccountId($currency);
        } elseif (TransactionType::isRefundTransactionType($transaction_type_id)) {
            return Account::getMasterAccountId($currency);
        } elseif (TransactionType::isDepositTransactionType($transaction_type_id)) {
            return Account::getAccountIdByMerchantId($merchant_info['id'], $currency);
        }
        return false;
    }

    private static function _getPartnerPaymentAccountId($params)
    {

        if (TransactionType::isPaymentTransactionType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionPayment($params);
        } elseif (TransactionType::isRefundTransactionType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionRefund($params);
        } elseif (TransactionType::isWithdrawTransactionType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionWithdraw($params);
        } elseif (TransactionType::isDepositTransactionType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionDeposit($params);
        } elseif (TransactionType::isInstallmentTransactionType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionInstallment($params);
        } elseif (TransactionType::isWithdrawTransactionCardVoucherType($params['transaction_type_id'])) {
            return self::_getPartnerPaymentAccountIdForTransactionCardVoucherWithdraw($params);
        }
        return false;
    }

    private static function _getPartnerPaymentAccountIdForTransactionPayment($params)
    {
        // $partner_payment_id = \common\models\db\PartnerPayment::getIdByCode('NGANLUONG');
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status ",
            'merchant_id' => $params['merchant_id'],
            'partner_payment_id' => $params['partner_payment_id'],
            'status' => PartnerPaymentAccount::STATUS_ACTIVE
        ]);
        if ($partner_payment_account_info != false) {
//            if (count($partner_payment_account_info) > 1) {
//                $partner_payment_account_ids = array();
//                $transaction_amounts = array();
//                foreach ($partner_payment_account_info as $row) {
//                    $transaction_amounts[$row['id']] = 0;
//                    $partner_payment_account_ids[$row['id']] = $row['id'];
//                }
//                $today = getdate();
//                $time_begin = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
//                $time_end = $time_begin + 86400;
//                $sql = "SELECT partner_payment_account_id, SUM(amount) AS total_amount "
//                        . "FROM transaction "
//                        . "WHERE partner_payment_account_id IN (" . implode(',', $partner_payment_account_ids) . ") "
//                        . "AND time_paid >= $time_begin "
//                        . "AND time_paid < $time_end "
//                        . "AND transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
//                        . "AND status = " . Transaction::STATUS_PAID . " "
//                        . "GROUP BY partner_payment_account_id "
//                        . "ORDER BY total_amount ASC, partner_payment_account_id ASC ";
//                $command = Transaction::getDb()->createCommand($sql);
//                $result = $command->queryAll();
//                if ($result != false) {
//                    foreach ($result as $row) {
//                        $transaction_amounts[$row['partner_payment_account_id']] = intval($row['total_amount']);
//                    }
//                }
//                $partner_payment_account_id = 0;
//                foreach ($transaction_amounts as $id => $total_amount) {
//                    if ($partner_payment_account_id == 0) {
//                        $partner_payment_account_id = $id;
//                    }
//                    if ($transaction_amounts[$partner_payment_account_id] > $total_amount) {
//                        $partner_payment_account_id = $id;
//                    }
//                }
//                return $partner_payment_account_id;
//            } else {
            return $partner_payment_account_info[0]['id'];
            //}
        }
        return false;
    }

    private static function _getPartnerPaymentAccountIdForTransactionInstallment($params)
    {
        // $partner_payment_id = \common\models\db\PartnerPayment::getIdByCode('ALEPAY');
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status ",
            'merchant_id' => $params['merchant_id'],
            'partner_payment_id' => $params['partner_payment_id'],
            'status' => PartnerPaymentAccount::STATUS_ACTIVE
        ]);
        if ($partner_payment_account_info != false) {
            // if (count($partner_payment_account_info) > 1) {
            //     $partner_payment_account_ids = array();
            //     $transaction_amounts = array();
            //     foreach ($partner_payment_account_info as $row) {
            //         $transaction_amounts[$row['id']] = 0;
            //         $partner_payment_account_ids[$row['id']] = $row['id'];
            //     }
            //     $today = getdate();
            //     $time_begin = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
            //     $time_end = $time_begin + 86400;
            //     $sql = "SELECT partner_payment_account_id, SUM(amount) AS total_amount "
            //             . "FROM transaction "
            //             . "WHERE partner_payment_account_id IN (" . implode(',', $partner_payment_account_ids) . ") "
            //             . "AND time_paid >= $time_begin "
            //             . "AND time_paid < $time_end "
            //             . "AND transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            //             . "AND status = " . Transaction::STATUS_PAID . " "
            //             . "GROUP BY partner_payment_account_id "
            //             . "ORDER BY total_amount ASC, partner_payment_account_id ASC ";
            //     $command = Transaction::getDb()->createCommand($sql);
            //     $result = $command->queryAll();
            //     if ($result != false) {
            //         foreach ($result as $row) {
            //             $transaction_amounts[$row['partner_payment_account_id']] = intval($row['total_amount']);
            //         }
            //     }
            //     $partner_payment_account_id = 0;
            //     foreach ($transaction_amounts as $id => $total_amount) {
            //         if ($partner_payment_account_id == 0) {
            //             $partner_payment_account_id = $id;
            //         }
            //         if ($transaction_amounts[$partner_payment_account_id] > $total_amount) {
            //             $partner_payment_account_id = $id;
            //         }
            //     }
            //     return $partner_payment_account_id;
            // } else {
            return $partner_payment_account_info[0]['id'];
            // }
        }
        return false;
    }

    private static function _getPartnerPaymentAccountIdForTransactionRefund($params)
    {
        $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id ", "id" => $params['refer_transaction_id']]);
        if ($payment_transaction_info != false) {
            return $payment_transaction_info['partner_payment_account_id'];
        }
        return false;
    }

    private static function _getPartnerPaymentAccountIdForTransactionWithdraw($params)
    {
        if (isset($params['partner_payment_account_id'])) {
            return $params['partner_payment_account_id'];
        }
        return false;
    }

    private static function _getPartnerPaymentAccountIdForTransactionDeposit($params)
    {
        if (isset($params['partner_payment_account_id'])) {
            return $params['partner_payment_account_id'];
        }
        return false;
    }

    /**
     *
     * @param params : transaction_type_id, merchant_id, checkout_order_id, cashout_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, amount, currency, refer_transaction_id, partner_payment_account_id, user_id
     * @param rollback
     */
    private static function _add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            $sender_account_id = self::_getSenderAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            $receiver_account_id = self::_getReceiverAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            if ($sender_account_id != false && $receiver_account_id != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :payment_method_id AND transaction_type_id = :transaction_type_id AND status = :status ", "payment_method_id" => $params['payment_method_id'], "transaction_type_id" => $params['transaction_type_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
                if ($payment_method_info != false) {
                    $data = self::_getTransactionAmountAndFee($params['transaction_type_id'], $params['amount'], $params['currency'], $merchant_info['id'], $params['payment_method_id'], $params['partner_payment_id'], $now, $params['card_owner_percent_instalment_fee'] ?? 0, $params['version'] ?? 1);
                    if ($data != false) {
                        $sender_account_balance = Account::getBalanceByAccountId($sender_account_id);
                        if ($sender_account_balance >= $data['amount']) {
                            $partner_payment_account_id = self::_getPartnerPaymentAccountId($params);
                            if ($partner_payment_account_id != false) {
                                $model = new Transaction();
                                $model->transaction_type_id = $params['transaction_type_id'];
                                $model->partner_id = $merchant_info['partner_id'];
                                $model->merchant_id = $merchant_info['id'];
                                $model->checkout_order_id = $params['checkout_order_id'];
                                $model->cashout_id = isset($params['cashout_id']) ? intval(@$params['cashout_id']) : "";
                                $model->payment_method_id = $params['payment_method_id'];
                                $model->partner_payment_id = $params['partner_payment_id'];
                                $model->partner_payment_method_refer_code = '';
                                $model->partner_payment_account_id = $partner_payment_account_id;
                                $model->amount = $data['amount'];
                                $model->sender_account_id = $sender_account_id;
                                $model->receiver_account_id = $receiver_account_id;
                                $model->sender_fee = $data['sender_fee'];
                                $model->receiver_fee = $data['receiver_fee'];
                                $model->partner_payment_sender_fee = $data['partner_payment_sender_fee'];
                                $model->partner_payment_receiver_fee = $data['partner_payment_receiver_fee'];
                                $model->currency = $params['currency'];
                                $model->refer_transaction_id = isset($params['refer_transaction_id']) ? intval(@$params['refer_transaction_id']) : "";
                                $model->status = Transaction::STATUS_NEW;
                                $model->time_created = time();
                                $model->time_updated = time();
                                $model->user_created = $params['user_id'];
                                $model->installment_conversion = isset($params['installment_conversion']) ? $params['installment_conversion'] : "";
                                $model->installment_fee = isset($params['installment_fee']) ? $params['installment_fee'] : "";
                                $model->installment_fee_merchant = isset($params['installment_fee_merchant']) ? $params['installment_fee_merchant'] : "";
                                $model->installment_fee_buyer = isset($params['installment_fee_buyer']) ? $params['installment_fee_buyer'] : "";
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $id = $model->getDb()->getLastInsertID();
                                        $all = true;
                                        if ($params['partner_payment_method_refer_code'] != '') {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                                            if ($result['error_message'] != '') {
                                                $all = false;
                                                $error_message = $result['error_message'];
                                            }
                                        }
                                        if ($all && TransactionType::isWithdrawTransactionType($params['transaction_type_id'])) {
                                            $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND type = :type ", "id" => intval(@$params['cashout_id']), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
                                            if ($cashout_info != false) {
                                                // dong bang so tien rut va fee tren tai khoan merchant
                                                $inputs = array(
                                                    'account_id' => $sender_account_id,
                                                    'currency' => $params['currency'],
                                                    'amount' => $data['amount'] + $data['sender_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                if ($result['error_message'] != '') {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            }
                                        }
                                        if ($all && TransactionType::isRefundTransactionType($params['transaction_type_id'])) {
                                            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => intval(@$params['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
                                            if ($payment_transaction_info != false) {
                                                // hoan phi ve merchant
                                                $inputs = array(
                                                    'account_id' => Account::getFeeAccountId($params['currency']),
                                                    'currency' => $params['currency'],
                                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                                if ($result['error_message'] == '') {
                                                    $inputs = array(
                                                        'account_id' => $sender_account_id,
                                                        'currency' => $params['currency'],
                                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                                        'user_id' => $params['user_id'],
                                                    );
                                                    $result = AccountBusiness::increaseBalance($inputs, false);
                                                    if ($result['error_message'] == '') {
                                                        // dong bang so tien thanh toan va phi hoan tien
                                                        $inputs = array(
                                                            'account_id' => $sender_account_id,
                                                            'currency' => $params['currency'],
                                                            'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $data['sender_fee'],
                                                            'user_id' => $params['user_id'],
                                                        );
                                                        $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                        if ($result['error_message'] != '') {
                                                            $all = false;
                                                            $error_message = $result['error_message'];
                                                        }
                                                    } else {
                                                        $all = false;
                                                        $error_message = $result['error_message'];
                                                    }
                                                } else {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            } else {
                                                $all = false;
                                                $error_message = 'Lỗi hệ thống';
                                            }
                                        }
                                        if ($all && TransactionType::isDepositTransactionType($params['transaction_type_id'])) {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'bank_refer_code' => $params['bank_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updateBankReferCode($inputs, false);
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
                                        $error_message = 'Có lỗi khi thêm giao dịch';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $error_message = 'Tài khoản kênh thanh toán không tồn tại';
                            }
                        } else {
                            $error_message = 'Số dư tài khoản không đủ để thực hiện giao dịch';
                        }
                    } else {
                        $error_message = 'Chưa cấu hình phí cho merchant hoặc kênh xử lý giao dịch';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không hợp lệ';
                }
            } else {
                $error_message = 'Tài khoản người chuyển và người nhận không tồn tại';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
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

    private static function _addForCallBack($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            $sender_account_id = self::_getSenderAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            $receiver_account_id = self::_getReceiverAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            if ($sender_account_id != false && $receiver_account_id != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :payment_method_id AND transaction_type_id = :transaction_type_id AND status = :status ", "payment_method_id" => $params['payment_method_id'], "transaction_type_id" => $params['transaction_type_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
                if ($payment_method_info != false) {
                    $data = self::_getTransactionAmountAndFee($params['transaction_type_id'], $params['amount'], $params['currency'], $merchant_info['id'], $params['payment_method_id'], $params['partner_payment_id'], $now);
                    if ($data != false) {
                        $sender_account_balance = Account::getBalanceByAccountId($sender_account_id);
                        if ($sender_account_balance >= $data['amount']) {
                            $partner_payment_account_id = self::_getPartnerPaymentAccountId($params);
                            if ($partner_payment_account_id != false) {
                                //----------
                                $model = new Transaction();
                                $model->transaction_type_id = $params['transaction_type_id'];
                                $model->partner_id = $merchant_info['partner_id'];
                                $model->merchant_id = $merchant_info['id'];
                                $model->checkout_order_id = $params['checkout_order_id'];
                                $model->cashout_id = isset($params['cashout_id']) ? intval(@$params['cashout_id']) : "";
                                $model->payment_method_id = $params['payment_method_id'];
                                $model->partner_payment_id = $params['partner_payment_id'];
                                $model->partner_payment_method_refer_code = '';
                                $model->partner_payment_account_id = $partner_payment_account_id;
                                $model->amount = $data['amount'];
                                $model->sender_account_id = $sender_account_id;
                                $model->receiver_account_id = $receiver_account_id;
                                $model->sender_fee = $data['sender_fee'];
                                $model->receiver_fee = $data['receiver_fee'];
                                $model->partner_payment_sender_fee = $data['partner_payment_sender_fee'];
                                $model->partner_payment_receiver_fee = $data['partner_payment_receiver_fee'];
                                $model->currency = $params['currency'];
                                $model->refer_transaction_id = intval(@$params['refer_transaction_id']);
                                $model->status = Transaction::STATUS_NEW;
                                $model->time_created = !empty($params['time_created']) ? $params['time_created'] : time();
                                $model->time_updated = time();
                                $model->user_created = $params['user_id'];
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $id = $model->getDb()->getLastInsertID();
                                        $all = true;
                                        if ($params['partner_payment_method_refer_code'] != '') {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                                            if ($result['error_message'] != '') {
                                                $all = false;
                                                $error_message = $result['error_message'];
                                            }
                                        }
                                        if ($all && TransactionType::isWithdrawTransactionType($params['transaction_type_id'])) {
                                            $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND type = :type ", "id" => intval(@$params['cashout_id']), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
                                            if ($cashout_info != false) {
                                                // dong bang so tien rut va fee tren tai khoan merchant
                                                $inputs = array(
                                                    'account_id' => $sender_account_id,
                                                    'currency' => $params['currency'],
                                                    'amount' => $data['amount'] + $data['sender_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                if ($result['error_message'] != '') {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            }
                                        }
                                        if ($all && TransactionType::isRefundTransactionType($params['transaction_type_id'])) {
                                            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => intval(@$params['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
                                            if ($payment_transaction_info != false) {
                                                // hoan phi ve merchant
                                                $inputs = array(
                                                    'account_id' => Account::getFeeAccountId($params['currency']),
                                                    'currency' => $params['currency'],
                                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                                if ($result['error_message'] == '') {
                                                    $inputs = array(
                                                        'account_id' => $sender_account_id,
                                                        'currency' => $params['currency'],
                                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                                        'user_id' => $params['user_id'],
                                                    );
                                                    $result = AccountBusiness::increaseBalance($inputs, false);
                                                    if ($result['error_message'] == '') {
                                                        // dong bang so tien thanh toan va phi hoan tien
                                                        $inputs = array(
                                                            'account_id' => $sender_account_id,
                                                            'currency' => $params['currency'],
                                                            'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $data['sender_fee'],
                                                            'user_id' => $params['user_id'],
                                                        );
                                                        $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                        if ($result['error_message'] != '') {
                                                            $all = false;
                                                            $error_message = $result['error_message'];
                                                        }
                                                    } else {
                                                        $all = false;
                                                        $error_message = $result['error_message'];
                                                    }
                                                } else {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            } else {
                                                $all = false;
                                                $error_message = 'Lỗi hệ thống';
                                            }
                                        }
                                        if ($all && TransactionType::isDepositTransactionType($params['transaction_type_id'])) {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'bank_refer_code' => $params['bank_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updateBankReferCode($inputs, false);
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
                                        $error_message = 'Có lỗi khi thêm giao dịch';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $error_message = 'Tài khoản kênh thanh toán không tồn tại';
                            }
                        } else {
                            $error_message = 'Số dư tài khoản không đủ để thực hiện giao dịch';
                        }
                    } else {
                        $error_message = 'Chưa cấu hình phí cho merchant hoặc kênh xử lý giao dịch';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không hợp lệ';
                }
            } else {
                $error_message = 'Tài khoản người chuyển và người nhận không tồn tại';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
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
     * @param params : transaction_type_id, merchant_id, checkout_order_id, cashout_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, amount, currency, refer_transaction_id, partner_payment_account_id, user_id
     * @param rollback
     */
    private static function _update($params, $rollback = true)
    {

        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            $sender_account_id = self::_getSenderAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            $receiver_account_id = self::_getReceiverAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            if ($sender_account_id != false && $receiver_account_id != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :payment_method_id AND transaction_type_id = :transaction_type_id AND status = :status ", "payment_method_id" => $params['payment_method_id'], "transaction_type_id" => $params['transaction_type_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
                if ($payment_method_info != false) {
                    $data = self::_getTransactionAmountAndFee($params['transaction_type_id'], $params['amount'], $params['currency'], $merchant_info['id'], $params['payment_method_id'], $params['partner_payment_id'], $now);
                    if ($data != false) {
                        $sender_account_balance = Account::getBalanceByAccountId($sender_account_id);
                        if ($sender_account_balance >= $data['amount']) {
                            $partner_payment_account_id = self::_getPartnerPaymentAccountId($params);
                            if ($partner_payment_account_id != false) {
                                //----------
                                $model = Transaction::findOne(['checkout_order_id' => $params['checkout_order_id']]);
                                $model->transaction_type_id = $params['transaction_type_id'];
                                $model->partner_id = $merchant_info['partner_id'];
                                $model->merchant_id = $merchant_info['id'];
                                $model->checkout_order_id = $params['checkout_order_id'];
                                $model->cashout_id = isset($params['cashout_id']) ? intval(@$params['cashout_id']) : "";
                                $model->payment_method_id = $params['payment_method_id'];
                                $model->partner_payment_id = $params['partner_payment_id'];
                                $model->partner_payment_method_refer_code = '';
                                $model->partner_payment_account_id = $partner_payment_account_id;
                                $model->amount = $data['amount'];
                                $model->sender_account_id = $sender_account_id;
                                $model->receiver_account_id = $receiver_account_id;
                                $model->sender_fee = $data['sender_fee'];
                                $model->receiver_fee = $data['receiver_fee'];
                                $model->partner_payment_sender_fee = $data['partner_payment_sender_fee'];
                                $model->partner_payment_receiver_fee = $data['partner_payment_receiver_fee'];
                                $model->currency = $params['currency'];
                                $model->refer_transaction_id = isset($params['refer_transaction_id']) ? intval(@$params['refer_transaction_id']) : "";
                                $model->status = Transaction::STATUS_NEW;
                                $model->time_created = time();
                                $model->time_updated = time();
                                $model->user_created = $params['user_id'];
                                $model->installment_conversion = isset($params['installment_conversion']) ? $params['installment_conversion'] : "";
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $id = $model->id;
                                        $all = true;
                                        if ($params['partner_payment_method_refer_code'] != '') {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                                            if ($result['error_message'] != '') {
                                                $all = false;
                                                $error_message = $result['error_message'];
                                            }
                                        }
                                        if ($all && TransactionType::isWithdrawTransactionType($params['transaction_type_id'])) {
                                            $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND type = :type ", "id" => intval(@$params['cashout_id']), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
                                            if ($cashout_info != false) {
                                                // dong bang so tien rut va fee tren tai khoan merchant
                                                $inputs = array(
                                                    'account_id' => $sender_account_id,
                                                    'currency' => $params['currency'],
                                                    'amount' => $data['amount'] + $data['sender_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                if ($result['error_message'] != '') {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            }
                                        }
                                        if ($all && TransactionType::isRefundTransactionType($params['transaction_type_id'])) {
                                            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :type AND status = :status ", "id" => intval(@$params['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
                                            if ($payment_transaction_info != false) {
                                                // hoan phi ve merchant
                                                $inputs = array(
                                                    'account_id' => Account::getFeeAccountId($params['currency']),
                                                    'currency' => $params['currency'],
                                                    'amount' => $payment_transaction_info['receiver_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                                if ($result['error_message'] == '') {
                                                    $inputs = array(
                                                        'account_id' => $sender_account_id,
                                                        'currency' => $params['currency'],
                                                        'amount' => $payment_transaction_info['receiver_fee'],
                                                        'user_id' => $params['user_id'],
                                                    );
                                                    $result = AccountBusiness::increaseBalance($inputs, false);
                                                    if ($result['error_message'] == '') {
                                                        // dong bang so tien thanh toan va phi hoan tien
                                                        $inputs = array(
                                                            'account_id' => $sender_account_id,
                                                            'currency' => $params['currency'],
                                                            'amount' => $payment_transaction_info['amount'] + $data['sender_fee'],
                                                            'user_id' => $params['user_id'],
                                                        );
                                                        $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                        if ($result['error_message'] != '') {
                                                            $all = false;
                                                            $error_message = $result['error_message'];
                                                        }
                                                    } else {
                                                        $all = false;
                                                        $error_message = $result['error_message'];
                                                    }
                                                } else {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            } else {
                                                $all = false;
                                                $error_message = 'Lỗi hệ thống';
                                            }
                                        }
                                        if ($all && TransactionType::isDepositTransactionType($params['transaction_type_id'])) {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'bank_refer_code' => $params['bank_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updateBankReferCode($inputs, false);
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
                                        $error_message = 'Có lỗi khi thêm giao dịch';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $error_message = 'Tài khoản kênh thanh toán không tồn tại';
                            }
                        } else {
                            $error_message = 'Số dư tài khoản không đủ để thực hiện giao dịch';
                        }
                    } else {
                        $error_message = 'Chưa cấu hình phí cho merchant hoặc kênh xử lý giao dịch';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không hợp lệ';
                }
            } else {
                $error_message = 'Tài khoản người chuyển và người nhận không tồn tại';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
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

    private static function _updateForCallBack($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            $sender_account_id = self::_getSenderAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            $receiver_account_id = self::_getReceiverAccountId($params['transaction_type_id'], $merchant_info, $params['currency']);
            if ($sender_account_id != false && $receiver_account_id != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :payment_method_id AND transaction_type_id = :transaction_type_id AND status = :status ", "payment_method_id" => $params['payment_method_id'], "transaction_type_id" => $params['transaction_type_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
                if ($payment_method_info != false) {
                    $data = self::_getTransactionAmountAndFee($params['transaction_type_id'], $params['amount'], $params['currency'], $merchant_info['id'], $params['payment_method_id'], $params['partner_payment_id'], $now);
                    if ($data != false) {
                        $sender_account_balance = Account::getBalanceByAccountId($sender_account_id);
                        if ($sender_account_balance >= $data['amount']) {
                            $partner_payment_account_id = self::_getPartnerPaymentAccountId($params);
                            if ($partner_payment_account_id != false) {
                                //----------
                                $model = Transaction::findOne(['checkout_order_id' => $params['checkout_order_id']]);
                                $model->transaction_type_id = $params['transaction_type_id'];
                                $model->partner_id = $merchant_info['partner_id'];
                                $model->merchant_id = $merchant_info['id'];
                                $model->checkout_order_id = $params['checkout_order_id'];
                                $model->cashout_id = isset($params['cashout_id']) ? intval(@$params['cashout_id']) : "";
                                $model->payment_method_id = $params['payment_method_id'];
                                $model->partner_payment_id = $params['partner_payment_id'];
                                $model->partner_payment_method_refer_code = '';
                                $model->partner_payment_account_id = $partner_payment_account_id;
                                $model->amount = $data['amount'];
                                $model->sender_account_id = $sender_account_id;
                                $model->receiver_account_id = $receiver_account_id;
                                $model->sender_fee = $data['sender_fee'];
                                $model->receiver_fee = $data['receiver_fee'];
                                $model->partner_payment_sender_fee = $data['partner_payment_sender_fee'];
                                $model->partner_payment_receiver_fee = $data['partner_payment_receiver_fee'];
                                $model->currency = $params['currency'];
                                $model->refer_transaction_id = intval(@$params['refer_transaction_id']);
                                $model->status = Transaction::STATUS_NEW;
                                $model->time_created = !empty($params['time_created']) ? $params['time_created'] : time();
                                $model->time_updated = time();
                                $model->user_created = $params['user_id'];
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $id = $model->id;
                                        $all = true;
                                        if ($params['partner_payment_method_refer_code'] != '') {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                                            if ($result['error_message'] != '') {
                                                $all = false;
                                                $error_message = $result['error_message'];
                                            }
                                        }
                                        if ($all && TransactionType::isWithdrawTransactionType($params['transaction_type_id'])) {
                                            $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND type = :type ", "id" => intval(@$params['cashout_id']), "type" => Cashout::TYPE_CHECKOUT_ORDER]);
                                            if ($cashout_info != false) {
                                                // dong bang so tien rut va fee tren tai khoan merchant
                                                $inputs = array(
                                                    'account_id' => $sender_account_id,
                                                    'currency' => $params['currency'],
                                                    'amount' => $data['amount'] + $data['sender_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                if ($result['error_message'] != '') {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            }
                                        }
                                        if ($all && TransactionType::isRefundTransactionType($params['transaction_type_id'])) {
                                            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :type AND status = :status ", "id" => intval(@$params['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
                                            if ($payment_transaction_info != false) {
                                                // hoan phi ve merchant
                                                $inputs = array(
                                                    'account_id' => Account::getFeeAccountId($params['currency']),
                                                    'currency' => $params['currency'],
                                                    'amount' => $payment_transaction_info['receiver_fee'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                                if ($result['error_message'] == '') {
                                                    $inputs = array(
                                                        'account_id' => $sender_account_id,
                                                        'currency' => $params['currency'],
                                                        'amount' => $payment_transaction_info['receiver_fee'],
                                                        'user_id' => $params['user_id'],
                                                    );
                                                    $result = AccountBusiness::increaseBalance($inputs, false);
                                                    if ($result['error_message'] == '') {
                                                        // dong bang so tien thanh toan va phi hoan tien
                                                        $inputs = array(
                                                            'account_id' => $sender_account_id,
                                                            'currency' => $params['currency'],
                                                            'amount' => $payment_transaction_info['amount'] + $data['sender_fee'],
                                                            'user_id' => $params['user_id'],
                                                        );
                                                        $result = AccountBusiness::increaseBalancePending($inputs, false);
                                                        if ($result['error_message'] != '') {
                                                            $all = false;
                                                            $error_message = $result['error_message'];
                                                        }
                                                    } else {
                                                        $all = false;
                                                        $error_message = $result['error_message'];
                                                    }
                                                } else {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            } else {
                                                $all = false;
                                                $error_message = 'Lỗi hệ thống';
                                            }
                                        }
                                        if ($all && TransactionType::isDepositTransactionType($params['transaction_type_id'])) {
                                            $inputs = array(
                                                'transaction_id' => $id,
                                                'bank_refer_code' => $params['bank_refer_code'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = self::updateBankReferCode($inputs, false);
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
                                        $error_message = 'Có lỗi khi thêm giao dịch';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $error_message = 'Tài khoản kênh thanh toán không tồn tại';
                            }
                        } else {
                            $error_message = 'Số dư tài khoản không đủ để thực hiện giao dịch';
                        }
                    } else {
                        $error_message = 'Chưa cấu hình phí cho merchant hoặc kênh xử lý giao dịch';
                    }
                } else {
                    $error_message = 'Phương thức thanh toán không hợp lệ';
                }
            } else {
                $error_message = 'Tài khoản người chuyển và người nhận không tồn tại';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
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

    private static function _getTransactionAmountAndFee($transaction_type_id, $amount, $currency, $merchant_id, $payment_method_id, $partner_payment_id, $time_request, $card_owner_percent_instalment_fee = 0, $version = 1)
    {
        if ($version == 3) {
            if (TransactionType::isInstallmentTransactionType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info != false) {
                    $sender_percent_fee = $merchant_fee_info['sender_percent_fee'] / 100;
                    $sender_flat_fee = $merchant_fee_info['sender_flat_fee'];
                    $instalment_fee = $card_owner_percent_instalment_fee / 100;
                    $denominator = 1 - $instalment_fee - $sender_percent_fee;

                    if ($denominator <= 0) {
                        throw new Exception("Lỗi: Mẫu số bằng hoặc nhỏ hơn 0, không thể tính toán.");
                    }

                    $sender_fee = ceil(($amount * $sender_percent_fee + $sender_flat_fee) / $denominator);
                    $receiver_fee = 0;
                    $payment_amount = $amount + $sender_fee;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFee($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFee($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            }
        } else {
            if (TransactionType::isWithdrawTransactionType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info != false) {
                    $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $amount);
                    $receiver_fee = 0;
                    $payment_amount = $amount;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFeeForWithdraw($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFeeForWithdraw($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            } elseif (TransactionType::isPaymentTransactionType($transaction_type_id) || TransactionType::isInstallmentTransactionType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info != false) {
                    $sender_fee = MerchantFee::getSenderFee($merchant_fee_info, $amount);
                    $receiver_fee = MerchantFee::getReceiverFee($merchant_fee_info, $amount);
                    $payment_amount = $amount + $sender_fee;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFee($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFee($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            } elseif (TransactionType::isRefundTransactionType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info != false) {
                    $sender_fee = MerchantFee::getSenderFeeForRefund($merchant_fee_info, $amount);
                    $receiver_fee = 0;
                    $payment_amount = $amount;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFeeForRefund($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFeeForRefund($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            } elseif (TransactionType::isDepositTransactionType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info != false) {
                    $sender_fee = MerchantFee::getSenderFee($merchant_fee_info, $amount);
                    $receiver_fee = MerchantFee::getReceiverFee($merchant_fee_info, $amount);
                    $payment_amount = $amount + $sender_fee;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info != false) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFee($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFee($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            } elseif (TransactionType::isWithdrawTransactionCardVoucherType($transaction_type_id)) {
                $merchant_fee_info = MerchantFee::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
                if ($merchant_fee_info) {
                    $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $amount);
                    $receiver_fee = MerchantFee::getReceiverFeeForWithdraw($merchant_fee_info, $amount);
                    $payment_amount = $amount;
                    $partner_payment_fee_info = PartnerPaymentFee::getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $payment_amount, $currency, $time_request);
                    if ($partner_payment_fee_info) {
                        $partner_payment_sender_fee = PartnerPaymentFee::getSenderFeeForWithdraw($partner_payment_fee_info, $payment_amount);
                        $partner_payment_receiver_fee = PartnerPaymentFee::getReceiverFeeForWithdraw($partner_payment_fee_info, $payment_amount);
                        return array(
                            'amount' => $amount,
                            'sender_fee' => $sender_fee,
                            'receiver_fee' => $receiver_fee,
                            'partner_payment_sender_fee' => $partner_payment_sender_fee,
                            'partner_payment_receiver_fee' => $partner_payment_receiver_fee,
                        );
                    }
                }
            }
        }
        return false;
    }

    /**
     *
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, amount, currency, user_id
     * @param type $rollback
     * @return type
     */
    public static function addPaymentTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND status IN (:status)", "id" => $params['checkout_order_id'], "status" => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
        if ($checkout_order_info != false) {
            if (empty($params['transaction_type_id'])) {
                $params['transaction_type_id'] = TransactionType::getPaymentTransactionTypeId();
            }
            $inputs = array(
                'version' => $params['version'],
                'transaction_type_id' => $params['transaction_type_id'],
                'merchant_id' => $checkout_order_info['merchant_id'],
                'checkout_order_id' => $checkout_order_info['id'],
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $params['amount'],
                'currency' => $params['currency'],
                'user_id' => $params['user_id'],
                'installment_conversion' => isset($params['installment_conversion']) ? $params['installment_conversion'] : "",
                'installment_fee' => isset($params['installment_fee']) ? $params['installment_fee'] : "",
                'installment_fee_merchant' => isset($params['installment_fee_merchant']) ? $params['installment_fee_merchant'] : '',
                'installment_fee_buyer' => isset($params['installment_fee_buyer']) ? $params['installment_fee_buyer'] : '',
                'card_owner_percent_instalment_fee' => isset($params['card_owner_percent_instalment_fee']) ? $params['card_owner_percent_instalment_fee'] : '',
            );
            $trans = Transaction::findOne(['checkout_order_id' => $checkout_order_info['id']]);
            if (!empty($trans) && $trans['status'] != Transaction::STATUS_CANCEL) {

                $result = self::_update($inputs, false);

            } else {

                $result = self::_add($inputs, false);

            }
            if ($result['error_message'] === '') {
                $error_message = '';
                $commit = true;
                $id = $result['id'];
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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

    //Áp dụng cho giao dịch được đồng bộ từ Ngân lượng về cổng

    public static function addPaymentTransactionForCallBack($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND status IN (:status)", "id" => $params['checkout_order_id'], "status" => [CheckoutOrder::STATUS_NEW, CheckoutOrder::STATUS_PAYING]]);
        if ($checkout_order_info != false) {
            if (empty($params['transaction_type_id'])) {
                $params['transaction_type_id'] = TransactionType::getPaymentTransactionTypeId();
            }
            $inputs = array(
                'transaction_type_id' => $params['transaction_type_id'],
                'merchant_id' => $checkout_order_info['merchant_id'],
                'checkout_order_id' => $checkout_order_info['id'],
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $params['amount'],
                'currency' => $params['currency'],
                'user_id' => $params['user_id'],
                'time_created' => $params['time_created'],
            );
            $trans = Transaction::findOne(['checkout_order_id' => $checkout_order_info['id']]);
            if (!empty($trans) && $trans['status'] != Transaction::STATUS_CANCEL) {
                $result = self::_updateForCallBack($inputs, false);
            } else {
                $result = self::_addForCallBack($inputs, false);
            }
            if ($result['error_message'] === '') {
                $error_message = '';
                $commit = true;
                $id = $result['id'];
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Đơn thanh toán không hợp lệ';
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
     * @param params : transaction_id, partner_payment_method_refer_code, user_id
     * @param rollback
     */
    static function updatePartnerPaymentMethodReferCode($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " ")->one();
        if ($model) {
            $model->partner_payment_method_refer_code = trim($params['partner_payment_method_refer_code']);
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'transaction_id' => $params['transaction_id'],
                        'partner_payment_refer_code' => $params['partner_payment_method_refer_code'],
                        'user_id' => $params['user_id'],
                    );
                    $result = PartnerPaymentReferCodeBusiness::add($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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
     * @param params : transaction_id, partner_payment_method_refer_code, partner_payment_info, user_id
     * @param rollback
     */
    static function paying($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findOne(["id" => $params['transaction_id'], "status" => [Transaction::STATUS_NEW, Transaction::STATUS_PAYING]]);
        if ($model) {
            $model->status = Transaction::STATUS_PAYING;
            $model->partner_payment_info = @$params['partner_payment_info'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $all = true;
                    if ($params['partner_payment_method_refer_code'] != $model->partner_payment_method_refer_code) {
                        $inputs = array(
                            'transaction_id' => $params['transaction_id'],
                            'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                            'user_id' => $params['user_id'],
                        );
                        $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                        if ($result['error_message'] != '') {
                            $all = false;
                            $error_message = $result['error_message'];
                        }
                    }
                    if ($all) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id) || TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'checkout_order_id' => $model->checkout_order_id,
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = CheckoutOrderBusiness::updateStatusPaying($inputs, false);
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
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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
     * @param params : transaction_id, reason_id, reason, user_id
     * @param rollback
     */
    static function cancel($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . ") ")->one();
        if ($model) {
            $model->status = Transaction::STATUS_CANCEL;
            $model->reason_id = @$params['reason_id'];
            $model->reason = @$params['reason'];
            $model->time_updated = time();
            $model->time_cancel = time();
            $model->user_cancel = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                        $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => intval($model->refer_transaction_id), "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
                        if ($payment_transaction_info != false) {
                            $inputs = array(
                                'account_id' => $model->sender_account_id,
                                'currency' => $model->currency,
                                'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $model->sender_fee,
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::decreaseBalancePending($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => $model->sender_account_id,
                                    'currency' => $model->currency,
                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                if ($result['error_message'] == '') {
                                    $inputs = array(
                                        'account_id' => Account::getFeeAccountId($model->currency),
                                        'currency' => $model->currency,
                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = 'Lỗi hệ thống';
                        }
                    } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'account_id' => $model->sender_account_id,
                            'currency' => $model->currency,
                            'amount' => $model->amount + $model->sender_fee,
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalancePending($inputs, false);
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
                    $error_message = 'Có lỗi khi hủy giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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

    static function failure($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql('SELECT * FROM transaction WHERE id = ' . $params['transaction_id'] . ' AND status IN (' . Transaction::STATUS_NEW . ',' . Transaction::STATUS_PAYING . ') ')->one();
        if ($model) {
            $model->status = Transaction::STATUS_FAILURE;
            $model->reason_id = @$params['reason_id'];
            $model->reason = @$params['reason'];
            $model->time_updated = time();
            $model->time_cancel = time();
            $model->user_cancel = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                        $payment_transaction_info = Tables::selectOneDataTable('transaction', ['id = :id AND transaction_type_id in (:type) AND status = :status ', 'id' => intval($model->refer_transaction_id), 'type' => TransactionType::getPaymentTransactionTypeId() . ',' . TransactionType::getInstallmentTransactionTypeId(), 'status' => Transaction::STATUS_PAID]);
                        if ($payment_transaction_info != false) {
                            $inputs = array(
                                'account_id' => $model->sender_account_id,
                                'currency' => $model->currency,
                                'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $model->sender_fee,
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::decreaseBalancePending($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => $model->sender_account_id,
                                    'currency' => $model->currency,
                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                if ($result['error_message'] == '') {
                                    $inputs = array(
                                        'account_id' => Account::getFeeAccountId($model->currency),
                                        'currency' => $model->currency,
                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = 'Lỗi hệ thống';
                        }
                    } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'account_id' => $model->sender_account_id,
                            'currency' => $model->currency,
                            'amount' => $model->amount + $model->sender_fee,
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalancePending($inputs, false);
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
                    $error_message = 'Có lỗi khi hủy giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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

    static function failureV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql('SELECT * FROM transaction WHERE id = ' . $params['transaction_id'] . ' AND status IN (' . Transaction::STATUS_NEW . ',' . Transaction::STATUS_PAYING . ',' . Transaction::STATUS_PAID . ') ')->one();
        if ($model) {
            $model->status = Transaction::STATUS_FAILURE;
            $model->reason_id = @$params['reason_id'];
            $model->reason = @$params['reason'];
            $model->time_updated = time();
            $model->time_cancel = time();
            $model->user_cancel = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                        $payment_transaction_info = Tables::selectOneDataTable('transaction', ['id = :id AND transaction_type_id in (:type) AND status = :status ', 'id' => intval($model->refer_transaction_id), 'type' => TransactionType::getPaymentTransactionTypeId() . ',' . TransactionType::getInstallmentTransactionTypeId(), 'status' => Transaction::STATUS_PAID]);
                        if ($payment_transaction_info != false) {
                            $inputs = array(
                                'account_id' => $model->sender_account_id,
                                'currency' => $model->currency,
                                'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $model->sender_fee,
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::decreaseBalancePending($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => $model->sender_account_id,
                                    'currency' => $model->currency,
                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                if ($result['error_message'] == '') {
                                    $inputs = array(
                                        'account_id' => Account::getFeeAccountId($model->currency),
                                        'currency' => $model->currency,
                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = 'Lỗi hệ thống';
                        }
                    } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'account_id' => $model->sender_account_id,
                            'currency' => $model->currency,
                            'amount' => $model->amount + $model->sender_fee,
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalancePending($inputs, false);
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
                    $error_message = 'Có lỗi khi hủy giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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


    static function revertQrVCBGateway($params, $rollback = true) // update thành công trên cổng trc, nếu MC trả về TB thì ms update lại cổng về TB
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql('SELECT * FROM transaction WHERE id = ' . $params['transaction_id'] . ' AND status = ' . Transaction::STATUS_PAID)->one();
        if ($model) {
            $model->status = Transaction::STATUS_REVERT;
            $model->reason_id = @$params['reason_id'];
            $model->reason = @$params['reason'];
            $model->time_updated = time();
            $model->time_cancel = time();
            $model->user_cancel = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                        $payment_transaction_info = Tables::selectOneDataTable('transaction', ['id = :id AND transaction_type_id in (:type) AND status = :status ', 'id' => intval($model->refer_transaction_id), 'type' => TransactionType::getPaymentTransactionTypeId() . ',' . TransactionType::getInstallmentTransactionTypeId(), 'status' => Transaction::STATUS_PAID]);
                        if ($payment_transaction_info != false) {
                            $inputs = array(
                                'account_id' => $model->sender_account_id,
                                'currency' => $model->currency,
                                'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $model->sender_fee,
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::decreaseBalancePending($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => $model->sender_account_id,
                                    'currency' => $model->currency,
                                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::decreaseBalance($inputs, false);
                                if ($result['error_message'] == '') {
                                    $inputs = array(
                                        'account_id' => Account::getFeeAccountId($model->currency),
                                        'currency' => $model->currency,
                                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['receiver_fee']),
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = 'Lỗi hệ thống';
                        }
                    } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'account_id' => $model->sender_account_id,
                            'currency' => $model->currency,
                            'amount' => $model->amount + $model->sender_fee,
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalancePending($inputs, false);
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
                    $error_message = 'Có lỗi khi hủy giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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
     * @param params : transaction_id, bank_refer_code, partner_payment_receiver_fee, user_id
     * @param rollback
     */
    static function updateBankReferCode($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " ")->one();
        if ($model) {
            $model->bank_refer_code = $params['bank_refer_code'];
            if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
            }
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi xác nhận giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
            }
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
     * @param params : transaction_id, time_paid, bank_refer_code, partner_payment_receiver_fee, user_id
     * @param rollback
     */

    static function paid($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            if ($model->status == Transaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $model->status = Transaction::STATUS_PAID;
                $model->bank_refer_code = trim($params['bank_refer_code']);
                if (isset($params['authorizationCode']) && $params['authorizationCode'] != null && $params['authorizationCode'] != '') {
                    $model->authorization_code = trim(@$params['authorizationCode']);
                }
                if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                    $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
                }
                if (isset($params['partner_code'])) {
                    $model->partnership_code = $params['partner_code'];
                }
                $model->time_updated = time();
                $model->time_paid = $params['time_paid'];
                $model->user_paid = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );

                            $result = self::_updateAccountBalanceForPaymentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'partner_payment_sender_fee' => $model->partner_payment_sender_fee,
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaid($inputs, false);
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                                'refund_rate' => $params['refund_rate']
                            );
                            $result = self::_updateAccountBalanceForRefundTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForWithdrawTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isDepositTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForDepositTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],

                            );
                            $result = self::_updateAccountBalanceForInstallmentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'month' => $params['month'],
                                    'payment_info' => $params['payment_info'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusWaitInstallment($inputs, false);
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
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không đúng';
                }
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

    static function paidOnusHasaki($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            if ($model->status == Transaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $model->status = Transaction::STATUS_PAID;
                $model->bank_refer_code = trim($params['bank_refer_code']);
                if (isset($params['authorizationCode']) && $params['authorizationCode'] != null && $params['authorizationCode'] != '') {
                    $model->authorization_code = trim(@$params['authorizationCode']);
                }
                if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                    $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
                }
                $model->time_updated = time();
                $model->time_paid = $params['time_paid'];
                $model->user_paid = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );

                            $result = self::_updateAccountBalanceForPaymentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'partner_payment_sender_fee' => $model->partner_payment_sender_fee,
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaid($inputs, false);
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                                'refund_rate' => $params['refund_rate']
                            );
                            $result = self::_updateAccountBalanceForRefundTransactionV2($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForWithdrawTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isDepositTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForDepositTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],

                            );
                            $result = self::_updateAccountBalanceForInstallmentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'month' => $params['month'],
                                    'payment_info' => $params['payment_info'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusWaitInstallment($inputs, false);
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
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không đúng';
                }
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

    static function paidVcbQrGateway($params, $rollback = true)
    {

        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            if ($model->status == Transaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $model->status = Transaction::STATUS_PAID;
                $model->bank_refer_code = trim($params['bank_refer_code']);
                if (isset($params['authorizationCode']) && $params['authorizationCode'] != null && $params['authorizationCode'] != '') {
                    $model->authorization_code = trim(@$params['authorizationCode']);
                }
                if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                    $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
                }
                $model->time_updated = time();
                $model->time_paid = $params['time_paid'];
                $model->user_paid = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );

                            $result = self::_updateAccountBalanceForPaymentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'partner_payment_sender_fee' => $model->partner_payment_sender_fee,
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaidVcbQrGateway($inputs, false);
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                                'refund_rate' => $params['refund_rate']
                            );
                            $result = self::_updateAccountBalanceForRefundTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForWithdrawTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isDepositTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForDepositTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],

                            );
                            $result = self::_updateAccountBalanceForInstallmentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'month' => $params['month'],
                                    'payment_info' => $params['payment_info'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusWaitInstallment($inputs, false);
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
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không đúng';
                }
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
     * @param params : transaction_id, time_paid, bank_refer_code, partner_payment_receiver_fee, user_id
     * @param rollback
     */
    static function review($params, $rollback = true)
    {

        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            if ($model->status == Transaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $model->status = Transaction::STATUS_PAID;
                $model->bank_refer_code = trim($params['bank_refer_code']);
                if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                    $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
                }
                $model->time_updated = time();
                $model->time_paid = !empty($params['time_paid']) ? $params['time_paid'] : time();
                $model->user_paid = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );

                            $result = self::_updateAccountBalanceForPaymentTransaction($inputs, false);

                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaid($inputs, false);
                                if ($result['error_message'] == '') {
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForRefundTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isWithdrawTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForWithdrawTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isDepositTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForDepositTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForInstallmentTransaction($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => $params['user_id'],
                                    'month' => $params['month'],
                                    'payment_info' => $params['payment_info'],
                                );
                                $result = CheckoutOrderBusiness::updateStatusReview($inputs, false);
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
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không đúng';
                }
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
     * @param params : transaction_id, time_paid, bank_refer_code, partner_payment_receiver_fee, user_id
     * @param rollback
     */
    public static function reviewCyberSource($params, $rollback = true)
    {

        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            if ($model->status == Transaction::STATUS_PAID) {
                $error_message = '';
                $commit = true;
            } else {
                $model->bank_refer_code = trim($params['bank_refer_code']);
                if (isset($params['partner_payment_receiver_fee']) && is_numeric($params['partner_payment_receiver_fee'])) {
                    $model->partner_payment_receiver_fee = $params['partner_payment_receiver_fee'];
                }
                $model->time_updated = time();
                $model->time_paid = !empty($params['time_paid']) ? $params['time_paid'] : time();
                $model->user_paid = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'checkout_order_id' => $model->checkout_order_id,
                                'transaction_id' => $model->id,
                                'sender_fee' => $model->sender_fee,
                                'receiver_fee' => $model->receiver_fee,
                                'time_paid' => $params['time_paid'],
                                'user_id' => $params['user_id'],
                            );
                            $result = CheckoutOrderBusiness::updateStatusReview($inputs, false);
                            if ($result['error_message'] == '') {
                                $error_message = '';
                                $commit = true;
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } elseif (TransactionType::isRefundTransactionType($model->transaction_type_id)) {
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => $params['user_id'],
                            );
                            $result = self::_updateAccountBalanceForRefundTransaction($inputs, false);
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
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không đúng';
                }
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
     * @param type $params : transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    private static function _updateAccountBalanceForPaymentTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getPaymentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            // giam tai khoan master
            $inputs = array(
                'account_id' => $transaction_info['sender_account_id'],
                'currency' => $transaction_info['currency'],
                'amount' => $transaction_info['amount'] + $transaction_info['sender_fee'],
                'user_id' => $params['user_id'],
            );
            $result = AccountBusiness::decreaseBalance($inputs, false);
            if ($result['error_message'] == '') {
                // tang tai khoan merchant
                $inputs = array(
                    'account_id' => $transaction_info['receiver_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $transaction_info['amount'] - $transaction_info['receiver_fee'] - $transaction_info['partner_payment_sender_fee'],
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::increaseBalance($inputs, false);
                if ($result['error_message'] == '') {
                    // tang tai khoan fee
                    $inputs = array(
                        'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                        'currency' => $transaction_info['currency'],
                        'amount' => $transaction_info['receiver_fee'] + $transaction_info['sender_fee'],
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                $error_message = $result['error_message'];
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
     * @param type $params : transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    private static function _updateAccountBalanceForWithdrawTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getWithdrawTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND type = :type ", "id" => $transaction_info['cashout_id'], "type" => Cashout::TYPE_CHECKOUT_ORDER]);
            if ($cashout_info != false) {
                // giam tai khoan merchant
                $inputs = array(
                    'account_id' => $transaction_info['sender_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $transaction_info['amount'] + $transaction_info['sender_fee'],
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::decreaseBalancePending($inputs, false);
                if ($result['error_message'] == '') {
                    $inputs = array(
                        'account_id' => $transaction_info['sender_account_id'],
                        'currency' => $transaction_info['currency'],
                        'amount' => $transaction_info['amount'] + $transaction_info['sender_fee'],
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::decreaseBalance($inputs, false);
                    if ($result['error_message'] == '') {
                        $inputs = array(
                            'account_id' => $transaction_info['receiver_account_id'],
                            'currency' => $transaction_info['currency'],
                            'amount' => $transaction_info['amount'],
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::increaseBalance($inputs, false);
                        if ($result['error_message'] == '') {
                            $inputs = array(
                                'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                                'currency' => $transaction_info['currency'],
                                'amount' => $transaction_info['sender_fee'],
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::increaseBalance($inputs, false);
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
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = '';
                $commit = true;
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
     * @param type $params : transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    private static function _updateAccountBalanceForRefundTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getRefundTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => intval($transaction_info['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
            if ($payment_transaction_info != false) {
                // giam tai khoan fee tăng mc
                $inputs = array(
                    'account_id' => $transaction_info['sender_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $transaction_info['sender_fee'] + doubleval($transaction_info['partner_payment_sender_fee']),
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::decreaseBalancePending($inputs, false);
                if ($result['error_message'] == '') {
                    // giảm tk mc
                    $inputs = array(
                        'account_id' => $transaction_info['sender_account_id'],
                        'currency' => $transaction_info['currency'],
                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $transaction_info['sender_fee'] + doubleval($transaction_info['partner_payment_sender_fee']),
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::decreaseBalance($inputs, false);
                    if ($result['error_message'] == '') {
                        // hoan phi sender_fee o giao dich thanh toan ( giảm tk fee)
                        $inputs = array(
                            'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                            'currency' => $transaction_info['currency'],
                            'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['sender_fee']) + $params['refund_rate'] * ((!empty($payment_transaction_info['installment_fee_buyer']) && $payment_transaction_info['installment_conversion'] == 1) ? $payment_transaction_info['installment_fee_buyer'] : 0),
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalance($inputs, false);
                        if ($result['error_message'] == '') {
                            // chuyen tien ve master (tăng tk master)
                            $inputs = array(
                                'account_id' => $transaction_info['receiver_account_id'],
                                'currency' => $transaction_info['currency'],
                                'amount' => $params['refund_rate'] * (doubleval($payment_transaction_info['amount']) + doubleval($payment_transaction_info['sender_fee']) + ((!empty($payment_transaction_info['installment_fee_buyer']) && $payment_transaction_info['installment_conversion'] == 1) ? $payment_transaction_info['installment_fee_buyer'] : 0)),
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::increaseBalance($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                                    'currency' => $transaction_info['currency'],
                                    'amount' => doubleval($transaction_info['sender_fee']),
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::increaseBalance($inputs, false);
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
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Lỗi hệ thống';
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


    private static function _updateAccountBalanceForRefundTransactionV2($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getRefundTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => intval($transaction_info['refer_transaction_id']), "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
            if ($payment_transaction_info != false) {
                // giam tai khoan merchant
                $inputs = array(
                    'account_id' => $transaction_info['sender_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $transaction_info['sender_fee'],
                    'user_id' => $params['user_id'],
                );
//                $result = AccountBusiness::decreaseBalancePending($inputs, false);
                $result = AccountBusiness::decreaseBalancePendingV2($inputs, false);
                if ($result['error_message'] == '') {
                    $inputs = array(
                        'account_id' => $transaction_info['sender_account_id'],
                        'currency' => $transaction_info['currency'],
                        'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['amount']) + $transaction_info['sender_fee'],
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::decreaseBalance($inputs, false);
                    if ($result['error_message'] == '') {
                        // hoan phi sender_fee o giao dich thanh toan
                        $inputs = array(
                            'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                            'currency' => $transaction_info['currency'],
                            'amount' => $params['refund_rate'] * doubleval($payment_transaction_info['sender_fee']),
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalance($inputs, false);
                        if ($result['error_message'] == '') {
                            // chuyen tien ve master
                            $inputs = array(
                                'account_id' => $transaction_info['receiver_account_id'],
                                'currency' => $transaction_info['currency'],
                                'amount' => $params['refund_rate'] * (doubleval($payment_transaction_info['amount']) + doubleval($payment_transaction_info['sender_fee'])),
                                'user_id' => $params['user_id'],
                            );
                            $result = AccountBusiness::increaseBalance($inputs, false);
                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                                    'currency' => $transaction_info['currency'],
                                    'amount' => $transaction_info['sender_fee'],
                                    'user_id' => $params['user_id'],
                                );
                                $result = AccountBusiness::increaseBalance($inputs, false);
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
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Lỗi hệ thống';
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
     * @param params : transaction_id, otp_transaction_id, otp_transaction_code, user_id
     * @param rollback
     */
    static function updateOtpInfo($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_PAID . "," . Transaction::STATUS_NOT_PAID . ") ")->one();
        if ($model) {
            $model->otp_transaction_id = $params['otp_transaction_id'];
            $model->otp_transaction_code = $params['otp_transaction_code'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi xác nhận giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
            }
        } else {
            $error_message = 'Giao dịch không tồn tại';
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
     * @param params : transaction_id, user_id
     * @param rollback
     */
    static function increaseOtpFailNumber($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_PAID . "," . Transaction::STATUS_NOT_PAID . ") ")->one();
        if ($model) {
            $otp_fail_number = intval($model->otp_fail_number) + 1;
            $model->otp_fail_number = $otp_fail_number;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($otp_fail_number >= $GLOBALS['OTP_TRANSACTION_MAX_FAIL_NUMBER']) {
                        $inputs = array(
                            'transaction_id' => $params['transaction_id'],
                            'reason_cancel' => 'Hủy giao dịch do nhập sai OTP quá ' . $GLOBALS['OTP_TRANSACTION_MAX_FAIL_NUMBER'] . ' lần',
                            'user_id' => $params['user_id'],
                        );
                        $result = self::cancelPaymentVoucher($inputs, false);
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
                    $error_message = 'Có lỗi khi xác nhận giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
            }
        } else {
            $error_message = 'Giao dịch không tồn tại';
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
     * @param type $params : cashout_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    public static function addWithdrawTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status", "id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_ACCEPT]);
        if ($cashout_info != false) {
            $inputs = array(
                'transaction_type_id' => TransactionType::getWithdrawTransactionTypeId(),
                'merchant_id' => $cashout_info['merchant_id'],
                'checkout_order_id' => 0,
                'cashout_id' => $params['cashout_id'],
                'payment_method_id' => $cashout_info['payment_method_id'],
                'partner_payment_id' => $cashout_info['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $cashout_info['amount'],
                'currency' => $cashout_info['currency'],
                'user_id' => $params['user_id'],
            );
            $result = self::_add($inputs, false);
            if ($result['error_message'] === '') {
                $id = $result['id'];
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
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
     * @param type $params : cashout_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    public static function addAndUpdatePayingByCashout($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $ids = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status", "id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_ACCEPT]);
        if ($cashout_info != false) {
            $all = true;
            $partner_payment_accounts = PartnerPaymentAccount::getPartnerPaymentAccountsForCashout($cashout_info);
            if ($partner_payment_accounts != false) {
                foreach ($partner_payment_accounts as $row) {
                    $inputs = array(
                        'transaction_type_id' => TransactionType::getWithdrawTransactionTypeId(),
                        'merchant_id' => $cashout_info['merchant_id'],
                        'checkout_order_id' => 0,
                        'cashout_id' => $params['cashout_id'],
                        'payment_method_id' => $cashout_info['payment_method_id'],
                        'partner_payment_id' => $cashout_info['partner_payment_id'],
                        'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                        'amount' => $row['amount'],
                        'currency' => $cashout_info['currency'],
                        'partner_payment_account_id' => $row['partner_payment_account_id'],
                        'user_id' => $params['user_id'],
                    );
                    $result = self::_add($inputs, false);
                    if ($result['error_message'] === '') {
                        $id = $result['id'];
                        $ids[] = $id;
                        $inputs = array(
                            'transaction_id' => $id,
                            'partner_payment_method_refer_code' => '',
                            'partner_payment_info' => '',
                            'user_id' => $params['user_id'],
                        );
                        $result = self::paying($inputs, false);
                        if ($result['error_message'] != '') {
                            $error_message = $result['error_message'];
                            $all = false;
                            break;
                        }
                    } else {
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
                $error_message = 'Số dư của một hoặc nhiều tài khoản trên kênh rút tiền đang không đủ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'ids' => $ids);
    }

    /**
     *
     * @param type $params : cashout_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    public static function cancelByCashout($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $ids = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status", "id" => $params['cashout_id'], "status" => Cashout::STATUS_REJECT]);
        if ($cashout_info != false) {
            $transaction_info = Tables::selectAllDataTable("transaction", ["cashout_id = :cashout_id AND transaction_type_id = :transaction_type_id AND status = :status",
                "cashout_id" => $params['cashout_id'],
                "transaction_type_id" => TransactionType::getWithdrawTransactionTypeId(),
                "status" => Transaction::STATUS_PAYING,
            ]);
            if ($transaction_info != false) {
                $all = true;
                foreach ($transaction_info as $row) {
                    $ids[] = $row['id'];
                    $inputs = array(
                        'transaction_id' => $row["id"],
                        'reason_id' => $params['reason_id'],
                        'reason' => $params['reason'],
                        'user_id' => $params['user_id'],
                    );
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
                $error_message = 'Dữ liệu không hợp lệ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'ids' => $ids);
    }

    /**
     *
     * @param type $params : cashout_id, time_paid, bank_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    public static function paidByCashout($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $ids = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $cashout_info = Tables::selectOneDataTable("cashout", ["id = :id AND status = :status", "id" => $params['cashout_id'], "status" => Cashout::STATUS_PAID]);
        if ($cashout_info != false) {
            $transaction_info = Tables::selectAllDataTable("transaction", ["cashout_id = :cashout_id AND transaction_type_id = :transaction_type_id AND status = :status",
                "cashout_id" => $params['cashout_id'],
                "transaction_type_id" => TransactionType::getWithdrawTransactionTypeId(),
                "status" => Transaction::STATUS_PAYING,
            ]);
            if ($transaction_info != false) {
                $all = true;
                foreach ($transaction_info as $row) {
                    $ids[] = $row['id'];
                    $bank_refer_code = $params['bank_refer_code'];
                    if (trim($row['bank_refer_code']) != '') {
                        $bank_refer_code = $row['bank_refer_code'];
                    }
                    $inputs = array(
                        'transaction_id' => $row["id"],
                        'time_paid' => $params['time_paid'],
                        'bank_refer_code' => $bank_refer_code,
                        'user_id' => $params['user_id'],
                    );
                    $result = self::paid($inputs, false);
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
                $error_message = 'Dữ liệu không hợp lệ';
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'ids' => $ids);
    }

    /**
     *
     * @param type $params : payment_transaction_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, currency, user_id
     * @param type $rollback
     * @return type
     */
    public static function addRefundTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $payment_transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id in (:type) AND status = :status ", "id" => $params['payment_transaction_id'], "type" => TransactionType::getPaymentTransactionTypeId() . "," . TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($payment_transaction_info != false) {
            $refund_rate = doubleval($params['refund_amount']) / doubleval($payment_transaction_info['amount']); // ti le so tien hoan
            $refund_transaction_amount = $refund_rate * (doubleval($payment_transaction_info['amount']) + doubleval($payment_transaction_info['sender_fee'])) + ((!empty($payment_transaction_info['installment_fee_buyer']) && $payment_transaction_info['installment_conversion'] == 1) ? doubleval($payment_transaction_info['installment_fee_buyer']) : 0);
            $inputs = array(
                'transaction_type_id' => TransactionType::getRefundTransactionTypeId(),
                'merchant_id' => $payment_transaction_info['merchant_id'],
                'checkout_order_id' => $payment_transaction_info['checkout_order_id'],
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $refund_transaction_amount,
                'currency' => $params['currency'],
                'refer_transaction_id' => $payment_transaction_info['id'],
                'user_id' => $params['user_id'],
                'refund_rate' => $refund_rate
            );
            $result = self::_add($inputs, false);
            if ($result['error_message'] === '') {
                $id = $result['id'];
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Giao dịch thanh toán muốn hoàn không hợp lệ';
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

    public static function addDepositTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        //$merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $params['merchant_id'], 'status' => Merchant::STATUS_ACTIVE]);
        // if ($merchant_info != false) {

        $inputs = array(
            'transaction_type_id' => TransactionType::getDepositTransactionTypeId(),
            'merchant_id' => $params['merchant_id'],
            'checkout_order_id' => 0,
            'cashout_id' => 0,
            'payment_method_id' => $params['payment_method_id'],
            'partner_payment_id' => $params['partner_payment_id'],
            'partner_payment_account_id' => $params['partner_payment_account_id'],
            'bank_refer_code' => $params['bank_refer_code'],
            'partner_payment_method_refer_code' => '',
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'user_id' => $params['user_id'],
        );

        $result = self::_add($inputs, false);
        if ($result['error_message'] === '') {
            $error_message = '';
            $commit = true;
            $id = $result['id'];
        } else {
            $error_message = $result['error_message'];
        }
        //} else {
        // $error_message = 'Merchant không hợp lệ';
        // }
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
     * @param type $params : transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    private static function _updateAccountBalanceForDepositTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getDepositTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            // giam tai khoan master
            $inputs = array(
                'account_id' => $transaction_info['sender_account_id'],
                'currency' => $transaction_info['currency'],
                'amount' => $transaction_info['amount'] + $transaction_info['sender_fee'],
                'user_id' => $params['user_id'],
            );
            $result = AccountBusiness::decreaseBalance($inputs, false);
            if ($result['error_message'] == '') {
                // tang tai khoan merchant
                $inputs = array(
                    'account_id' => $transaction_info['receiver_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $transaction_info['amount'] - $transaction_info['receiver_fee'],
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::increaseBalance($inputs, false);
                if ($result['error_message'] == '') {
                    // tang tai khoan fee
                    $inputs = array(
                        'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                        'currency' => $transaction_info['currency'],
                        'amount' => $transaction_info['receiver_fee'] + $transaction_info['sender_fee'],
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::increaseBalance($inputs, false);
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
                $error_message = $result['error_message'];
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

    private static function _updateAccountBalanceForInstallmentTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND transaction_type_id = :transaction_type_id AND status = :status", "id" => $params['transaction_id'], "transaction_type_id" => TransactionType::getInstallmentTransactionTypeId(), "status" => Transaction::STATUS_PAID]);
        if ($transaction_info != false) {
            // giam tai khoan master
            $inputs = array(
                'account_id' => $transaction_info['sender_account_id'],
                'currency' => $transaction_info['currency'],
                'amount' => $transaction_info['amount'] + $transaction_info['sender_fee'] + $transaction_info['installment_fee_buyer'],
                'user_id' => $params['user_id'],
            );
            $result = AccountBusiness::decreaseBalance($inputs, false);
            if ($result['error_message'] == '') {
                // tang tai khoan merchant
                $inputs = array(
                    'account_id' => $transaction_info['receiver_account_id'],
                    'currency' => $transaction_info['currency'],
                    'amount' => $transaction_info['amount'] - $transaction_info['receiver_fee'] - $transaction_info['partner_payment_sender_fee'] - $transaction_info['installment_fee_merchant'],
                    'user_id' => $params['user_id'],
                );
                $result = AccountBusiness::increaseBalance($inputs, false);

                if ($result['error_message'] == '') {
                    // tang tai khoan fee
                    $inputs = array(
                        'account_id' => Account::getFeeAccountId($transaction_info['currency']),
                        'currency' => $transaction_info['currency'],
                        'amount' => $transaction_info['receiver_fee'] + $transaction_info['sender_fee'] + $transaction_info['installment_fee_merchant'] + $transaction_info['installment_fee_buyer'],
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::increaseBalance($inputs, false);

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
                $error_message = $result['error_message'];
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

    static function updateReview($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findOne(["id" => $params['transaction_id'], "status" => [Transaction::STATUS_NEW, Transaction::STATUS_PAYING]]);
        if ($model) {
            $model->status = Transaction::STATUS_PAYING;
            $model->bank_refer_code = trim($params['bank_refer_code']);
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'checkout_order_id' => $model->checkout_order_id,
                            'user_id' => $params['user_id'],
                        );
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusReview($inputs, false);
                        if ($result['error_message'] == '') {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else if (TransactionType::isInstallmentTransactionType($model->transaction_type_id)) {
                        $inputs = array(
                            'checkout_order_id' => $model->checkout_order_id,
                            'user_id' => $params['user_id'],
                            'month' => $params['month'],
                            'payment_info' => $params['payment_info'],
                        );
                        $result = CheckoutOrderBusiness::updateCheckoutOrderStatusReview($inputs, false);
                        if ($result['error_message'] == '') {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = 'Giao dịch không hợp lệ';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật giao dịch';
                }
            } else {
                $error_message = 'Tham số đầu vào không đúng';
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


    public static function getTotalTransactionByMerchant($params)
    {
        $query = "select t.merchant_id, mc.name as merchant_name, count(t.id) as total_transaction, sum(t.amount) as total_amount "
            . "from transaction as t "
            . "join merchant as mc on t.merchant_id = mc.id "
            . "where t.status = " . Transaction::STATUS_PAID . " "
            . "and t.time_paid >= " . $params['time_paid_from'] . " "
            . "and t.time_paid <= " . $params['time_paid_to'] . " "
            . "and t.transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            . "group by t.merchant_id "
            . "order by t.merchant_id desc";
        $command = Transaction::getDb()->createCommand($query);
        $result = $command->queryAll();
        return $result;
    }

    public static function addWithdrawTransactionCardVoucher($params, $rollback = true): array
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $requirement_info = CardVoucherRequirement::find()
            ->where(['id' => $params['requirement_id']])
            ->andWhere(['status' => CardVoucherRequirement::STATUS_NEW])
            ->one();
        if ($requirement_info) {
            $inputs = array(
                'transaction_type_id' => TransactionType::getWithdrawTransactionCardVoucherTypeId(),
                'merchant_id' => $requirement_info->getCardVoucher()->one()->merchant_id,
                'checkout_order_id' => 0,
                'cashout_id' => 0,
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $requirement_info->amount,
                'currency' => $params['currency'],
                'user_id' => $params['user_id'],
                'account_id' => $params['account_id'],
                'requirement_id' => $requirement_info->id,
                'order_code' => $requirement_info->order_code,
                'refer_transaction_id' => '',
            );
            $result = self::_addForCardVoucher($inputs, false);
            if ($result['error_message'] === '') {
                $id = $result['id'];
                $error_message = '';
                $commit = true;
            } else {
                $error_message = $result['error_message'];
            }
        } else {
            $error_message = 'Phiếu chi không hợp lệ';
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

    private static function _addForCardVoucher($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status", "id" => $params['merchant_id'], "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :payment_method_id AND transaction_type_id = :transaction_type_id AND status = :status ", "payment_method_id" => $params['payment_method_id'], "transaction_type_id" => $params['transaction_type_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
            if ($payment_method_info) {
                $data = self::_getTransactionAmountAndFee($params['transaction_type_id'], $params['amount'], $params['currency'], $merchant_info['id'], $params['payment_method_id'], $params['partner_payment_id'], $now);
                if ($data) {
                    $partner_payment_account_id = self::_getPartnerPaymentAccountId($params);
                    if ($partner_payment_account_id) {
                        //----------
                        $model = new Transaction();
                        $model->transaction_type_id = $params['transaction_type_id'];
                        $model->partner_id = $merchant_info['partner_id'];
                        $model->merchant_id = $merchant_info['id'];
                        $model->checkout_order_id = $params['checkout_order_id'];
                        $model->cashout_id = intval(@$params['cashout_id']);
                        $model->payment_method_id = $params['payment_method_id'];
                        $model->partner_payment_id = $params['partner_payment_id'];
                        $model->partner_payment_method_refer_code = '';
                        $model->partner_payment_account_id = $partner_payment_account_id;
                        $model->amount = $data['amount'];
                        $model->sender_account_id = "0";
                        $model->receiver_account_id = $params['account_id'];
                        $model->sender_fee = $data['sender_fee'];
                        $model->receiver_fee = $data['receiver_fee'];
                        $model->partner_payment_sender_fee = $data['partner_payment_sender_fee'];
                        $model->partner_payment_receiver_fee = $data['partner_payment_receiver_fee'];
                        $model->currency = $params['currency'];
                        $model->refer_transaction_id = intval(@$params['refer_transaction_id']);
                        $model->status = Transaction::STATUS_PAID;
                        $model->time_created = time();
                        $model->time_updated = time();
                        $model->bank_refer_code = $params['order_code'];
                        $model->user_created = $params['user_id'];
                        $model->card_voucher_requirement_id = $params['requirement_id'];
                        if ($model->validate()) {
                            if ($model->save()) {
                                $id = $model->id;
                                $all = true;
                                if ($params['partner_payment_method_refer_code'] != '') {
                                    $inputs = array(
                                        'transaction_id' => $id,
                                        'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = self::updatePartnerPaymentMethodReferCode($inputs, false);
                                    if ($result['error_message'] != '') {
                                        $all = false;
                                        $error_message = $result['error_message'];
                                    }
                                }
                                if ($all && TransactionType::isWithdrawTransactionCardVoucherType($params['transaction_type_id'])) {
                                    $requirement_info = CardVoucherRequirement::findOne($model->card_voucher_requirement_id);
                                    if ($requirement_info) {
//                                        Cộng tài khoản phí
                                        $inputs = array(
                                            'account_id' => Account::getFeeCardVoucherAccountId($model->currency),
                                            'currency' => $params['currency'],
                                            'amount' => doubleval($data['receiver_fee']),
                                            'user_id' => $params['user_id'],
                                        );
                                        $result = AccountBusiness::increaseBalanceCardVoucher($inputs, false);
                                        // dong bang so tien rut va fee tren tai khoan merchant
                                        if ($result['error_message'] != '') {
                                            $all = false;
                                            $error_message = $result['error_message'];
                                        } else {
                                            $inputs = array(
                                                'account_id' => $params['account_id'],
                                                'currency' => $params['currency'],
                                                'amount' => $data['amount'] - $data['receiver_fee'],
                                                'user_id' => $params['user_id'],
                                            );
                                            $result = AccountBusiness::increaseBalanceCardVoucher($inputs, false);
                                            if ($result['error_message'] != '') {
                                                $all = false;
                                                $error_message = $result['error_message'];
                                            } else {
                                                $inputs = array(
                                                    'card_voucher_id' => $requirement_info->getCardVoucher()->one()->id,
                                                    'amount' => $data['amount'],
                                                    'user_id' => $params['user_id'],
                                                );
                                                $result = CardVoucherBusiness::decreaseBalanceFreezing($inputs, false);
                                                if ($result['error_message'] != '') {
                                                    $all = false;
                                                    $error_message = $result['error_message'];
                                                }
                                            }
                                        }

                                    }
                                }
                                if ($all) {
                                    $error_message = '';
                                    $commit = true;
                                }
                            } else {
                                $error_message = 'Có lỗi khi thêm giao dịch';
                            }
                        } else {
                            $error_message = 'Tham số đầu vào không hợp lệ';
                        }
                    } else {
                        $error_message = 'Tài khoản kênh thanh toán không tồn tại';
                    }

                } else {
                    $error_message = 'Chưa cấu hình phí cho merchant hoặc kênh xử lý giao dịch';
                }
            } else {
                $error_message = 'Phương thức thanh toán không hợp lệ';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    private static function _getPartnerPaymentAccountIdForTransactionCardVoucherWithdraw($params)
    {
        // $partner_payment_id = \common\models\db\PartnerPayment::getIdByCode('ALEPAY');
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status ",
            'merchant_id' => $params['merchant_id'],
            'partner_payment_id' => $params['partner_payment_id'],
            'status' => PartnerPaymentAccount::STATUS_ACTIVE
        ]);
        if ($partner_payment_account_info != false) {
            // if (count($partner_payment_account_info) > 1) {
            //     $partner_payment_account_ids = array();
            //     $transaction_amounts = array();
            //     foreach ($partner_payment_account_info as $row) {
            //         $transaction_amounts[$row['id']] = 0;
            //         $partner_payment_account_ids[$row['id']] = $row['id'];
            //     }
            //     $today = getdate();
            //     $time_begin = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
            //     $time_end = $time_begin + 86400;
            //     $sql = "SELECT partner_payment_account_id, SUM(amount) AS total_amount "
            //             . "FROM transaction "
            //             . "WHERE partner_payment_account_id IN (" . implode(',', $partner_payment_account_ids) . ") "
            //             . "AND time_paid >= $time_begin "
            //             . "AND time_paid < $time_end "
            //             . "AND transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            //             . "AND status = " . Transaction::STATUS_PAID . " "
            //             . "GROUP BY partner_payment_account_id "
            //             . "ORDER BY total_amount ASC, partner_payment_account_id ASC ";
            //     $command = Transaction::getDb()->createCommand($sql);
            //     $result = $command->queryAll();
            //     if ($result != false) {
            //         foreach ($result as $row) {
            //             $transaction_amounts[$row['partner_payment_account_id']] = intval($row['total_amount']);
            //         }
            //     }
            //     $partner_payment_account_id = 0;
            //     foreach ($transaction_amounts as $id => $total_amount) {
            //         if ($partner_payment_account_id == 0) {
            //             $partner_payment_account_id = $id;
            //         }
            //         if ($transaction_amounts[$partner_payment_account_id] > $total_amount) {
            //             $partner_payment_account_id = $id;
            //         }
            //     }
            //     return $partner_payment_account_id;
            // } else {
            return $partner_payment_account_info[0]['id'];
            // }
        }
        return false;

    }

    public static function getTransactionByMerchant($params)
    {
        $query = "select t.*,p.*,c.*, m.merchant_code as merchant_code, p.code as payment_method_code,t.status as transaction_status,t.time_created as transaction_create, t.time_paid as transaction_paid "
            . "from transaction as t "
            . "left join merchant as m on t.merchant_id = m.id "
            . "left join payment_method as p on t.payment_method_id = p.id "
            . "LEFT JOIN checkout_order AS c ON c.id = t.checkout_order_id "
            . "where m.id = " . $params['merchant_id'] . "  and  ((t.transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_PAID . " "
            . "and t.time_paid >= " . $params['time_from'] . " "
            . "and t.time_paid <= " . $params['time_to'] . ")"
            . " or (t.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_NEW . " "
            . "and t.time_created >= " . $params['time_from'] . " "
            . "and t.time_created <= " . $params['time_to'] . " ))"; // Loại bỏ refund vcb atm card ngày 14/09/2021 theo yêu cầu VH
        $page = (!empty($params['page'])) ? $params['page'] : 1;
        $size = (!empty($params['size'])) ? $params['size'] : 100;

//        if (@$_GET['debug'] == "duclm")
//            echo "<br>========" . $query . "========<br>";
        $total_result = count(Transaction::getDb()->createCommand($query)->queryAll());
        $total_page = ceil($total_result / $size);
        $offset = ($page - 1) * $size;
        $query_data = $query . " limit " . $offset . ',' . $size;
        $data = Transaction::getDb()->createCommand($query_data)->queryAll();

        return [
            'index' => [
                'size' => $size,
                'page' => $page,
                'total_page' => $total_page,
                'total_record' => $total_result,
            ],
            'data' => $data
        ];
    }

    public static function getTransactionRefundAll($params)
    {
        $query = "select t.*,p.*,c.*, p.code as payment_method_code,t.status as transaction_status,t.time_created as transaction_create, t.time_paid as transaction_paid "
            . "from transaction as t "
            . "left join payment_method as p on t.payment_method_id = p.id "
            . "LEFT JOIN checkout_order AS c ON c.id = t.checkout_order_id "
            . "where    (t.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
            . "and t.status = " . Transaction::STATUS_NEW . " "
            . "and t.time_created >= " . $params['time_from'] . " "
            . "and t.time_created <= " . $params['time_to'] . " )"; // Loại bỏ refund vcb atm card ngày 14/09/2021 theo yêu cầu VH
        $page = (!empty($params['page'])) ? $params['page'] : 1;
        $size = (!empty($params['size'])) ? $params['size'] : 100;

//        if (@$_GET['debug'] == "duclm")
//            echo "<br>========" . $query . "========<br>";
        $total_result = count(Transaction::getDb()->createCommand($query)->queryAll());
        $total_page = ceil($total_result / $size);
        $offset = ($page - 1) * $size;
        $query_data = $query . " limit " . $offset . ',' . $size;
        $data = Transaction::getDb()->createCommand($query_data)->queryAll();

        return [
            'index' => [
                'size' => $size,
                'page' => $page,
                'total_page' => $total_page,
                'total_record' => $total_result,
            ],
            'data' => $data
        ];
    }

    static function paidHandle($params, $rollback = true)
    {
        self::_writeLog('[' . __FUNCTION__ . '][START]');
        self::_writeLog('[' . __FUNCTION__ . '][INPUT][' . $params['transaction_id'] . ']' . json_encode($params));
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Transaction::getDb()->beginTransaction();
        }
        $model = Transaction::findBySql("SELECT * FROM transaction WHERE id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_FAILURE . "," . Transaction::STATUS_PAYING . "," . Transaction::STATUS_PAID . ") ")->one();
        if ($model) {
            self::_writeLog('[' . __FUNCTION__ . '][CONDITION-1]');
            if ($model->status == Transaction::STATUS_PAID) {
                self::_writeLog('[' . __FUNCTION__ . '][CONDITION-1.1]');
                $error_message = '';
                $commit = true;
            } else {
                self::_writeLog('[' . __FUNCTION__ . '][CONDITION-1.2]');
                $model->status = Transaction::STATUS_PAID;
                $model->bank_refer_code = @trim($params['bank_refer_code']);

                $model->time_updated = time();
                $model->time_paid = $params['time_paid'];
                $model->user_paid = 0;
                self::_writeLog('[' . __FUNCTION__ . '][CONDITION-2]');
                if ($model->validate()) {
                    self::_writeLog('[' . __FUNCTION__ . '][CONDITION-2][SUCCESS]');
                    self::_writeLog('[' . __FUNCTION__ . '][CONDITION-3]');
                    if ($model->save()) {

                        self::_writeLog('[' . __FUNCTION__ . '][CONDITION-3][SUCCESS]');
                        self::_writeLog('[' . __FUNCTION__ . '][CONDITION-4]');
                        if (TransactionType::isPaymentTransactionType($model->transaction_type_id)) {
                            self::_writeLog('[' . __FUNCTION__ . '][CONDITION-4][SUCCESS]');
                            $inputs = array(
                                'transaction_id' => $model->id,
                                'user_id' => 0,
                            );

                            $result = self::_updateAccountBalanceForPaymentTransaction($inputs, false);


                            if ($result['error_message'] == '') {
                                $inputs = array(
                                    'checkout_order_id' => $model->checkout_order_id,
                                    'transaction_id' => $model->id,
                                    'sender_fee' => $model->sender_fee,
                                    'receiver_fee' => $model->receiver_fee,
                                    'time_paid' => $params['time_paid'],
                                    'user_id' => 0,
                                );
                                $result = CheckoutOrderBusiness::updateStatusPaidHandle($inputs, false);
                                self::_writeLog('[' . __FUNCTION__ . '][CONDITION-5]');
                                if ($result['error_message'] == '') {
                                    self::_writeLog('[' . __FUNCTION__ . '][CONDITION-5][SUCCESS]');
                                    $error_message = '';
                                    $commit = true;
                                } else {
                                    $error_message = $result['error_message'];
                                    self::_writeLog('[' . __FUNCTION__ . '][CONDITION-5][ERROR]' . $error_message);
                                }
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = '';
                            $commit = true;
                        }
                    } else {
                        $error_message = 'Có lỗi khi xác nhận giao dịch';
                        self::_writeLog('[' . __FUNCTION__ . '][CONDITION-3][ERROR]' . $error_message);
                    }
                } else {
                    self::_writeLog('[' . __FUNCTION__ . '][CONDITION-2][ERROR]' . json_encode($model->getErrors()));
                    $error_message = 'Tham số đầu vào không đúng';
                }
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
        self::_writeLog('[' . __FUNCTION__ . '][END]' . json_encode($model->getAttributes()));
        return array('error_message' => $error_message);
    }

    private static function getPaymentMethodIgnore()
    {
        $payment_method_ignore = "";
        foreach (self::PAYMENT_METHOD_IGNORE as $payment_method) {
            $payment_method_ignore .= "'" . $payment_method . "'" . ",";
        }
        return rtrim($payment_method_ignore, ",");
    }

    private static function _writeLog($data, $breakLine = true, $addTime = true)
    {
        $file_name = 'transaction/' . date('Ymd') . '.txt';
        $pathinfo = pathinfo($file_name);
        Logs::create($pathinfo['dirname'], $pathinfo['basename'], $data);
    }

}
