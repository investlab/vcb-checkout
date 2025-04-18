<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 09/05/2017
 * Time: 2:09 CH
 */
namespace common\models\business;

use common\models\db\PartnerPayment;
use common\util\TextUtil;
use Yii;

class PartnerPaymentBusiness
{
    public static function getById($id)
    {
        return PartnerPayment::findOne(['id' => $id]);
    }

    public static function getByIdToArray($id)
    {
        return PartnerPayment::findOne(['id' => $id])->toArray();
    }

    public static function getNameById($id)
    {
        return PartnerPayment::findOne(['id' => $id])->name;
    }

    /**
     *
     * @param type $params : name, code, description, user_id
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
            $transaction = PartnerPayment::getDb()->beginTransaction();
        }
        $model = new PartnerPayment();
        $model->name = $params['name'];
        $model->code = mb_strtoupper($params['code']);
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
                $error_message = 'Có lỗi khi thêm kênh thanh toán';
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
            $transaction = PartnerPayment::getDb()->beginTransaction();
        }
        $model = self::getById($params['id']);
        if ($model != null) {
            $model->name = $params['name'];
            $model->code = mb_strtoupper($params['code']);
            $model->description = $params['description'];
            $model->token_key = $params['token_key'];
            $model->checksum_key = $params['checksum_key'];
            $model->time_updated = time();
            $model->user_updated = $params['user_id'];
            if ($model->validate()) {
                if ($model->save()) {
                    $error_message = '';
                    $commit = true;
                } else {
                    $error_message = 'Có lỗi khi sửa kênh thanh toán';
                }
            } else {
                $error_message = 'Tham số đầu vào không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy kênh thanh toán này';
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