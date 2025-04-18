<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 13/12/2017
 * Time: 3:38 CH
 */

namespace common\models\business;

use common\models\db\Right;
use common\models\db\UserAdminAccount;
use common\models\db\UserGroup;
use common\models\db\UserGroupRight;
use Yii;

class UserAdminAccountBusiness
{
    public static function getByIDToArray($id)
    {
        return UserAdminAccount::find()->where(['id' => $id])->asArray()->one();
    }

    public static function getByUserId($user_id)
    {
        $data = UserAdminAccount::find()->where(['=', 'user_id', $user_id])->asArray()->all();
        foreach ($data as $k => $v) {
            $user_group = UserGroup::findOne(['id' => $v['user_group_id']]);
            $data[$k]['group_name'] = $user_group['name'];
            $data[$k]['group_code'] = $user_group['code'];
            if ($v['status'] == UserAdminAccount::STATUS_ACTIVE) {
                $data[$k]['status_name'] = 'Hoạt động';
                $data[$k]['status_class'] = 'label-success';
            } else {
                $data[$k]['status_name'] = 'Bị khóa';
                $data[$k]['status_class'] = 'label-danger';
            }

        }

        return $data;
    }

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $transaction = UserAdminAccount::getDb()->beginTransaction();
        }
        $model = new UserAdminAccount();
        $model->name = $params['name'];
        $model->user_group_id = $params['user_group_id'];
        $model->user_id = $params['user_id'];
        $model->status = $params['status'];
        $model->time_created = time();
        $model->time_updated = time();
        $model->user_created = $params['user_create_id'];
        $model->user_updated = $params['user_create_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm tài khoản';
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
        if ($rollback) {
            $transaction = UserAdminAccount::getDb()->beginTransaction();
        }
        $model = UserAdminAccount::findOne(['id' => $params['id']]);
        $model->name = $params['name'];
        $model->status = $params['status'];
        $model->time_updated = time();
        $model->user_updated = $params['user_create_id'];

        if ($model->validate()) {
            if ($model->save()) {
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật tài khoản';
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
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : id, user_id
     * @param type $rollback
     * @return type
     */
    public static function lock($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $transaction = UserAdminAccount::getDb()->beginTransaction();
        }
        $model = UserAdminAccount::findOne(['id' => $params['id']]);
        if ($model) {
            $model->status = UserAdminAccount::STATUS_LOCK;
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $commit = true;
                    $error_message = '';
                } else {
                    $error_message = 'Có lỗi khi khóa tài khoản.';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Dữ liệu không tồn tại';
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