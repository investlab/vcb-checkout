<?php

namespace common\models\business;


use common\models\db\PaymentMethodFee;

class PaymentMethodFeeBusiness
{

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethodFee::getDb()->beginTransaction();
        }
        $model = new PaymentMethodFee();
        $model->payment_method_id = $params['payment_method_id'];
        $model->percentage_fee = $params['percentage_fee'];
        $model->flat_fee = $params['flat_fee'];
        $model->payer_percentage_fee = $params['payer_percentage_fee'];
        $model->payer_flat_fee = $params['payer_flat_fee'];
        $model->time_begin = $params['time_begin'];
        $model->time_end = $params['time_end'];
        $model->status = PaymentMethodFee::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm';
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

    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = PaymentMethodFee::getDb()->beginTransaction();
        }
        $model = PaymentMethodFee::findOne(['id' => $params['id']]);
        $model->payment_method_id = $params['payment_method_id'];
        $model->percentage_fee = $params['percentage_fee'];
        $model->flat_fee = $params['flat_fee'];
        $model->payer_percentage_fee = $params['payer_percentage_fee'];
        $model->payer_flat_fee = $params['payer_flat_fee'];
        $model->time_begin = $params['time_begin'];
        $model->time_end = $params['time_end'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật';
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
} 