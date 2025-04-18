<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 17/08/2017
 * Time: 4:56 CH
 */
namespace common\models\business;

use common\models\db\UserLoginFail;
use Yii;

class UserLoginFailBusiness
{

    public static function getByIDToArray($id)
    {
        $user_login_fail = UserLoginFail::findOne(['id' => $id]);
        if ($user_login_fail != null) {
            return $user_login_fail->toArray();
        }
        return $user_login_fail;
    }

    public static function getById($id)
    {
        return UserLoginFail::findOne(['id' => $id]);
    }

    public static function getAllByUserId($user_id)
    {
        return UserLoginFail::find()->andWhere(['user_login_id' => $user_id])->asArray()->all();
    }

    public static function getCountByUserId($user_id)
    {
        return UserLoginFail::find()->andWhere(['user_login_id' => $user_id])->count();
    }

    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLoginFail::getDb()->beginTransaction();
        }

        $model = new UserLoginFail();
        $model->user_login_id = $params['user_login_id'];
        $model->time_failed = time();
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
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

    static function deleteListId($list_id)
    {
        $sql = "DELETE FROM user_login_fail "
            . "WHERE id IN (" . implode(',', $list_id) . ")";
        $command = UserLoginFail::getDb()->createCommand($sql);
        $update = $command->execute();
        return $update;
    }

    static function deleteByUserId($user_id)
    {
        $sql = "DELETE FROM user_login_fail "
            . "WHERE user_login_id = " . $user_id;
        $command = UserLoginFail::getDb()->createCommand($sql);
        $update = $command->execute();
        return $update;
    }
}