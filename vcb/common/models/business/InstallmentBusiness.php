<?php

namespace common\models\business;


use common\components\libs\Tables;
use common\models\db\Installment;

class InstallmentBusiness
{
    public static function add($params): array
    {
//        params ex:

        $id = false;
        $model = new Installment();
        $model->checkout_order_id = $params['checkout_order_id'];
        $model->status = Installment::STATUS_WAIT_SENT;
        $model->time_created = time();
        $model->user_created = 9999 /* User system*/;
        if ($model->validate()) {
            if ($model->save()) {
                $id = $model->id;
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi khi tạo giao dịch trả góp';
            }
        } else {
            $error_message = 'Tham số đầu vào không hợp lệ';
        }
        return [
            'error_message' => $error_message,
            'id' => $id,
        ];
    }

    public static function cancel($params)
    {
        $installment = Installment::find()
            ->where([
                'checkout_order_id' => $params['checkout_order_id'],
                'status' => Installment::STATUS_WAIT_SENT
            ])->one();
        if ($installment) {
            $installment->status = Installment::STATUS_CANCEL;
            $installment->time_updated = time();
            $installment->user_updated = '9999';
            if ($installment->save()) {
                $error_message = '';
            } else {
                $error_message = 'Update installment record fail';
            }
        } else {
            $error_message = 'Installment record not exists';
        }

        return $error_message;
    }

}