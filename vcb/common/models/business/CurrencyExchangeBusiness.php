<?php

namespace common\models\business;

use common\models\db\CurrencyExchange;

class CurrencyExchangeBusiness
{
    public static function getById($id)
    {
        return CurrencyExchange::findOne(['id' => $id]);
    }

    public static function getByCode($code)
    {
        return CurrencyExchange::findOne(['currency_code' => $code]);
    }

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = CurrencyExchange::getDb()->beginTransaction();
        }
        $model = new CurrencyExchange();
        $model->currency_code = $params['CurrencyCode'];
        $model->currency_name = $params['CurrencyName'];
        $model->buy = floatval(preg_replace("/[^-0-9\.]/","",$params['Buy']));
        $model->transfer = floatval(preg_replace("/[^-0-9\.]/","",$params['Transfer']));
        $model->sell = floatval(preg_replace("/[^-0-9\.]/","",$params['Sell']));
        $model->time_update = time();
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật tỉ giá';
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
        //------------
        if ($rollback) {
            $transaction = CurrencyExchange::getDb()->beginTransaction();
        }
        $model = CurrencyExchange::findBySql("SELECT * FROM currency_exchange WHERE currency_code = '" . $params['CurrencyCode']."'")->one();
        $model->buy = floatval(preg_replace("/[^-0-9\.]/","",$params['Buy']));
        $model->transfer = floatval(preg_replace("/[^-0-9\.]/","",$params['Transfer']));
        $model->sell = floatval(preg_replace("/[^-0-9\.]/","",$params['Sell']));
        $model->time_update = time();
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật tỉ giá';
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