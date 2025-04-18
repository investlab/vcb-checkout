<?php

namespace common\models\business;

use Yii;
use common\models\db\Transaction;
use common\models\db\Customer;
use common\components\libs\Tables;
use common\models\db\OtpTransaction;

class OtpTransactionBusiness
{

    private static function _getOTP($transaction_id)
    {
        return strtoupper(substr(md5($transaction_id . uniqid() . rand(1, 1000)), 0, $GLOBALS['OTP_TRANSACTION_LENGTH']));
    }

    /**
     *
     * @param params : transaction_id, customer_id, mobile time_limit, user_id
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        $otp = null;
        //------------
        if ($rollback) {
            $transaction = OtpTransaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", "id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NOT_PAID . "," . Transaction::STATUS_PAID . ")");
        if ($transaction_info != false) {
            $customer_info = Tables::selectOneDataTable("customer", "id = " . $transaction_info['customer_id'] . " AND status = " . Customer::STATUS_ACTIVE);
            if ($customer_info != false) {
                $otp = self::_getOTP($params['transaction_id']);
                $model = new OtpTransaction();
                $model->transaction_id = $params['transaction_id'];
                $model->customer_id = $customer_info['id'];
                $model->code = OtpTransaction::encryptOTP($otp);
                $model->mobile = $customer_info['mobile'];
                $model->time_limit = $params['time_limit'];
                $model->number = 0;
                $model->status = OtpTransaction::STATUS_ACTIVE;
                $model->time_created = time();
                $model->time_updated = time();
                $model->user_created = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $id = $model->getDb()->getLastInsertID();
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi thêm OTP';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Khách hàng không tồn tại hoặc đang bị khóa';
            }
        } else {
            $error_message = 'Giao dịch không tồn tại hoặc không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id, 'otp' => $otp);
    }

    /**
     *
     * @param params : transaction_id, user_id
     * @param rollback
     */
    static function addOtpVerifyTransactionPaymentVoucher($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = OtpTransaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", "id = " . $params['transaction_id'] . " AND status IN (" . Transaction::STATUS_NOT_PAID . "," . Transaction::STATUS_PAID . ")");
        if ($transaction_info != false) {
            $customer_info = Tables::selectOneDataTable("customer", "id = " . $transaction_info['customer_id'] . " AND status = " . Customer::STATUS_ACTIVE);
            if ($customer_info != false) {
                $inputs = array(
                    'transaction_id' => $params['transaction_id'],
                    'customer_id' => $customer_info['id'],
                    'mobile' => $customer_info['mobile'],
                    'time_limit' => time() + 15 * 60,
                    'user_id' => $params['user_id'],
                );
                $result = self::add($inputs, false);
                if ($result['error_message'] == '') {
                    $id = $result['id'];
                    $otp = $result['otp'];
                    $inputs = array(
                        'otp_transaction_id' => $id,
                        'otp' => $otp,
                        'user_id' => $params['user_id'],
                    );
                    $result = QueueNotifyBusiness::addNotifyOtpVerifyTransactionPaymentVoucher($inputs, false);
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
                $error_message = 'Khách hàng không tồn tại hoặc đang bị khóa';
            }
        } else {
            $error_message = 'Giao dịch không tồn tại hoặc không hợp lệ';
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
     * @param params : transaction_id, user_id
     * @param rollback
     */
    static function lockAllByTransaction($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = OtpTransaction::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", "id = " . $params['transaction_id'] . " ");
        if ($transaction_info != false) {
            $sql = "UPDATE " . OtpTransaction::tableName() . " SET "
                . "status = " . OtpTransaction::STATUS_LOCK . ", "
                . "time_updated = " . time() . ", "
                . "user_updated = " . $params['user_id'] . " "
                . "WHERE transaction_id = " . $params['transaction_id'] . " "
                . "AND status = " . OtpTransaction::STATUS_ACTIVE . " ";
            $connection = OtpTransaction::getDb();
            $command = $connection->createCommand($sql);
            $result = $command->execute();
            if ($result) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi khóa OTP';
            }
        } else {
            $error_message = 'Giao dịch không tồn tại hoặc không hợp lệ';
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
}