<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 11:11 SA
 */

namespace common\models\business;

use common\models\db\Method;
use common\components\libs\Tables;
use Yii;

class MethodBusiness
{

    public static function getById($id)
    {
        return Method::findOne(['id' => $id]);
    }

    public static function getByIdToArray($id)
    {
        return Method::findOne(['id' => $id])->toArray();
    }

    public static function getNameById($id)
    {
        return Method::findOne(['id' => $id])->name;
    }

    /**
     *
     * @param type $params : transaction_type_id, name, code, description, user_id
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
            $transaction = Method::getDb()->beginTransaction();
        }
        $transaction_type_info = Tables::selectOneDataTable("transaction_type", ["id = :id", "id" => $params['transaction_type_id']]);
        if ($transaction_type_info != false) {
            $model = new Method();
            $model->transaction_type_id = $params['transaction_type_id'];
            $model->name = $params['name'];
            $model->code = mb_strtoupper($params['code']);
            $model->description = $params['description'];
            $model->status = 1;
            $model->position = $params['position'];
            $model->time_created = time();
            $model->user_created = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $id = $model->getDb()->getLastInsertID();
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi thêm nhóm phương thức thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Loại giao dịch không hợp lệ';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message, 'id' => $id);
    }

    /**
     *
     * @param type $params : id, name, code, description, user_id
     * @param type $rollback
     * @return type
     */
    static function edit($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = Method::getDb()->beginTransaction();
        }
        $model = self::getById($params['id']);
        if ($model != null) {
            $model->name = $params['name'];
            $model->code = mb_strtoupper($params['code']);
            $model->description = $params['description'];
            $model->position = $params['position'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi sửa nhóm phương thức thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy nhóm phương thức thanh toán này';
        }
        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            BasicBusiness::convertErrorMessage($error_message);
        }
        return array('error_message' => $error_message);
    }
}