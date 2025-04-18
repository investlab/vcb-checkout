<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 13:21
 */

namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\PartnerCard;
use Yii;

class PartnerCardBusiness
{

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerCard::getDb()->beginTransaction();
        }
        $model = new PartnerCard();
        $model->name = $params['name'];
        $model->code = $params['code'];
        $model->bill_type = $params['bill_type'];
        $model->config = $params['config'];
        $model->status = PartnerCard::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm đối tác gạch thẻ';
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
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PartnerCard::getDb()->beginTransaction();
        }
        $model = PartnerCard::findOne(['id' => $params['id']]);
        $model->name = $params['name'];
        $model->code = $params['code'];
        $model->config = $params['config'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật đối tác gạch thẻ';
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
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerCard::getDb()->beginTransaction();
        }
        $model = PartnerCard::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = PartnerCard::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa đối tác gạch thẻ';
            }
        } else {
            $error_message = 'Không tìm thấy đối tác này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    static function active($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PartnerCard::getDb()->beginTransaction();
        }
        $model = PartnerCard::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = PartnerCard::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi kích hoạt Đối tác';
            }
        } else {
            $error_message = 'Không tìm thấy đối tác này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

} 