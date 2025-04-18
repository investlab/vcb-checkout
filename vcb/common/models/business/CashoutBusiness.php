<?php

namespace common\models\business;

use Yii;
use common\models\db\Cashout;
use common\components\libs\Tables;
use common\models\db\PaymentMethod;
use common\models\db\Method;
use common\models\db\Bank;
use common\models\db\Merchant;
use common\models\db\Partner;
use common\models\db\MerchantFee;
use common\models\db\CheckoutOrder;
use common\models\db\CardTransaction;
use common\models\db\Account;

class CashoutBusiness {

    /**
     * 
     * @param type $params: merchant_id, items, user_id, 
     * @param array $items: payment_method_id, amount, currency, bank_account_code, bank_account_name, bank_account_branch, bank_card_month, bank_card_year, partner_payment_data
     * @param type $rollback
     */
    static function addMultiForCheckoutOrder($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        if (!empty($params['items'])) {
            $all = true;
            foreach ($params['items'] as $item) {
                $inputs = array(
                    'merchant_id' => $params['merchant_id'],
                    'payment_method_id' => $item['payment_method_id'],
                    'amount' => $item['amount'],
                    'currency' => $item['currency'],
                    'bank_account_code' => $item['bank_account_code'],
                    'bank_account_name' => $item['bank_account_name'],
                    'bank_account_branch' => $item['bank_account_branch'],
                    'bank_card_month' => $item['bank_card_month'],
                    'bank_card_year' => $item['bank_card_year'],
                    'partner_payment_data' => $item['partner_payment_data'],
                    'user_id' => $params['user_id'],
                );
                $result = self::addForCheckoutOrder($inputs, false);
                if ($result['error_message'] != '') {
                    $all = false;
                    $error_message = $result['error_message'];
                    break;
                }
            }
            if ($all) {
                $commit = true;
                $error_message = '';
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
        return array('error_message' => $error_message);
    }
    
    /**
     *
     * @param params : merchant_id, amount, payment_method_id, currency, bank_account_code, bank_account_name, bank_account_branch, bank_card_month, bank_card_year, partner_payment_data, user_id
     * @param rollback
     */
    static function addForCheckoutOrder($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $params['merchant_id'], 'status' => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id AND status = :status ", "id" => $params['payment_method_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
            if ($payment_method_info != false) {
                if ($params['amount'] >= $payment_method_info['min_amount']) {
                    $merchant_fee_info = MerchantFee::getPaymentFee($params['merchant_id'], $params['payment_method_id'], $params['amount'], $params['currency'], $now);
                    if ($merchant_fee_info != false) {
                        $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $params['amount']);
                        $account_balance = Account::getBalance($params['merchant_id'], $params['currency'], $account_info);
                        if ($account_balance > ($params['amount'] + $sender_fee)) {
                            if (Account::checkSystemTotalBalance()) {
                                $model = new Cashout();
                                $model->type = Cashout::TYPE_CHECKOUT_ORDER;
                                $model->partner_id = $merchant_info['partner_id'];
                                $model->merchant_id = $merchant_info['id'];
                                $model->time_begin = $now;
                                $model->time_end = $now;
                                $model->amount = $params['amount'];
                                $model->currency = $params['currency'];
                                $model->method_id = PaymentMethod::getMethodIdByPaymentMethodId($payment_method_info['id']);
                                $model->receiver_fee = $sender_fee;
                                $model->bank_id = $payment_method_info['bank_id'];
                                $model->payment_method_id = $payment_method_info['id'];
                                $model->bank_account_code = $params['bank_account_code'];
                                $model->bank_account_name = $params['bank_account_name'];
                                $model->bank_account_branch = $params['bank_account_branch'];
                                $model->bank_card_month = $params['bank_card_month'];
                                $model->bank_card_year = $params['bank_card_year'];
                                $model->partner_payment_data = @$params['partner_payment_data'];
                                $model->reference_code_merchant = @$params['reference_code_merchant'];
                                $model->status = Cashout::STATUS_VERIFY;
                                $model->time_created = $now;
                                $model->time_updated = $now;
                                $model->user_created = $params['user_id'];
                                if ($model->validate()) {
                                    if ($model->save()) {
                                        $id = $model->getDb()->getLastInsertID();
                                        $inputs = array(
                                            'account_id' => $account_info['id'],
                                            'currency' => $params['currency'],
                                            'amount' => $model->amount + $model->receiver_fee,
                                            'user_id' => $params['user_id'],
                                        );
                                        $result = AccountBusiness::increaseBalancePending($inputs, false);
                                        if ($result['error_message'] == '') {
                                            $error_message = '';
                                            $commit = true;
                                        } else {
                                            $error_message = $result['error_message'];
                                        }
                                    } else {
                                        $error_message = 'Có lỗi khi thêm phiếu chi';
                                    }
                                } else {
                                    $error_message = 'Tham số đầu vào không hợp lệ';
                                }
                            } else {
                                $error_message = 'Số dư tài khoản trên toàn hệ thống đang sai, yêu cầu rút tiền không thực hiện được';
                            }
                        } else {
                            $error_message = 'Số dư tài khoản không đủ để rút';
                        }
                    } else {
                        $error_message = 'Hệ thống chưa cấu hình phí rút tiền';
                    }
                } else {
                    $error_message = 'Số tiền yêu cầu rút đang nhỏ hơn số tiền tối thiểu để rút';
                }
            } else {
                $error_message = 'Phương thức rút tiền không hợp lệ';
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
     * @param params : merchant_id, time_begin, time_end, payment_method_id, currency, bank_account_code, bank_account_name, bank_account_branch, bank_card_month, bank_card_year, partner_payment_data, user_id
     * @param rollback
     */
    static function addForCardTransaction($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $now = time();
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $params['merchant_id'], 'status' => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            //$time_begin = Cashout::getTimeBegin($params['merchant_id']);
            if ($params['time_begin'] <= $params['time_end']) {
                if (CardTransaction::getTotalCardAmountForCashout($params['merchant_id'], $params['currency'], $params['time_begin'], $params['time_end'], $now) > 0) {
                    if (Cashout::checkAddCashout($params['merchant_id'], Cashout::TYPE_CARD_TRANSACTION)) {
                        $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id AND status = :status ", "id" => $params['payment_method_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
                        if ($payment_method_info != false) {
                            $model = new Cashout();
                            $model->type = Cashout::TYPE_CARD_TRANSACTION;
                            $model->partner_id = $merchant_info['partner_id'];
                            $model->merchant_id = $merchant_info['id'];
                            $model->time_begin = $params['time_begin'];
                            $model->time_end = $params['time_end'];
                            $model->amount = 0;
                            $model->currency = $params['currency'];
                            $model->method_id = PaymentMethod::getMethodIdByPaymentMethodId($payment_method_info['id']);
                            $model->bank_id = $payment_method_info['bank_id'];
                            $model->payment_method_id = $payment_method_info['id'];
                            $model->bank_account_code = $params['bank_account_code'];
                            $model->bank_account_name = $params['bank_account_name'];
                            $model->bank_account_branch = $params['bank_account_branch'];
                            $model->bank_card_month = $params['bank_card_month'];
                            $model->bank_card_year = $params['bank_card_year'];
                            $model->partner_payment_data = @$params['partner_payment_data'];
                            $model->status = Cashout::STATUS_NEW;
                            $model->time_created = $now;
                            $model->time_updated = $now;
                            $model->user_created = $params['user_id'];
                            if ($model->validate()) {
                                if ($model->save()) {
                                    $id = $model->getDb()->getLastInsertID();
                                    //-----
                                    $inputs = array(
                                        'merchant_id' => $merchant_info['id'],
                                        'time_begin' => $params['time_begin'],
                                        'time_end' => $params['time_end'],
                                        'time_request' => $now,
                                        'cashout_id' => $id,
                                        'currency' => $params['currency'],
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = CardTransactionBusiness::updateCashoutId($inputs, false);
                                    if ($result['error_message'] == '') {
                                        $cashout_amount = CheckoutOrder::getTotalCashoutAmountByCashoutId($id);
                                        if ($cashout_amount >= $payment_method_info['min_amount']) {
                                            $merchant_fee_info = MerchantFee::getPaymentFee($params['merchant_id'], $params['payment_method_id'], $cashout_amount, $params['currency'], $now);
                                            if ($merchant_fee_info != false) {
                                                $sender_fee = MerchantFee::getSenderFeeForWithdraw($merchant_fee_info, $cashout_amount);
                                                $model->amount = $cashout_amount;
                                                $model->receiver_fee = $sender_fee;
                                                if ($model->validate() && $model->save()) {
                                                    $commit = true;
                                                    $error_message = '';
                                                } else {
                                                    $error_message = 'Có lỗi khi thêm phiếu chi';
                                                }
                                            } else {
                                                $error_message = 'Hệ thống chưa cấu hình phí rút tiền';
                                            }
                                        } else {
                                            $error_message = 'Số tiền yêu cầu rút đang nhỏ hơn số tiền tối thiểu để rút';
                                        }
                                    } else {
                                        $error_message = $result['error_message'];
                                    }
                                } else {
                                    $error_message = 'Có lỗi khi thêm phiếu chi';
                                }
                            } else {
                                $error_message = 'Tham số đầu vào không hợp lệ';
                            }
                        } else {
                            $error_message = 'Phương thức rút tiền không hợp lệ';
                        }
                    } else {
                        $error_message = 'Hiện đã có một yêu cầu rút đang trong quá trình xử lý';
                    }
                } else {
                    $error_message = 'Không có đơn hàng phù hợp để rút tiền';
                }
            } else {
                $error_message = 'Thời gian bắt đầu không được lớn hơn thời gian kết thúc';
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
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusWaitVerify($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_NEW]);
        if ($model != null) {
            $model->status = Cashout::STATUS_WAIT_VERIFY;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'cashout_id' => $params['cashout_id'],
                        'user_id' => $params['user_id'],
                    );
                    $result = QueueNotifyBusiness::addNotifyEmailCashoutStatusWaitVerify($inputs, false);
                    if ($result['error_message'] == '') {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusVerify($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_VERIFY]);
        if ($model != null) {
            $model->status = Cashout::STATUS_VERIFY;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, partner_payment_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusWaitAccept($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $transaction_id = null;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_VERIFY]);
        if ($model != null) {
            $model->partner_payment_id = $params['partner_payment_id'];
            $model->status = Cashout::STATUS_WAIT_ACCEPT;
            $model->time_request = time();
            $model->time_updated = time();
            $model->user_request = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'merchant_id' => $model->merchant_id,
                        'currency' => $model->currency,
                        'amount' => $model->amount + $model->receiver_fee,
                        'user_id' => $params['user_id'],
                    );
                    $result = AccountBusiness::decreaseBalancePendingByMerchantId($inputs, false);
                    if ($result['error_message'] == '') {
                        $inputs = array(
                            'cashout_id' => $params['cashout_id'],
                            'partner_payment_method_refer_code' => '',
                            'user_id' => $params['user_id'],
                        );
                        $result = TransactionBusiness::addAndUpdatePayingByCashout($inputs, false);
                        if ($result['error_message'] == '') {
                            $transaction_ids = $result['ids'];
                            $transaction_info = Tables::selectAllDataTable("transaction", ["id IN(:ids)", "ids" => $transaction_ids]);
                            if ($transaction_info != false) {
                                $total_fee = 0;
                                foreach ($transaction_info as $row) {
                                    $total_fee += $row['sender_fee'] + $row['partner_payment_receiver_fee'];
                                }
                                //-------
                                $model->transaction_id = 0;
                                $model->receiver_fee = $total_fee;
                                if ($model->validate() && $model->save()) {
                                    $commit = true;
                                    $error_message = '';
                                } else {
                                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                                }
                            } else {
                                $error_message = 'Dữ liệu không hợp lệ';
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message, 'transaction_id' => $transaction_id);
    }

    /**
     *
     * @param type $params : cashout_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusReject($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_ACCEPT]);
        if ($model != null) {
            $model->reason_id = $params['reason_id'];
            $model->reason = $params['reason'];
            $model->status = Cashout::STATUS_REJECT;
            $model->time_reject = time();
            $model->time_updated = time();
            $model->user_reject = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $all = true;
                    if ($model->type == Cashout::TYPE_CARD_TRANSACTION) {
                        $inputs = array(
                            'cashout_id' => $params['cashout_id'],
                            'user_id' => $params['user_id'],
                        );
                        $result = CardTransactionBusiness::removeCashoutId($inputs, false);
                        if ($result['error_message'] != '') {
                            $all = false;
                            $error_message = $result['error_message'];
                        }
                    }
                    if ($all) {
                        $inputs = array(
                            'cashout_id' => $model->id,
                            'reason_id' => $params['reason_id'],
                            'reason' => $params['reason'],
                            'user_id' => $params['user_id'],
                        );
                        $result = TransactionBusiness::cancelByCashout($inputs, false);
                        if ($result['error_message'] == '') {
                            $inputs = array(
                                'cashout_id' => $params['cashout_id'],
                                'user_id' => $params['user_id'],
                            );
                            $result = QueueNotifyBusiness::addNotifyEmailCashoutStatusReject($inputs, false);
                            if ($result['error_message'] == '') {
                                $commit = true;
                                $error_message = '';
                            } else {
                                $error_message = $result['error_message'];
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, bank_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    /* static function updateBankReferCode($params, $rollback = true) {
      $error_message = 'Lỗi không xác định';
      $commit = false;
      if ($rollback) {
      $transaction = Cashout::getDb()->beginTransaction();
      }
      $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_ACCEPT]);
      if ($model != null) {
      $inputs = array(
      'transaction_id' => $model->transaction_id,
      'bank_refer_code' => $params['bank_refer_code'],
      'user_id' => $params['user_id'],
      );
      $result = TransactionBusiness::updateBankReferCode($inputs, false);
      if ($result['error_message'] == '') {
      $commit = true;
      $error_message = '';
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
      return array('error_message' => $error_message);
      } */

    /**
     *
     * @param type $params : cashout_id, bank_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusAccept($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_WAIT_ACCEPT]);
        if ($model != null) {
            $model->status = Cashout::STATUS_ACCEPT;
            $model->time_accept = time();
            $model->time_updated = time();
            $model->user_accept = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, time_paid, bank_refer_code, receiver_fee, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusAcceptAndPaid($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $result = self::updateStatusAccept($params, false);
        if ($result['error_message'] == '') {
            $result = self::updateStatusPaid($params, false);
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, time_paid, bank_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusPaid($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => Cashout::STATUS_ACCEPT]);
        if ($model != null) {
            $model->status = Cashout::STATUS_PAID;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            $model->reference_code = $params['bank_refer_code'];
            if ($model->validate()) {
                if ($model->save()) {
                    $all = true;
                    if ($model->type == Cashout::TYPE_CARD_TRANSACTION) {
                        $inputs = array(
                            'cashout_id' => $params['cashout_id'],
                            'user_id' => $params['user_id'],
                        );
                        $result = CardTransactionBusiness::updateStatusWithdrawByCashout($inputs, false);
                        if ($result['error_message'] != '') {
                            $error_message = $result['error_message'];
                            $all = false;
                        }
                    }
                    if ($all) {
                        $inputs = array(
                            'cashout_id' => $model->id,
                            'time_paid' => $params['time_paid'],
                            'bank_refer_code' => $params['bank_refer_code'],                           
                            'user_id' => $params['user_id'],
                        );
                        $result = TransactionBusiness::paidByCashout($inputs, false);
                        if ($result['error_message'] == '') {
                            $ids = $result['ids'];
                            $transaction_info = Tables::selectAllDataTable("transaction", ["id IN (:ids)", "ids" => $ids]);
                            if ($transaction_info != false) {
                                $total_receiver_fee = 0;
                                foreach ($transaction_info as $row) {
                                    $total_receiver_fee += $row['sender_fee'] + $row['partner_payment_receiver_fee'];
                                }
                                //-------
                                $model->receiver_fee = $total_receiver_fee;
                                if ($model->validate() && $model->save()) {
                                    $inputs = array(
                                        'cashout_id' => $params['cashout_id'],
                                        'user_id' => $params['user_id'],
                                    );
                                    $result = QueueNotifyBusiness::addNotifyEmailCashoutStatusPaid($inputs, false);
                                    if ($result['error_message'] == '') {
                                        $commit = true;
                                        $error_message = '';
                                    } else {
                                        $error_message = $result['error_message'];
                                    }
                                } else {
                                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                                }
                            } else {
                                $error_message = 'Dữ liệu không hợp lệ';
                            }
                        } else {
                            $error_message = $result['error_message'];
                        }
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : cashout_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusCancel($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = Cashout::getDb()->beginTransaction();
        }
        $model = Cashout::findOne(["id" => $params['cashout_id'], "status" => [Cashout::STATUS_NEW, Cashout::STATUS_WAIT_VERIFY, Cashout::STATUS_VERIFY]]);
        if ($model != null) {
            $model->reason_id = $params['reason_id'];
            $model->reason = $params['reason'];
            $model->status = Cashout::STATUS_CANCEL;
            $model->time_cancel = time();
            $model->time_updated = time();
            $model->user_cancel = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($model->type == Cashout::TYPE_CHECKOUT_ORDER) {
                        $inputs = array(
                            'merchant_id' => $model->merchant_id,
                            'currency' => $model->currency,
                            'amount' => $model->amount + $model->receiver_fee,
                            'user_id' => $params['user_id'],
                        );
                        $result = AccountBusiness::decreaseBalancePendingByMerchantId($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } elseif ($model->type == Cashout::TYPE_CARD_TRANSACTION) {
                        $inputs = array(
                            'cashout_id' => $params['cashout_id'],
                            'user_id' => $params['user_id'],
                        );
                        $result = CardTransactionBusiness::removeCashoutId($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật phiếu chi';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message);
    }

}
