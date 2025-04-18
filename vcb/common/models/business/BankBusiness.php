<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/20/2016
 * Time: 10:02 PM
 */

namespace common\models\business;


use common\models\db\Bank;
use common\models\form\BankForm;
use Yii;

class BankBusiness
{

    public static function getById($id)
    {
        return Bank::findOne(['id' => $id]);
    }

    public static function getByName($name)
    {
        return Bank::findOne(['code' => $name]);
    }

    public static function insert(BankForm $form)
    {
        $bank = new Bank();
        $bank->name = $form->name;
        $bank->trade_name = $form->trade_name;
        $bank->code = $form->code;
        $bank->description = $form->description;
        $bank->status = $form->status;
        $bank->time_created = time();
        $bank->user_created = 1;

        if ($bank->validate()) {
            if ($bank->save()) {
                return $bank->getDb()->getLastInsertID();
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     *
     * @param type $params : name, trade_name, code, description, user_id
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
            $transaction = Bank::getDb()->beginTransaction();
        }
        $model = new Bank();
        $model->name = $params['name'];
        $model->trade_name = $params['trade_name'];
        $model->code = $params['code'];
        $model->description = $params['description'];
        $model->status = 1;
        $model->time_created = time();
        $model->user_created = $params['user_id'];
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->getDb()->getLastInsertID();
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thêm ngân hàng';
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