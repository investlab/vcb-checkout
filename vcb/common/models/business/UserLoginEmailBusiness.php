<?php

namespace common\models\business;

use Yii;
use common\models\db\UserLogin;
use common\models\db\UserLoginEmail;
use common\components\libs\Tables;
use common\components\utils\Validation;

class UserLoginEmailBusiness
{

    /**
     *
     * @param params : user_login_id, email
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLoginEmail::getDb()->beginTransaction();
        }
        $user_login_info = Tables::selectOneDataTable("user_login", "id = " . $params['user_login_id']);
        if ($user_login_info != false) {
            $model = new UserLoginEmail();
            $model->user_login_id = $params['user_login_id'];
            $model->email = $params['email'];
            $model->time_created = time();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm tài khoản';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản đăng nhập không tồn tại';
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