<?php

namespace common\models\business;

use Yii;

use common\models\db\PaymentTransactionReceipt;
use common\components\libs\Tables;

class PaymentTransactionReceiptBusiness
{

    public static function getByPaymentTransactionId($id)
    {
        return PaymentTransactionReceipt::findOne(['payment_transaction_id' => $id]);
    }

    /**
     *
     * @param type $params : payment_transaction_id, receipt
     * @return type
     */
    public static function editPPMReceipt($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransactionReceipt::getDb()->beginTransaction();
        }
        //------------
        $payment_transaction_receipt = self::getByPaymentTransactionId($params['id']);
        if ($payment_transaction_receipt != null) {
            $payment_transaction_receipt->partner_payment_method_receipt = trim($params['partner_payment_method_receipt']);
            if ($payment_transaction_receipt->validate() && $payment_transaction_receipt->save()) {
                $error_message = '';
                $commit = true;
            } else {
                $error_message = 'Có lỗi khi thay đổi mã chứng từ thanh toán';
            }
        }

        if ($rollback) {
            if ($commit == true) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        }
        return array('error_message' => $error_message);
    }

    /**
     *
     * @param type $params : payment_transaction_id, receipt
     * @return type
     */
    public
    static function add($params, $rollback = true)
    {
        $error_message = 'Lỗi không xác định';
        $id = 0;
        $commit = false;
        //------------
        if ($rollback) {
            $transaction = PaymentTransactionReceipt::getDb()->beginTransaction();
        }
        //------------
        $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $params['payment_transaction_id'] . " ");
        if ($payment_transaction_info != false) {
            $params['receipt'] = trim($params['receipt']);
            if (!PaymentTransactionReceipt::isExists($params['receipt'], $params['payment_transaction_id'], $receipt_info)) {
                $model = new PaymentTransactionReceipt();
                $model->payment_transaction_id = $params['payment_transaction_id'];
                $model->payment_transaction_type = $payment_transaction_info['type'];
                $model->payment_method_id = $payment_transaction_info['payment_method_id'];
                $model->partner_payment_id = $payment_transaction_info['partner_payment_id'];
                $model->partner_payment_method_receipt = $params['receipt'];
                $model->time_created = time();
                if ($model->validate() && $model->save()) {
                    $error_message = '';
                    $commit = true;
                    $id = $model->getDb()->lastInsertID;
                } else {
                    $error_message = 'Có lỗi khi thêm chứng từ thanh toán';
                }
            } else {
                if ($receipt_info['payment_transaction_id'] == $params['payment_transaction_id']) {
                    $error_message = '';
                    $commit = true;
                    $id = $receipt_info['id'];
                } else {
                    $error_message = 'Mã chứng từ thanh toán đã tồn tại';
                }
            }
        } else {
            $error_message = 'Mã giao dịch thanh toán không hợp lệ';
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

?>