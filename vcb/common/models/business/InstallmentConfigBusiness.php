<?php


namespace common\models\business;

use common\models\db\InstallmentConfig;
use Yii;

class InstallmentConfigBusiness
{
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $config = InstallmentConfig::getDb()->beginTransaction();
        }
        $model = new InstallmentConfig();
        $model->merchant_id = $params['merchant_id'];
        $model->card_accept = json_encode($params['card_accept']);
        $model->cycle_accept = json_encode($params['cycle_accept']);
        if ($model->validate()) {
            if ($model->save()) {
                $commit == true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi thêm cấu hình trả góp';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $config->commit();
            } else {
                $config->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : id, name, website, logo, email_notification, mobile_notification, user_id
     * @param type $rollback
     * @return type
     */
    static function update($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        if ($rollback) {
            $config = InstallmentConfig::getDb()->beginTransaction();
        }
        $model = InstallmentConfig::findOne(['merchant_id' => $params['merchant_id']]);

        $bank_code = $params['bank_code'];
        $card_accept = json_decode($model->card_accept, true);
        $card_accept[$bank_code] = $params['card_accept'][$bank_code];

        $cycle_accept = json_decode($model->cycle_accept, true);
        $cycle_accept[$bank_code] = $params['cycle_accept'][$bank_code];

        $model->card_accept = json_encode($card_accept);
        $model->cycle_accept = json_encode($cycle_accept);
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $commit = true;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật cấu hình trả góp';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }

        if ($rollback) {
            if ($commit == true) {
                $config->commit();
            } else {
                $config->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    //Khoá, mở khoá cấu hình trả góp merchant
    public static function lockInstallment($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        if ($rollback) {
            $config = InstallmentConfig::getDb()->beginTransaction();
        }
        $model = InstallmentConfig::findOne(['merchant_id' => $params['merchant_id']]);
        if (!empty($model)) {
            if ($params['lock']) {
                $model->status = LOCK_STATUS;
                $message = 'Có lỗi khi khoá cấu hình trả góp';
            } else {
                $model->status = ACTIVE_STATUS;
                $message = 'Có lỗi khi mở cấu hình trả góp';
            }
            if ($model->save()) {
                $commit = true;
                $error_message = '';
            } else {
                $error_message = $message;
            }
        } else {
            $error_message = 'Không tìm thấy cấu hình trả góp';
        }
        if ($rollback) {
            if ($commit == true) {
                $config->commit();
            } else {
                $config->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }
}