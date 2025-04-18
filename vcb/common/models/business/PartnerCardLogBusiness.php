<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use common\models\db\PartnerCardLog;
use common\models\db\CardLog;
use common\models\db\PartnerCardReferCode;
use common\components\libs\Tables;
use Yii;

class PartnerCardLogBusiness
{

    /**
     *
     * @param type $params : partner_card_id, type, function, input, session_id, card_log_id, card_type_id, card_code, card_serial, user_id
     * @param type $rollback
     * @return type
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardLog::getDb()->beginTransaction();
        }
        $model = new PartnerCardLog();
        $model->partner_card_id = $params['partner_card_id'];
        $model->type = $params['type'];
        $model->function = $params['function'];
        $model->input = $params['input'];
        $model->session_id = $params['session_id'];
        $model->card_log_id = $params['card_log_id'];
        $model->card_type_id = $params['card_type_id'];
        $model->card_code = $params['card_code'];
        $model->card_serial = $params['card_serial'];
        $model->card_status = \common\models\db\PartnerCardLog::CARD_STATUS_TIMEOUT;
        $model->status = PartnerCardLog::STATUS_PROCESSING;
        $model->backup_status = PartnerCardLog::BACKUP_STATUS_NEW;
        $model->time_created = time();
        $model->time_backup = 0;
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $model_card_log = CardLog::findOne(["id" => $params['card_log_id']]);
                if ($model_card_log) {
                    $model_card_log->partner_card_log_id = $id;
                    $model_card_log->save();
                    //-----------
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = '';
                    $commit = true;
                }
            } else {
                $error_message = 'Có lỗi khi log gạch thẻ';
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
     * @param type $params : partner_card_log_id, output, result, refer_code, card_price, card_status, user_id,
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardLog::getDb()->beginTransaction();
        }
        $model = PartnerCardLog::findOne(["id" => $params['partner_card_log_id'], "status" => PartnerCardLog::STATUS_PROCESSING]);
        if ($model != null) {
            $model->output = $params['output'];
            $model->result = $params['result'];
            $model->refer_code = $params['refer_code'];
            $model->card_price = $params['card_price'];
            $model->card_status = $params['card_status'];
            $model->status = PartnerCardLog::STATUS_PROCESSED;
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    //------------
                    if (trim($params['refer_code']) != '') {
                        $model_refer_code = new PartnerCardReferCode();
                        $model_refer_code->card_log_id = $model->card_log_id;
                        $model_refer_code->partner_card_log_id = $model->id;
                        $model_refer_code->partner_card_id = $model->partner_card_id;
                        $model_refer_code->partner_card_refer_code = $model->refer_code;
                        $model_refer_code->time_created = time();
                        if ($model_refer_code->validate() && $model_refer_code->save()) {
                            $error_message = '';
                            $commit = true;
                        } else {
                            $error_message = 'Có lỗi khi log thẻ cào';
                        }
                    } else {
                        $error_message = '';
                        $commit = true;
                    }
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
    static function backup($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerCardLog::getDb()->beginTransaction();
        }
        $now = time();
        $time_limit = PartnerCardLog::getTimelimitBackup();
        //----------
        $sql = "UPDATE partner_card_log SET backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSING . ", time_backup = $now "
            . "WHERE time_created < $time_limit "
            . "AND (backup_status = " . PartnerCardLog::BACKUP_STATUS_NEW . " OR (backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSING . " AND time_backup < " . $params['timeout'] . " )) LIMIT 10000 ";
        $command = PartnerCardLog::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "INSERT INTO `partner_card_log_backup`(`id`, `partner_card_id`, `type`, `function`, `input`, `output`, `session_id`, `result`, `refer_code`, `card_log_id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_status`, `status`, `backup_status`, `time_backup`, `time_created`, `time_updated`, `time_card_updated`) "
                . "SELECT `id`, `partner_card_id`, `type`, `function`, `input`, `output`, `session_id`, `result`, `refer_code`, `card_log_id`, `card_type_id`, `card_code`, `card_serial`, `card_price`, `card_status`, `status`, `backup_status`, `time_backup`, `time_created`, `time_updated`, `time_card_updated` "
                . "FROM partner_card_log "
                . "WHERE backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSING . " "
                . "AND time_backup = $now ";
            $command = PartnerCardLog::getDb()->createCommand($sql);
            if ($command->execute()) {
                $sql = "UPDATE partner_card_log SET backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSED . ", time_updated = $now "
                    . "WHERE backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSING . " "
                    . "AND time_backup = $now ";
                $command = PartnerCardLog::getDb()->createCommand($sql);
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
    static function deleteAfterBackup($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerCardLog::getDb()->beginTransaction();
        }
        //----------
        $sql = "DELETE FROM partner_card_log WHERE backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSED . " LIMIT 1000 ";
        $command = PartnerCardLog::getDb()->createCommand($sql);
        if ($command->execute()) {
            $sql = "OPTIMIZE TABLE `partner_card_log` ";
            $command = PartnerCardLog::getDb()->createCommand($sql);
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
}