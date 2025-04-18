<?php

namespace common\models\business;

use Yii;
use common\models\db\PaymentTransactionRefund;
use common\models\db\PaymentTransaction;
use common\components\libs\Tables;

class PaymentTransactionRefundBusiness
{

    /**
     *
     * @param type $params : refund_transaction_id, user_id
     * @return type
     */
    public static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransactionRefund::getDb()->beginTransaction();
        }
        //------------

        $refund_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $params['refund_transaction_id'] . " AND type = " . PaymentTransaction::TYPE_REFUND . " ");
        if ($refund_transaction_info != false) {
            $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $refund_transaction_info['related_payment_transaction_id'] . " AND type = " . PaymentTransaction::TYPE_PAYMENT . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_REFUND . ") ");
            if ($payment_transaction_info != false) {
                $all = true;
                $payment_transaction_refund_info = Tables::selectAllDataTable("payment_transaction_refund", "payment_transaction_id = " . $payment_transaction_info['id']);
                if ($payment_transaction_refund_info != false) {
                    $refund_amount = 0;
                    foreach ($payment_transaction_refund_info as $row) {
                        if ($row['refund_transaction_id'] != $params['refund_transaction_id']) {
                            $refund_amount += $row['amount'];
                        } else {
                            $all = false;
                            $error_message = 'Dữ liệu không hợp lệ';
                            break;
                        }
                    }
                    if (($refund_amount + $refund_transaction_info['amount']) > $payment_transaction_info['amount']) {
                        $error_message = 'Số tiền muốn hoàn không hợp lệ';
                        $all = false;
                    }
                }
                if ($all) {
                    $model = new PaymentTransactionRefund();
                    $model->payment_transaction_id = $payment_transaction_info['id'];
                    $model->refund_transaction_id = $params['refund_transaction_id'];
                    $model->amount = $refund_transaction_info['amount'];
                    $model->time_created = time();
                    $model->user_created = $params['user_id'];
                    if ($model->validate() && $model->save()) {
                        $error_message = '';
                        $commit = true;
                        $id = $model->getDb()->lastInsertID;
                    } else {
                        $error_message = 'Có lỗi khi thêm giao dịch hoàn tiền';
                    }
                }
            } else {
                $error_message = 'Mã giao dịch thanh toán không hợp lệ';
            }
        } else {
            $error_message = 'Giao dịch hoàn tiền không hợp lệ';
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
