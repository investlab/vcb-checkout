<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use common\models\db\PartnerCardSession;
use common\components\libs\Tables;
use Yii;

class PartnerCardSessionBusiness
{

    /**
     *
     * @param type $params : partner_card_id, session_id, session_time_limit
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
            $transaction = PartnerCardSession::getDb()->beginTransaction();
        }
        $model = new PartnerCardSession();
        $model->partner_card_id = $params['partner_card_id'];
        $model->session_id = $params['session_id'];
        $model->session_time_limit = $params['session_time_limit'];
        $model->status = PartnerCardSession::STATUS_WAIT;
        $model->time_created = time();
        $model->time_updated = time();
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi session gạch thẻ';
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
     * @param type $params : partner_card_id, session_id, session_time_limit,
     * @param type $rollback
     * @return type
     */
    static function updateNewSessionId($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardSession::getDb()->beginTransaction();
        }
        $model = PartnerCardSession::findOne(["partner_card_id" => $params['partner_card_id']]);
        if ($model != null) {
            $model->session_id = $params['session_id'];
            $model->session_time_limit = $params['session_time_limit'];
            $model->status = PartnerCardSession::STATUS_ACTIVE;
            $model->time_updated = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật log session';
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
     * @param type $params : partner_card_id,
     * @param type $rollback
     * @return type
     */
    static function updateStatusWait($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerCardSession::getDb()->beginTransaction();
        }
        $now = time();
        $time_out = $now - 60;
        $sql = "UPDATE partner_card_session SET status = " . PartnerCardSession::STATUS_WAIT . ", time_updated = $now "
            . "WHERE partner_card_id = " . $params['partner_card_id'] . " "
            . "AND (session_time_limit < $now OR (status = " . PartnerCardSession::STATUS_WAIT . " AND time_updated < $time_out )) ";
        $command = PartnerCardSession::getDb()->createCommand($sql);
        if ($command->execute()) {
            $error_message = '';
            $commit = true;
        } else {
            $error_message = 'Có lỗi khi cập nhật log session';
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