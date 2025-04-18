<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 6/12/2018
 * Time: 13:26
 */

namespace common\models\business;

use Yii;
use common\models\db\CardType;

class CardTypeBusiness
{
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = CardType::getDb()->beginTransaction();
        }
        $model = new CardType();
        $model->name = $params['name'];
        $model->code = $params['code'];
        $model->currency = $GLOBALS['CURRENCY']['VND'];
        $model->status = CardType::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm loại thẻ';
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
            $transaction = CardType::getDb()->beginTransaction();
        }
        $model = CardType::findOne(['id' => $params['id']]);
        $model->name = $params['name'];
        $model->code = $params['code'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật loại thẻ';
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
            $transaction = CardType::getDb()->beginTransaction();
        }
        $model = CardType::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = CardType::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa loại thẻ';
            }
        } else {
            $error_message = 'Không tìm thấy loại thẻ này';
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
            $transaction = CardType::getDb()->beginTransaction();
        }
        $model = CardType::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = CardType::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi kích hoạt loại thẻ';
            }
        } else {
            $error_message = 'Không tìm thấy loại thẻ này';
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