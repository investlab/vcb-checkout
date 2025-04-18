<?php


namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\CreditAccount;
use common\models\db\Merchant;
use Yii;

class CreditAccountBusiness
{
    /**
     *
     * @param params : merchant_id, brand_code, account_number
     * @param rollback
     */
    static function add($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $credit_account = CreditAccount::getDb()->beginTransaction();
        }
        $merchant_info = Tables::selectOneDataTable("merchant", "id = " . $params['merchant_id'] . " AND status = " . Merchant::STATUS_ACTIVE);
        if ($merchant_info != false) {
            $model = new CreditAccount();
            $model->merchant_id = $params['merchant_id'];
            $model->branch_code = $params['branch_code'];
            $model->account_number = $params['account_number'];
            $model->status = CreditAccount::STATUS_ACTIVE;
            $model->time_created = time();
            $model->time_updated = time();
            $model->user_created = Yii::$app->user->getId();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm tài khoản báo có';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Merchant không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $credit_account->commit();
            } else {
                $credit_account->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param params : merchant_id, brand_code, account_number
     * @param rollback
     */
    static function update($params, $rollback = true) {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $credit_account = CreditAccount::getDb()->beginTransaction();
        }
        $model = CreditAccount::findOne(["merchant_id" => $params['merchant_id'], "status" => CreditAccount::STATUS_ACTIVE]);
        if (!empty($model)) {
            $model->branch_code = $params['branch_code'];
            $model->account_number = $params['account_number'];
            $model->time_updated = time();
            $model->user_updated = Yii::$app->user->getId();
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi cập nhật tài khoản báo có';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Tài khoản báo có không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $credit_account->commit();
            } else {
                $credit_account->rollBack();
            }
        }
        return array('error_message' => $error_message, 'id' => $id);
    }
}