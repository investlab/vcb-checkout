<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */

namespace common\models\business;

use common\models\db\CardLog;
use common\models\db\CardLogBackup;
use common\models\db\CardTransaction;
use common\components\libs\Tables;
use Yii;

class CardTransactionBusiness
{

    /**
     *
     * @param type $params : card_log_id, user_id
     * @param type $rollback
     * @return type
     */
    static function addByCardLog($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = CardTransaction::getDb()->beginTransaction();
        }
        $now = time();
        $model_card_log = CardLog::findOne(["id" => $params['card_log_id'], "card_status" => CardLog::CARD_STATUS_SUCCESS, "transaction_status" => CardLog::TRANSACTION_STATUS_NEW]);
        if ($model_card_log == null) {
            $model_card_log = CardLogBackup::findOne(["id" => $params['card_log_id'], "card_status" => CardLog::CARD_STATUS_SUCCESS, "transaction_status" => CardLog::TRANSACTION_STATUS_NEW]);
        }
        if ($model_card_log != null) {
            $model_card_log->transaction_status = CardLog::TRANSACTION_STATUS_PROCESSING;
            $model_card_log->time_updated = $now;
            $model_card_log->user_updated = $params['user_id'];
            if ($model_card_log->validate() && $model_card_log->save()) {
                $sql = "INSERT INTO `card_transaction`(`id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `card_log_id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, `status`, `cashout_id`, `time_created`, `time_updated`, `time_withdraw`, `user_created`, `user_updated`, `user_withdraw`) "
                    . "SELECT `id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, " . CardTransaction::STATUS_NEW . ", 0, ".$now.", ".$now.", 0, " . $params['user_id'] . ", 0, 0 "
                    . "FROM " . $model_card_log->tableName() . " "
                    . "WHERE id = " . $model_card_log->id . " "
                    . "AND card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                    . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " ";
                $command = CardLog::getDb()->createCommand($sql);
                if ($command->execute()) {
                    $model_card_log->transaction_status = CardLog::TRANSACTION_STATUS_PROCESSED;
                    $model_card_log->card_transaction_id = $model_card_log->id;
                    $model_card_log->time_updated = $now;
                    $model_card_log->user_updated = $params['user_id'];
                    if ($model_card_log->validate() && $model_card_log->save()) {
                        $id = $model_card_log->id;
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = 'Có lỗi khi tạo giao dịch thẻ cào';
                    }
                } else {
                    $error_message = 'Có lỗi khi tạo giao dịch thẻ cào';
                }
            } else {
                $error_message = 'Có lỗi khi tạo giao dịch thẻ cào';
            }
        } else {
            $error_message = 'Log thẻ cào không tồn tại';
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
     * @param type $params : merchant_id, currency, time_begin, time_end, time_request, cashout_id, user_id
     * @param type $rollback
     * @return type
     */
    static function updateCashoutId($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CardTransaction::getDb()->beginTransaction();
        }
        $now = time();
        $sql = "UPDATE card_transaction "
                . "SET cashout_id = " . $params['cashout_id'] . ", "
                . "status = " . CardTransaction::STATUS_PROCESSING . ", "
                . "time_withdraw = " . $params['time_request'] . ", "
                . "user_withdraw = " . $params['user_id'] . " "
                . "WHERE merchant_id = " . $params['merchant_id'] . " "
                . "AND time_created >= " . $params['time_begin'] . " "
                . "AND time_created <= " . $params['time_end'] . " "
                . "AND withdraw_time_limit <= $now "
                . "AND currency = '" . $params['currency'] . "' "
                . "AND status = " . CardTransaction::STATUS_NEW . " "                
                . "AND cashout_id = 0 ";
        $command = CardTransaction::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "INSERT INTO cashout_card_transaction(cashout_id, card_transaction_id, time_created) "
                . "SELECT cashout_id, id, " . $params['time_request'] . " FROM card_transaction "
                . "WHERE cashout_id = " . $params['cashout_id'] . " "
                . "AND status = " . CardTransaction::STATUS_PROCESSING;
            $command = CardTransaction::getDb()->createCommand($sql);
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
    static function removeCashoutId($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CardTransaction::getDb()->beginTransaction();
        }
        $sql = "UPDATE card_transaction "
            . "SET cashout_id = " . 0 . ", "
            . "status = " . CardTransaction::STATUS_NEW . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CardTransaction::STATUS_PROCESSING . " ";
        $command = CardTransaction::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "DELETE FROM cashout_card_transaction WHERE cashout_id = " . $params['cashout_id'] . " ";
            $command = CardTransaction::getDb()->createCommand($sql);
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
    static function updateStatusWithdrawByCashout($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = CardTransaction::getDb()->beginTransaction();
        }
        $sql = "UPDATE card_transaction "
            . "SET status = " . CardTransaction::STATUS_WITHDRAW . ", "
            . "time_updated = " . time() . ", "
            . "user_updated = " . $params['user_id'] . " "
            . "WHERE cashout_id = " . $params['cashout_id'] . " "
            . "AND status = " . CardTransaction::STATUS_PROCESSING . " ";
        $command = CardTransaction::getDb()->createCommand($sql);
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
}
