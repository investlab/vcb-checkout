<?php

namespace common\models\business;

use Yii;
use common\models\db\CheckoutOrderBackup;
use common\components\libs\Tables;
use common\models\db\PaymentTransaction;
use common\models\db\Merchant;

class CheckoutOrderBackupBusiness {

    /**
     *
     * @param type $params : version, language_id, merchant_id, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function addAndRequestPayment($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $checkout_order_id = null;
        $payment_transaction_id = null;
        //-----------
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE, 'id' => $params['merchant_id']]);
        if ($merchant_info != false) {
            $inputs = array(
                'version' => $params['version'],
                'language_id' => $params['language_id'],
                'merchant_id' => $params['merchant_id'],
                'order_code' => $params['order_code'],
                'order_description' => $params['order_description'],
                'amount' => $params['amount'],
                'currency' => $params['currency'],
                'return_url' => $params['return_url'],
                'cancel_url' => $params['cancel_url'],
                'notify_url' => $params['notify_url'],
                'time_limit' => $params['time_limit'],
                'buyer_fullname' => $params['buyer_fullname'],
                'buyer_mobile' => $params['buyer_mobile'],
                'buyer_email' => $params['buyer_email'],
                'buyer_address' => $params['buyer_address'],
                'user_id' => $params['user_id'],
            );
            $result = self::add($inputs, false);
            if ($result['error_message'] == '') {
                $checkout_order_id = $result['id'];
                $inputs = array(
                    'checkout_order_id' => $checkout_order_id,
                    'payment_method_id' => $params['payment_method_id'],
                    'partner_payment_id' => $params['partner_payment_id'],
                    'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                    'user_id' => $params['user_id'],
                );
                $result = self::requestPayment($inputs, false);
                if ($result['error_message'] == '') {
                    $transaction_id = $result['transaction_id'];
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = $result['error_message'];
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
        return array('error_message' => $error_message, 'checkout_order_id' => $checkout_order_id, 'transaction_id' => $transaction_id);
    }

    /**
     *
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function requestPayment($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = :checkout_order_id AND status = " . CheckoutOrderBackup::STATUS_NEW . " ", array('checkout_order_id' => $params['checkout_order_id']))->one();
        if ($model != null) {
            $inputs = array(
                'checkout_order_id' => $model->id,
                'payment_method_id' => $params['payment_method_id'],
                'partner_payment_id' => $params['partner_payment_id'],
                'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                'amount' => $model->amount,
                'currency' => $model->currency,
                'user_id' => $params['user_id'],
            );
            $result = TransactionBusiness::addPaymentTransaction($inputs, false);
            if ($result['error_message'] == '') {
                $transaction_id = $result['id'];
                $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $transaction_id]);
                if ($transaction_info != false) {
                    //-------
                    $model->transaction_id = $transaction_id;
                    $model->cashin_amount = $model->amount + $transaction_info['sender_fee'];
                    $model->cashout_amount = $model->amount - $transaction_info['receiver_fee'];
                    $model->time_updated = time();
                    $model->user_updated = $params['user_id'];
                    if ($model->validate() && $model->save()) {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
                    }
                }
            } else {
                $error_message = $result['error_message'];
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
        return array('error_message' => $error_message, 'transaction_id' => $transaction_id);
    }

    /**
     *
     * @param type $params : checkout_order_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelRequestPayment($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = :checkout_order_id AND status = " . CheckoutOrderBackup::STATUS_PAYING . " ", array('checkout_order_id' => $params['checkout_order_id']))->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_CANCEL;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'transaction_id' => $model->transaction_id,
                    'reason_id' => $params['reason_id'],
                    'reason' => $params['reason'],
                    'user_id' => $params['user_id']
                );
                $result = TransactionBusiness::cancel($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : checkout_order_id, receipt, bin_code, card_type, request_token, user_id
     * @param type $rollback
     * @return type
     */
    static function reviewRequestPayment($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $payment_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = :checkout_order_id AND status = " . CheckoutOrderBackup::STATUS_PAYING . " ", array('checkout_order_id' => $params['checkout_order_id']))->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_REVIEW;
            $model->time_updated = time();
            if ($model->validate() && $model->save()) {
                $inputs = array(
                    'id' => $model->transaction_id,
                    'receipt' => $params['receipt'],
                    'bin_code' => $params['bin_code'],
                    'card_type' => $params['card_type'],
                    'request_token' => $params['request_token'],
                    'user_id' => $params['user_id']
                );
                $result = TransactionBusiness::review($inputs, false);
                if ($result['error_message'] == '') {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = $result['error_message'];
                }
            } else {
                $error_message = 'Có lỗi khi gửi yêu cầu thanh toán đơn hàng';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : version, language_id, merchant_id, order_code, order_description, amount, currency, return_url, cancel_url, notify_url, time_limit, buyer_fullname, buyer_email, buyer_mobile, buyer_address, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $token_code = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = " . Merchant::STATUS_ACTIVE . " ", 'id' => $params['merchant_id']]);
        if ($merchant_info != false) {
            $model = new CheckoutOrderBackup();
            $model->token_code = uniqid() . rand();
            $model->version = $params['version'];
            $model->language_id = $params['language_id'];
            $model->partner_id = $merchant_info['partner_id'];
            $model->merchant_id = $params['merchant_id'];
            $model->order_code = trim($params['order_code']);
            $model->order_description = $params['order_description'];
            $model->amount = $params['amount'];
            $model->currency = $params['currency'];
            $model->return_url = $params['return_url'];
            $model->cancel_url = $params['cancel_url'];
            $model->notify_url = $params['notify_url'];
            $model->time_limit = $params['time_limit'];
            $model->buyer_fullname = $params['buyer_fullname'];
            $model->buyer_email = $params['buyer_email'];
            $model->buyer_mobile = $params['buyer_mobile'];
            $model->buyer_address = $params['buyer_address'];
            $model->status = CheckoutOrderBackup::STATUS_NEW;
            $model->callback_status = CheckoutOrderBackup::CALLBACK_STATUS_NEW;
            $model->time_created = time();
            $model->time_updated = time();
            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $token_code = CheckoutOrderBackup::getTokenCode($id);
                    $model->token_code = $token_code;
                    if ($model->save()) {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = 'Có lỗi khi thêm yêu cầu thanh toán';
                    }
                } else {
                    $error_message = 'Có lỗi khi thêm yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
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
        return array('error_message' => $error_message, 'id' => $id, 'token_code' => $token_code);
    }

    /**
     *
     * @param type $params : checkout_order_id, transaction_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusPaying($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrderBackup::STATUS_NEW . "," . CheckoutOrderBackup::STATUS_PAYING . ") ")->one();
        if ($model != null) {
            $model->transaction_id = $params['transaction_id'];
            $model->status = CheckoutOrderBackup::STATUS_PAYING;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
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
     * @param type $params : checkout_order_id, transaction_id, sender_fee, receiver_fee, time_paid, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusPaid($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status = " . CheckoutOrderBackup::STATUS_PAYING)->one();
        if ($model != null) {
            $notify_url = trim($model->notify_url);
            //--------
            $model->transaction_id = $params['transaction_id'];
            $model->sender_fee = $params['sender_fee'];
            $model->receiver_fee = $params['receiver_fee'];
            $model->cashin_amount = $model->amount + $params['sender_fee'];
            $model->cashout_amount = $model->amount - $params['receiver_fee'];
            $model->status = CheckoutOrderBackup::STATUS_PAID;
            $model->callback_status = $notify_url != '' ? CheckoutOrderBackup::CALLBACK_STATUS_PROCESSING : CheckoutOrderBackup::CALLBACK_STATUS_ERROR;
            $model->time_paid = $params['time_paid'];
            $model->time_updated = time();
            $model->user_paid = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    if ($notify_url != '') {
                        $inputs = array(
                            'checkout_order_id' => $params['checkout_order_id'],
                            'notify_url' => $notify_url,
                            'time_process' => time(),
                        );
                        $result = CheckoutOrderCallbackBusiness::add($inputs, false);
                        if ($result['error_message'] == '') {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = $result['error_message'];
                        }
                    } else {
                        $commit = true;
                        $error_message = '';
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu không hợp lệ';
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
     * @param type $params : checkout_order_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusCancel($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrderBackup::STATUS_NEW . "," . CheckoutOrderBackup::STATUS_PAYING . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_CANCEL;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
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
     * @param type $params : checkout_order_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCallbackStatusError($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " ")->one();
        if ($model != null) {
            $model->callback_status = CheckoutOrderBackup::CALLBACK_STATUS_ERROR;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
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
     * @param type $params : checkout_order_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCallbackStatusSuccess($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " ")->one();
        if ($model != null) {
            $model->callback_status = CheckoutOrderBackup::CALLBACK_STATUS_SUCCESS;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
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
     * @param type $params : merchant_id, currency, time_begin, time_end, time_request, cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCashoutId($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order_backup "
            . "SET cashout_id = " . $params['cashout_id'] . ", "
            . "status = " . CheckoutOrderBackup::STATUS_WAIT_WIDTHDAW . ", "
            . "time_withdraw = " . $params['time_request'] . ", "
            . "user_withdraw = " . $params['user_id'] . " "
            . "WHERE merchant_id = " . $params['merchant_id'] . " "
            . "AND time_created >= " . $params['time_begin'] . " "
            . "AND time_created <= " . $params['time_end'] . " "
            . "AND currency = '" . $params['currency'] . "' "
            . "AND status = " . CheckoutOrderBackup::STATUS_PAID . " "
            . "AND cashout_id = 0 ";
        $command = CheckoutOrderBackup::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "INSERT INTO cashout_checkout_order(cashout_id, checkout_order_id, time_created, user_created) "
                . "SELECT cashout_id, id, " . $params['time_request'] . ", " . $params['user_id'] . " FROM checkout_order_backup "
                . "WHERE cashout_id = " . $params['cashout_id'] . " "
                . "AND status = " . CheckoutOrderBackup::STATUS_WAIT_WIDTHDAW;
            $command = CheckoutOrderBackup::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật phiếu chi';
            }
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
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
    static function removeCashoutId($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order_backup "
            . "SET cashout_id = " . 0 . ", "
            . "status = " . CheckoutOrderBackup::STATUS_PAID . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CheckoutOrderBackup::STATUS_WAIT_WIDTHDAW . " ";
        $command = CheckoutOrderBackup::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "DELETE FROM cashout_checkout_order WHERE cashout_id = " . $params['cashout_id'] . " ";
            $command = CheckoutOrderBackup::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật phiếu chi';
            }
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
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
    static function updateStatusWithdrawByCashout($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $sql = "UPDATE checkout_order_backup "
            . "SET status = " . CheckoutOrderBackup::STATUS_WIDTHDAW . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CheckoutOrderBackup::STATUS_WAIT_WIDTHDAW . " ";
        $command = CheckoutOrderBackup::getDb()->createCommand($sql);
        if ($command->execute()) {
            $error_message = '';
            $commit = true;
        } else {
            $error_message = 'Có lỗi khi cập nhật phiếu chi';
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
     * @param type $params : checkout_order_id, payment_method_id, partner_payment_id, partner_payment_method_refer_code, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusWaitRefund($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrderBackup::STATUS_PAID . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_WAIT_REFUND;
            $model->time_refund = time();
            $model->user_refund = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'payment_transaction_id' => $model->transaction_id,
                        'payment_method_id' => $params['payment_method_id'],
                        'partner_payment_id' => $params['partner_payment_id'],
                        'partner_payment_method_refer_code' => $params['partner_payment_method_refer_code'],
                        'currency' => $model->currency,
                        'user_id' => $params['user_id'],
                    );
                    $result = TransactionBusiness::addRefundTransaction($inputs, false);
                    if ($result['error_message'] == '') {
                        $refund_transaction_id = $result['id'];
                        $model->refund_transaction_id = $refund_transaction_id;
                        if ($model->validate() && $model->save()) {
                            $commit = true;
                            $error_message = '';
                        } else {
                            $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                        }
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu thanh toán không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
    }

    /**
     *
     * @param type $params : checkout_order_id, time_paid,  bank_refer_code, receiver_fee, user_id
     * @param type $rollback
     * @return type
     */
    static function updateStatusRefund($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrderBackup::STATUS_WAIT_REFUND . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_REFUND;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'transaction_id' => $model->refund_transaction_id,
                        'time_paid' => $params['time_paid'],
                        'bank_refer_code' => $params['bank_refer_code'],
                        'partner_payment_receiver_fee' => $params['receiver_fee'],
                        'user_id' => $params['user_id'],
                    );
                    $result = TransactionBusiness::paid($inputs, false);
                    if ($result['error_message'] == '') {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật yêu cầu hoàn tiền';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu hoàn tiền không hợp lệ';
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
     * @param type $params : checkout_order_id, reason_id, reason, user_id
     * @param type $rollback
     * @return type
     */
    static function cancelWaitRefund($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $refund_transaction_id = null;
        if ($rollback) {
            $transaction = CheckoutOrderBackup::getDb()->beginTransaction();
        }
        $model = CheckoutOrderBackup::findBySql("SELECT * FROM checkout_order_backup WHERE id = " . $params['checkout_order_id'] . " AND status IN (" . CheckoutOrderBackup::STATUS_WAIT_REFUND . ")")->one();
        if ($model != null) {
            $model->status = CheckoutOrderBackup::STATUS_PAID;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'transaction_id' => $model->refund_transaction_id,
                        'reason_id' => $params['reason_id'],
                        'reason' => $params['reason'],
                        'user_id' => $params['user_id'],
                    );
                    $result = TransactionBusiness::cancel($inputs, false);
                    if ($result['error_message'] == '') {
                        $commit = true;
                        $error_message = '';
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi hủy yêu cầu hoàn tiền';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Yêu cầu hoàn tiền không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'refund_transaction_id' => $refund_transaction_id);
    }

}
