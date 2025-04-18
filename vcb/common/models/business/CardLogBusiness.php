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
use common\models\db\CardMerchantReferCode;
use common\models\db\CardTransaction;
use common\components\libs\Tables;
use Yii;

class CardLogBusiness {

    /**
     *
     * @param type $params : version, merchant_id, merchant_refer_code, bill_type, cycle_day, card_type_id, card_code, card_serial, partner_card_id, percent_fee, currency, merchant_input, time_created, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = CardLog::getDb()->beginTransaction();
        }
        $model = new CardLog();
        $model->version = $params['version'];
        $model->merchant_id = $params['merchant_id'];
        $model->merchant_refer_code = $params['merchant_refer_code'];
        $model->bill_type = $params['bill_type'];
        $model->cycle_day = $params['cycle_day'];
        $model->card_type_id = $params['card_type_id'];
        $model->card_code = $params['card_code'];
        $model->card_serial = $params['card_serial'];
        $model->partner_card_id = $params['partner_card_id'];
        $model->percent_fee = $params['percent_fee'];
        $model->currency = $params['currency'];
        $model->merchant_input = $params['merchant_input'];
        $model->withdraw_time_limit = CardLog::getWithdrawTimeLimit($params['bill_type'], $params['cycle_day'], $params['time_created']);
        $model->card_status = CardLog::CARD_STATUS_TIMEOUT;
        $model->transaction_status = CardLog::TRANSACTION_STATUS_NEW;
        $model->backup_status = CardLog::BACKUP_STATUS_NEW;
        $model->time_created = $params['time_created'];
        $model->time_create_transaction = 0;
        $model->time_backup = 0;
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                //------------
                $model_refer_code = new CardMerchantReferCode();
                $model_refer_code->card_log_id = $id;
                $model_refer_code->merchant_id = $params['merchant_id'];
                $model_refer_code->merchant_refer_code = $params['merchant_refer_code'];
                $model_refer_code->time_created = $params['time_created'];
                if ($model_refer_code->validate() && $model_refer_code->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi log thẻ cào';
                }
            } else {
                $error_message = 'Có lỗi khi log thẻ cào';
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
     * @param type $params : card_log_id, result_code, merchant_output, card_price, card_amount, card_status, partner_card_refer_code, user_id,
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = CardLog::getDb()->beginTransaction();
        }
        $model = CardLog::findOne(["id" => $params['card_log_id'], "card_status" => CardLog::CARD_STATUS_TIMEOUT]);
        if ($model != null) {
            $model->result_code = trim($params['result_code']);
            $model->merchant_output = trim($params['merchant_output']);
            $model->card_price = $params['card_price'];
            $model->card_amount = $params['card_amount'];
            $model->card_status = $params['card_status'];
            $model->partner_card_refer_code = $params['partner_card_refer_code'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật log thẻ cào';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy dữ liệu';
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
     * @param type $params : timeout
     * @param type $rollback
     * @return type
     */
    static function createCardTransaction($params) {
        $error_message = 'Lỗi không xác định';
        //------------
        $now = time();
        $sql = "UPDATE card_log SET transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . ", time_create_transaction = $now "
                . "WHERE card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                . "AND (transaction_status = " . CardLog::TRANSACTION_STATUS_NEW . " OR (transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " AND time_create_transaction < " . $params['timeout'] . " )) ";
        $command = CardLog::getDb()->createCommand($sql);
        if ($command->execute()) {
            $inputs = array(
                'time_create_transaction' => $now,
            );
            $result = self::_createCardTransactionMultiRow($inputs);
            if ($result['error_message'] == '') {
                $error_message = '';
            } else {                
                $error_message = 'Có lỗi khi thêm giao dịch thẻ cào từ log';
                $card_log_info = Tables::selectAllDataTable("card_log", "card_status = " . CardLog::CARD_STATUS_SUCCESS . " AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " AND time_create_transaction = $now ");
                if ($card_log_info != false) {
                    foreach ($card_log_info as $row) {
                        $inputs = array(
                            'card_log_id' => $row['id'],
                        );
                        $result = self::_createCardTransactionOneByOne($inputs);
                    }
                }
            }
        } else {
            $error_message = 'Có lỗi khi thêm giao dịch thẻ cào từ log';
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : time_create_transaction
     * @param type $rollback
     * @return type
     */
    private static function _createCardTransactionMultiRow($params) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        $transaction = CardLog::getDb()->beginTransaction();
        $now = $params['time_create_transaction'];
        try {
            $sql = "INSERT INTO `card_transaction`(`id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `card_log_id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, `status`, `cashout_id`, `time_created`, `time_updated`, `time_withdraw`, `user_created`, `user_updated`, `user_withdraw`) "
                    . "SELECT `id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, " . CardTransaction::STATUS_NEW . ", 0, `time_created`, `time_updated`, 0, `user_created`, 0, 0 FROM card_log "
                    . "WHERE card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                    . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " "
                    . "AND time_create_transaction = $now ";
            $command = CardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $sql = "UPDATE card_log SET transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSED . ", card_transaction_id = card_log.id, time_updated = $now "
                        . "WHERE card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                        . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " "
                        . "AND time_create_transaction = $now ";
                $command = CardLog::getDb()->createCommand($sql);
                if ($command->execute()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm giao dịch thẻ cào từ log';
                }
            } else {
                $error_message = 'Có lỗi khi thêm giao dịch thẻ cào từ log';
            }
        } catch (\yii\db\Exception $ex) {
            $error_message = 'Có lỗi khi thêm giao dịch thẻ cào từ log do trùng thông tin thẻ';
        }            
        if ($commit == true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : card_log_id
     * @param type $rollback
     * @return type
     */
    private static function _createCardTransactionOneByOne($params) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        $transaction = CardLog::getDb()->beginTransaction();
        $now = time();
        try {
            $sql = "INSERT INTO `card_transaction`(`id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `card_log_id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, `status`, `cashout_id`, `time_created`, `time_updated`, `time_withdraw`, `user_created`, `user_updated`, `user_withdraw`) "
                . "SELECT `id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_refer_code`, `partner_card_log_id`, `percent_fee`, `withdraw_time_limit`, " . CardTransaction::STATUS_NEW . ", 0, `time_created`, `time_updated`, 0, `user_created`, 0, 0 FROM card_log "
                . "WHERE id = " . $params['card_log_id'] . " "
                . "AND card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " ";
            $command = CardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $sql = "UPDATE card_log SET transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSED . ", card_transaction_id = card_log.id, time_updated = $now "
                        . "WHERE id = " . $params['card_log_id'] . " "
                        . "AND card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                        . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " ";
                $command = CardLog::getDb()->createCommand($sql);
                if ($command->execute()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật log thẻ cào';
                }
            } else {
                $error_message = 'Có lỗi khi cập nhật log thẻ cào';
            }
        } catch (\yii\db\Exception $ex) {
            $sql = "UPDATE card_log SET transaction_status = " . CardLog::TRANSACTION_STATUS_ERROR . ", time_updated = $now "
                    . "WHERE id = " . $params['card_log_id'] . " "
                    . "AND card_status = " . CardLog::CARD_STATUS_SUCCESS . " "
                    . "AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " ";
            $command = CardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật log thẻ cào';
            }
        }
        if ($commit == true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : timeout
     * @param type $rollback
     * @return type
     */
    static function backup($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = CardLog::getDb()->beginTransaction();
        }
        $now = time();
        $time_limit = CardLog::getTimelimitBackup();
        //----------
        $sql = "UPDATE card_log SET backup_status = " . CardLog::BACKUP_STATUS_PROCESSING . ", time_backup = $now "
                . "WHERE time_created < $time_limit "
                . "AND ((card_status = " . CardLog::CARD_STATUS_SUCCESS . " AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSED . ") OR card_status != " . CardLog::CARD_STATUS_SUCCESS . ") "
                . "AND (backup_status = " . CardLog::BACKUP_STATUS_NEW . " OR (backup_status = " . CardLog::BACKUP_STATUS_PROCESSING . " AND time_backup < " . $params['timeout'] . " )) LIMIT 10000 ";
        $command = CardLog::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "INSERT INTO `card_log_backup`(`id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_log_id`, `partner_card_refer_code`, `percent_fee`, `withdraw_time_limit`, `merchant_input`, `merchant_output`, `result_code`, `card_status`, `transaction_status`, `card_transaction_id`, `backup_status`, `time_card_updated`, `time_created`, `time_updated`, `time_create_transaction`, `time_backup`, `user_created`, `user_updated`) "
                    . "SELECT `id`, `version`, `merchant_id`, `merchant_refer_code`, `bill_type`, `cycle_day`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_amount`, `currency`, `partner_card_id`, `partner_card_log_id`, `partner_card_refer_code`, `percent_fee`, `withdraw_time_limit`, `merchant_input`, `merchant_output`, `result_code`, `card_status`, `transaction_status`, `card_transaction_id`, `backup_status`, `time_card_updated`, `time_created`, `time_updated`, `time_create_transaction`, `time_backup`, `user_created`, `user_updated` "
                    . "FROM card_log "
                    . "WHERE backup_status = " . CardLog::BACKUP_STATUS_PROCESSING . " "
                    . "AND time_backup = $now ";
            $command = CardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $sql = "UPDATE card_log SET backup_status = " . CardLog::BACKUP_STATUS_PROCESSED . ", time_updated = $now "
                        . "WHERE backup_status = " . CardLog::BACKUP_STATUS_PROCESSING . " "
                        . "AND time_backup = $now ";
                $command = CardLog::getDb()->createCommand($sql);
                if ($command->execute()) {
                    $delete = self::deleteAfterBackup(array(), false);
                    if ($delete['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $delete['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi backup log';
                }
            } else {
                $error_message = 'Có lỗi khi backup log';
            }
        } else {
            $error_message = 'Có lỗi khi backup log';
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
     * @param type $params :
     * @param type $rollback
     * @return type
     */
    static function deleteAfterBackup($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = CardLog::getDb()->beginTransaction();
        }
        //----------
        $sql = "DELETE FROM card_log WHERE backup_status = " . CardLog::BACKUP_STATUS_PROCESSED . " LIMIT 1000 ";
        $command = CardLog::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "OPTIMIZE TABLE `card_log` ";
            $command = CardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi xóa log';
            }
        } else {
            $error_message = 'Có lỗi khi xóa log';
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
     * @param type $params : card_log_id, card_price, partner_card_refer_code, user_id,
     * @param type $rollback
     * @return type
     */
    static function updateCardStatusSuccess($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = CardLog::getDb()->beginTransaction();
        }
        $model = CardLog::findOne(["id" => $params['card_log_id'], "card_status" => CardLog::CARD_STATUS_TIMEOUT]);
        if ($model == null) {
            $model = CardLogBackup::findOne(["id" => $params['card_log_id'], "card_status" => CardLog::CARD_STATUS_TIMEOUT]);
        }
        if ($model != null) {
            $fee = \common\models\db\MerchantCardFee::calculateFee($params['card_price'], $model->percent_fee);
            $card_amount = $params['card_price'] - $fee;
            $model->result_code = '00';
            $model->card_price = $params['card_price'];
            $model->card_amount = $card_amount;
            $model->card_status = CardLog::CARD_STATUS_SUCCESS;
            $model->partner_card_refer_code = $params['partner_card_refer_code'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $inputs = array(
                        'card_log_id' => $model->id,
                        'user_id' => $params['user_id'],
                    );
                    $result = CardTransactionBusiness::addByCardLog($inputs, false);
                    if ($result['error_message'] == '') {
                        $error_message = '';
                        $commit = true;
                    } else {
                        $error_message = $result['error_message'];
                    }
                } else {
                    $error_message = 'Có lỗi khi cập nhật log thẻ cào';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Log thẻ không tồn tại hoặc không hợp lệ';
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
