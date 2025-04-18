<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/20/2016
 * Time: 10:02 PM
 */

namespace common\models\business;

use common\models\db\PartnerPaymentReferCode;
use common\components\libs\Tables;
use Yii;

class PartnerPaymentReferCodeBusiness
{
    /**
     *
     * @param params : transaction_id, partner_payment_refer_code, user_id,
     * @param rollback
     */
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        $id = null;
        //------------
        if ($rollback) {
            $transaction = PartnerPaymentReferCode::getDb()->beginTransaction();
        }
        $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id", "id" => $params['transaction_id']]);
        if ($transaction_info != false) {
            $check_exists = Tables::selectOneDataTable("partner_payment_refer_code", ["partner_payment_id = :partner_payment_id AND partner_payment_refer_code = :partner_payment_refer_code AND transaction_type_id = :transaction_type_id ", "partner_payment_id" => $transaction_info['partner_payment_id'], "partner_payment_refer_code" => $params['partner_payment_refer_code'], "transaction_type_id" => $transaction_info['transaction_type_id']]);
            if ($check_exists == false) {
                $model = PartnerPaymentReferCode::findOne(["transaction_id" => $params['transaction_id']]);
                if ($model == null) {
                    $model = new PartnerPaymentReferCode();
                }
                $model->partner_payment_id = $transaction_info['partner_payment_id'];
                $model->partner_payment_refer_code = strval($params['partner_payment_refer_code']);
                $model->transaction_type_id = $transaction_info['transaction_type_id'];
                $model->transaction_id = $transaction_info['id'];
                $model->time_created = time();
                $model->user_created = $params['user_id'];
                if ($model->validate()) {
                    if ($model->save()) {
                        $error_message = '';
                        $commit = true;
                        $id = $model->getDb()->getLastInsertID();
                    } else {
                        $error_message = 'Có lỗi khi thêm mã tham chiếu kênh thanh toán';
                    }
                } else {
                    $error_message = 'Tham số đầu vào không hợp lệ';
                }
            } else {
                $error_message = 'Mã tham chiếu kênh thanh toán đã tồn tại';
            }
        } else {
            $error_message = 'Giao dịch không hợp lệ';
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
