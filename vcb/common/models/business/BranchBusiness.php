<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/28/2018
 * Time: 10:33
 */

namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\Branch;
use common\models\db\User;
use Yii;

class BranchBusiness
{
    /**
     *
     * @param $params : name, city, user_id
     * @param boolean $rollback
     * @return 'error_message', 'id'
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $branch = Branch::getDb()->beginTransaction();
        }
        $model = new Branch();
        $model->name = $params['name'];
        $model->city = $params['city'];
        $model->status = Branch::STATUS_ACTIVE;
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm chi nhánh';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $branch->commit();
            } else {
                $branch->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param $params : id, name, city, user_id
     * @param boolean $rollback
     * @return 'error_message', 'id'
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $branch = Branch::getDb()->beginTransaction();
        }
        $model = Branch::findOne(['id' => $params['id']]);
        $model->name = $params['name'];
        $model->city = $params['city'];
        $model->time_updated = time();
        $model->user_updated = $params['user_id'];
        
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật chi nhánh';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $branch->commit();
            } else {
                $branch->rollBack();
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
            $branch = Branch::getDb()->beginTransaction();
        }
        $model = Branch::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = Branch::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi Khóa chi nhánh';
            }
        } else {
            $error_message = 'Không tìm thấy chi nhánh này';
        }
        if ($rollback) {
            if ($commit == true) {
                $branch->commit();
            } else {
                $branch->rollBack();
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
            $branch = Branch::getDb()->beginTransaction();
        }
        $model = Branch::findOne(['id' => $params['id']]);
        if ($model != null) {
            $model->status = Branch::STATUS_ACTIVE;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi kích hoạt chi nhánh';
            }
        } else {
            $error_message = 'Không tìm thấy chi nhánh này';
        }
        if ($rollback) {
            if ($commit == true) {
                $branch->commit();
            } else {
                $branch->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }

    static function viewDetail($id) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $branch = Branch::findOne(['id' => $id]);

        if (!empty($branch)) {
            $error_message = '';
            $user_created = User::findOne(['id' => $branch['user_created']]);
            if (!empty($user_created)) {
                $branch['user_created'] = $user_created['fullname'];
            }
            $user_updated = User::findOne(['id' => $branch['user_updated']]);
            if (!empty($user_updated)) {
                $branch['user_updated'] = $user_updated['fullname'];
            }
        } else {
            $error_message = 'Không tìm thấy chi nhánh';
        }

        return [
            'data' => $branch,
            'error_message' => $error_message
        ];
    }
} 