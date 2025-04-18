<?php

namespace common\models\business;

use Yii;
use common\models\db\UserLogin;
use common\models\db\UserLoginSocialNetwork;
use common\models\db\SocialNetwork;
use common\components\libs\Tables;
use common\components\utils\Validation;

class UserLoginSocialNetworkBusiness
{

    /**
     *
     * @param params : user_login_id, social_network_account_id
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = UserLoginSocialNetwork::getDb()->beginTransaction();
        }
        $user_login_info = Tables::selectOneDataTable("user_login", "id = " . $params['user_login_id']);
        if ($user_login_info != false) {
            $social_network_info = Tables::selectOneDataTable("social_network", "id = " . $params['social_network_id'] . " AND status = " . SocialNetwork::STATUS_ACTIVE);
            if ($social_network_info != false) {
                $model = new UserLoginSocialNetwork();
                $model->user_login_id = $params['user_login_id'];
                $model->social_network_id = $params['social_network_id'];
                $model->social_network_account_id = $params['social_network_account_id'];
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
                $error_message = 'Mạng xã hội không tồn tại';
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