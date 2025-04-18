<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/20/2016
 * Time: 10:02 PM
 */

namespace common\models\business;


use common\models\db\BinAccept;
use common\models\form\BinAcceptForm;
use Yii;

class BinAcceptBusiness
{

    public static function getById($id)
    {
        return BinAccept::findOne(['id' => $id]);
    }

    /**
     *
     * @param type $params : bin_code, card_type, status
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
            $bin_accept = BinAccept::getDb()->beginTransaction();
        }
        $model = new BinAccept();
        $model->bin_code = $params['bin_code'];
        $model->card_type = $params['card_type'];
        $model->status = $params['status'];
        $model->time_created = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thêm đầu bin';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $bin_accept->commit();
            } else {
                $bin_accept->rollBack();
            }
        }

        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : bin_code, card_type, status
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
            $bin_accept = BinAccept::getDb()->beginTransaction();
        }
        $model = BinAccept::findOne(['id' => $params['id']]);
        $model->bin_code = $params['bin_code'];
        $model->card_type = $params['card_type'];
        $model->status = $params['status'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi cập nhật đầu bin';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $bin_accept->commit();
            } else {
                $bin_accept->rollBack();
            }
        }

        return array('error_message' => $error_message, 'id' => $id);
    }


    static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $bin_accept = BinAccept::getDb()->beginTransaction();
        }
        $model = BinAccept::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = BinAccept::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi khóa đầu bin';
            }
        } else {
            $error_message = 'Không tìm thấy dữ liệu';
        }
        if ($rollback) {
            if ($commit == true) {
                $bin_accept->commit();
            } else {
                $bin_accept->rollBack();
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
            $bin_accept = BinAccept::getDb()->beginTransaction();
        }
        $model = BinAccept::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = BinAccept::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi mở khóa đầu bin';
            }
        } else {
            $error_message = 'Không tìm thấy dữ liệu';
        }
        if ($rollback) {
            if ($commit == true) {
                $bin_accept->commit();
            } else {
                $bin_accept->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }
} 