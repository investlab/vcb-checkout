<?php

/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 11/03/2017
 * Time: 11:36 SA
 */

namespace console\controllers;

use yii\console\Controller;
use common\components\libs\Tables;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\models\db\AccountLog;
use common\models\db\Account;
use Yii;

class AccountLogController extends Controller {

    public function init() {
        parent::init();
        Yii::$app->setTimeZone('Asia/Ho_Chi_Minh');
    }

    /**
     * moi ngay chay 1 lan
     */
    public function actionProcess() {
        $this->_writeLog('[AccountLog::process] start');
        $yesterday = getdate(time() - 86400);
        $time_begin = mktime(0, 0, 0, $yesterday['mon'], $yesterday['mday'], $yesterday['year']);
        $time_end = $time_begin + 86400;
        $this->_addAccountLog($time_begin, $time_end);
        $account_log_ids = $this->_getAccountLogIds($time_end);        
        $this->_updateBalanceWithPaymentTransaction($account_log_ids, $time_begin, $time_end);
        $this->_updateBalanceWithWithdrawTransaction($account_log_ids, $time_begin, $time_end);
        $this->_updateBalanceWithRefundTransaction($account_log_ids, $time_begin, $time_end);
        $this->_writeLog('[AccountLog::process] end');
    }

    private static function _getAccountLogIds($time_end) {
        $account_ids = array();
        $account_log_info = Tables::selectAllDataTable("account_log", "time_created = $time_end ");
        if ($account_log_info != false) {
            foreach ($account_log_info as $row) {
                $account_ids[$row['account_id']] = $row['account_id'];
            }
        }
        return $account_ids;
    }
    
    private static function _addAccountLog($time_begin, $time_end) {
        $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                . "SELECT id, balance, balance_freezing, balance_pending, $time_end FROM account "
                . "WHERE time_created >= $time_begin AND time_created < $time_end ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->execute();
    }

    private static function _updateBalanceWithPaymentTransaction($account_log_ids, $time_begin, $time_end) {
        $fee_account_id = Account::getFeeAccountId($GLOBALS['CURRENCY']['VND']);
        $master_account_id = Account::getMasterAccountId($GLOBALS['CURRENCY']['VND']);
        // thanh toan trong ngay
        $sql = "SELECT receiver_account_id, SUM(amount - receiver_fee) AS increase_balance_receiver, SUM(amount + sender_fee) AS decrease_balance_sender, SUM(sender_fee + receiver_fee) AS increase_balance_fee "
                . "FROM transaction "
                . "WHERE transaction_type_id = " . TransactionType::getPaymentTransactionTypeId() . " "
                . "AND time_paid >= $time_begin "
                . "AND time_paid < $time_begin "
                . "AND status = " . Transaction::STATUS_PAID . " "
                . "GROUP BY receiver_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            $decrease_master_balance = 0;
            $increase_fee_balance = 0;
            foreach ($result as $row) {
                $decrease_master_balance+= $row['decrease_balance_sender'];
                $increase_fee_balance+= $row['increase_balance_fee'];
                if (isset($account_log_ids[$row['receiver_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $row['increase_balance_receiver'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['receiver_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $row['increase_balance_receiver'] . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['receiver_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
            if (isset($account_log_ids[$master_account_id])) {
                // update balance master
                $sql = "UPDATE `account_log` "
                        . "SET balance = balance - " . $decrease_master_balance . " "
                        . "WHERE account_id = " . $account_log_ids[$master_account_id] . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            } else {
                // update balance master
                $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                        . "SELECT account_id, balance - " . $decrease_master_balance . ", balance_freezing, balance_pending, $time_end "
                        . "FROM account_log "
                        . "WHERE account_id = " . $master_account_id . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            }
            if ($increase_fee_balance > 0) {
                if (isset($account_log_ids[$fee_account_id])) {
                    // update balance master
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $increase_fee_balance . " "
                            . "WHERE account_id = " . $account_log_ids[$fee_account_id] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                } else {
                    // update balance fee
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $increase_fee_balance . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $fee_account_id . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
    }

    private static function _updateBalanceWithWithdrawTransaction($account_log_ids, $time_begin, $time_end) {
        $fee_account_id = Account::getFeeAccountId($GLOBALS['CURRENCY']['VND']);
        $master_account_id = Account::getMasterAccountId($GLOBALS['CURRENCY']['VND']);
        // tao trong ngay
        $sql = "SELECT sender_account_id, SUM(amount + sender_fee) AS decrease_balance_sender "
                . "FROM transaction "
                . "WHERE transaction_type_id = " . TransactionType::getWithdrawTransactionTypeId() . " "
                . "AND time_created >= $time_begin "
                . "AND time_created < $time_begin "
                . "AND status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . ") "
                . "GROUP BY sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            foreach ($result as $row) {
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance - " . $row['decrease_balance_sender'] . ", "
                            . "balance_pending = balance_pending + " . $row['decrease_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance - " . $row['decrease_balance_sender'] . ", balance_freezing, balance_pending + " . $row['decrease_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
        // huy trong ngay
        $sql = "SELECT sender_account_id, SUM(amount + sender_fee) AS decrease_balance_sender "
                . "FROM transaction "
                . "WHERE transaction_type_id = " . TransactionType::getWithdrawTransactionTypeId() . " "
                . "AND time_cancel >= $time_begin "
                . "AND time_cancel < $time_begin "
                . "AND status = " . Transaction::STATUS_CANCEL . " "
                . "GROUP BY sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            foreach ($result as $row) {
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $row['decrease_balance_sender'] . ", "
                            . "balance_pending = balance_pending - " . $row['decrease_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $row['decrease_balance_sender'] . ", balance_freezing, balance_pending - " . $row['decrease_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
        // thanh toan trong ngay        
        $sql = "SELECT sender_account_id, SUM(amount - receiver_fee) AS increase_balance_receiver, SUM(amount + sender_fee) AS decrease_balance_sender, SUM(sender_fee + receiver_fee) AS increase_balance_fee "
                . "FROM transaction "
                . "WHERE transaction_type_id = " . TransactionType::getWithdrawTransactionTypeId() . " "
                . "AND time_paid >= $time_begin "
                . "AND time_paid < $time_begin "
                . "AND status = " . Transaction::STATUS_PAID . " "
                . "GROUP BY sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            $increase_master_balance = 0;
            $increase_fee_balance = 0;
            foreach ($result as $row) {
                $increase_master_balance+= $row['increase_balance_receiver'];
                $increase_fee_balance+= $row['increase_balance_fee'];
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance - " . $row['decrease_balance_sender'] . ", "
                            . "balance_pending = balance_pending - " . $row['decrease_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance - " . $row['decrease_balance_sender'] . ", balance_freezing, balance_pending - " . $row['decrease_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
            if (isset($account_log_ids[$master_account_id])) {
                // update balance master
                $sql = "UPDATE `account_log` "
                        . "SET balance = balance + " . $increase_master_balance . " "
                        . "WHERE account_id = " . $account_log_ids[$master_account_id] . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            } else {
                // update balance master
                $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                        . "SELECT account_id, balance + " . $increase_master_balance . ", balance_freezing, balance_pending, $time_end "
                        . "FROM account_log "
                        . "WHERE account_id = " . $master_account_id . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            }
            if ($increase_fee_balance > 0) {
                if (isset($account_log_ids[$fee_account_id])) {
                    // update balance master
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $increase_fee_balance . " "
                            . "WHERE account_id = " . $account_log_ids[$fee_account_id] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                } else {
                    // update balance fee
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $increase_fee_balance . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $fee_account_id . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
    }

    private static function _updateBalanceWithRefundTransaction($account_log_ids, $time_begin, $time_end) {
        $fee_account_id = Account::getFeeAccountId($GLOBALS['CURRENCY']['VND']);
        $master_account_id = Account::getMasterAccountId($GLOBALS['CURRENCY']['VND']);
        // tao trong ngay
        $sql = "SELECT transaction.sender_account_id, SUM(payment_transaction.receiver_fee) AS decrease_balance_fee, SUM(payment_transaction.amount + transaction.sender_fee) AS decrease_balance_sender "
                . "FROM transaction, transaction AS payment_transaction "
                . "WHERE payment_transaction.id = transaction.refer_transaction_id "
                . "AND transaction.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "AND transaction.time_created >= $time_begin "
                . "AND transaction.time_created < $time_begin "
                . "AND transaction.status IN (" . Transaction::STATUS_NEW . "," . Transaction::STATUS_PAYING . ") "
                . "GROUP BY transaction.sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            $decrease_fee_balance = 0;
            foreach ($result as $row) {
                $decrease_fee_balance+= $row['decrease_balance_fee'];
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $row['decrease_balance_fee'] . " - " . $row['decrease_balance_sender'] . ", "
                            . "balance_pending = balance_pending + " . $row['decrease_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $row['decrease_balance_fee'] . " - " . $row['decrease_balance_sender'] . ", balance_freezing, balance_pending + " . $row['decrease_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
            if ($decrease_fee_balance > 0) {
                if (isset($account_log_ids[$fee_account_id])) {
                    // update balance master
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance - " . $decrease_fee_balance . " "
                            . "WHERE account_id = " . $account_log_ids[$fee_account_id] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                } else {
                    // update balance fee
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance - " . $decrease_fee_balance . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $fee_account_id . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
        // huy trong ngay
        $sql = "SELECT transaction.sender_account_id, SUM(payment_transaction.receiver_fee) AS increase_balance_fee, SUM(payment_transaction.amount + transaction.sender_fee) AS increase_balance_sender "
                . "FROM transaction, transaction AS payment_transaction "
                . "WHERE payment_transaction.id = transaction.refer_transaction_id "
                . "AND transaction.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "AND transaction.time_cancel >= $time_begin "
                . "AND transaction.time_cancel < $time_begin "
                . "AND transaction.status = " . Transaction::STATUS_CANCEL . " "
                . "GROUP BY transaction.sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            $increase_fee_balance = 0;
            foreach ($result as $row) {
                $increase_fee_balance+= $row['increase_balance_fee'];
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance - " . $row['increase_balance_fee'] . " + " . $row['increase_balance_sender'] . ", "
                            . "balance_pending = balance_pending - " . $row['increase_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance - " . $row['increase_balance_fee'] . " + " . $row['increase_balance_sender'] . ", balance_freezing, balance_pending - " . $row['increase_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
            if ($increase_fee_balance > 0) {
                if (isset($account_log_ids[$fee_account_id])) {
                    // update balance master
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $increase_fee_balance . " "
                            . "WHERE account_id = " . $account_log_ids[$fee_account_id] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                } else {
                    // update balance fee
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $increase_fee_balance . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $fee_account_id . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
        // thanh toan trong ngay        
        $sql = "SELECT transaction.sender_account_id, SUM(transaction.sender_fee - payment_transaction.sender_fee) AS increase_balance_fee, SUM(payment_transaction.amount + transaction.sender_fee) AS decrease_balance_sender, SUM(payment_transaction.amount + payment_transaction.sender_fee) AS increase_balance_receiver "
                . "FROM transaction, transaction AS payment_transaction "
                . "WHERE payment_transaction.id = transaction.refer_transaction_id "
                . "AND transaction.transaction_type_id = " . TransactionType::getRefundTransactionTypeId() . " "
                . "AND transaction.time_paid >= $time_begin "
                . "AND transaction.time_paid < $time_begin "
                . "AND transaction.status = " . Transaction::STATUS_PAID . " "
                . "GROUP BY transaction.sender_account_id ";
        $command = Yii::$app->getDb()->createCommand($sql);
        $result = $command->queryAll();
        if ($result) {
            $increase_fee_balance = 0;
            foreach ($result as $row) {
                $increase_fee_balance+= $row['increase_balance_fee'];
                $increase_master_balance+= $row['increase_balance_receiver'];
                if (isset($account_log_ids[$row['sender_account_id']])) {
                    // update balance merchant
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance - " . $row['decrease_balance_sender'] . ", "
                            . "balance_pending = balance_pending - " . $row['decrease_balance_sender'] . " "
                            . "WHERE account_id = " . $account_log_ids[$row['sender_account_id']] . " ";
                    $this->execute($sql);
                } else {
                    // update balance merchant
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance - " . $row['decrease_balance_sender'] . ", balance_freezing, balance_pending - " . $row['decrease_balance_sender'] . ", $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $row['sender_account_id'] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
            if (isset($account_log_ids[$master_account_id])) {
                // update balance master
                $sql = "UPDATE `account_log` "
                        . "SET balance = balance + " . $increase_master_balance . " "
                        . "WHERE account_id = " . $account_log_ids[$master_account_id] . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            } else {
                // update balance master
                $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                        . "SELECT account_id, balance + " . $increase_master_balance . ", balance_freezing, balance_pending, $time_end "
                        . "FROM account_log "
                        . "WHERE account_id = " . $master_account_id . " "
                        . "ORDER BY id DESC LIMIT 1";
                $this->execute($sql);
            }
            if ($increase_fee_balance > 0) {
                if (isset($account_log_ids[$fee_account_id])) {
                    // update balance master
                    $sql = "UPDATE `account_log` "
                            . "SET balance = balance + " . $increase_fee_balance . " "
                            . "WHERE account_id = " . $account_log_ids[$fee_account_id] . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                } else {
                    // update balance fee
                    $sql = "INSERT INTO `account_log`(`account_id`, `balance`, `balance_freezing`, `balance_pending`, `time_created`) "
                            . "SELECT account_id, balance + " . $increase_fee_balance . ", balance_freezing, balance_pending, $time_end "
                            . "FROM account_log "
                            . "WHERE account_id = " . $fee_account_id . " "
                            . "ORDER BY id DESC LIMIT 1";
                    $this->execute($sql);
                }
            }
        }
    }
    
    private static function execute($sql) {
        $command = Yii::$app->getDb()->createCommand($sql);
        if ($command->execute()) {
            $this->_writeLog('[AccountLog::process] Success: ' . $sql);
            return true;
        } else {
            $this->_writeLog('[AccountLog::process] Error: ' . $sql);
        }
        return false;
    }

    private static function _writeLog($data) {
        $file = fopen(ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'console' . DS . 'account_log' . DS . date('Ymd') . '.txt', 'a');
        if ($file) {
            fwrite($file, '[' . date('d/m/Y, H:i:s') . ']' . $data . "\n");
            fclose($file);
            return true;
        }
        return false;
    }

}
