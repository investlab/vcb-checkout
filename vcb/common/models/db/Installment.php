<?php

namespace common\models\db;

use common\components\libs\AuditEntryBehaviors;
use common\components\libs\Tables;
use common\components\utils\Translate;
use Yii;

/**
 * This is the model class for table "transaction".
 *
 * @property integer $id
 * @property integer $transaction_type_id
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property integer $checkout_order_id
 * @property integer $cashout_id
 * @property integer $payment_method_id
 * @property integer $partner_payment_id
 * @property string $partner_payment_method_refer_code
 * @property string $partner_payment_info
 * @property string $bank_refer_code
 * @property double $amount
 * @property integer $sender_account_id
 * @property integer $receiver_account_id
 * @property double $sender_fee
 * @property double $receiver_fee
 * @property double $partner_payment_sender_fee
 * @property double $partner_payment_receiver_fee
 * @property string $currency
 * @property integer $reason_id
 * @property string $reason
 * @property integer $status
 * @property integer $refer_transaction_id
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_paid
 * @property integer $time_cancel
 * @property integer $user_created
 * @property integer $user_cancel
 * @property integer $user_updated
 * @property integer $user_paid
 * @property integer $installment_conversion
 * @property mixed|null $card_voucher_requirement_id
 * @property string $authorization_code
 */
class Installment extends MyActiveRecord {

    const STATUS_WAIT_SENT = 1;
    const STATUS_SENT = 2;
    const STATUS_CANCEL = 3;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'installment';
    }

//    public function behaviors()
//    {
//        return [
//            'auditEntryBehaviors' => [
//                'class' => AuditEntryBehaviors::class,
//            ],
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['checkout_order_id', 'status', 'time_created', 'user_created'], 'required'],
            [['checkout_order_id', 'status', 'time_created', 'user_created', 'time_updated', 'user_updated'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'checkout_order_id' => 'Mã đơn hàng',
            'partner_id' => 'Partner ID',
        ];
    }

    public static function getStatus() {
        return array(
            self::STATUS_WAIT_SENT => Translate::get("Đang chờ gửi CĐTG"),
            self::STATUS_SENT => Translate::get("Đã gửi CĐTG"),
            self::STATUS_CANCEL => Translate::get("Đã huỷ CĐGT & GDTT"),
        );
    }


    public static function getOperators() {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'cancel' => array('title' => 'Hủy giao dịch', 'confirm' => false),
        );
    }

    public static function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        switch ($row['status']) {
            case self::STATUS_WAIT_SENT:
                $result['cancel'] = $operators['cancel'];
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row) {
        $transaction_type_id = $row['transaction_type_id'];
        if (intval($transaction_type_id) > 0) {
            $transaction_type = Tables::selectOneDataTable('transaction_type', ['id = :id', 'id' => $transaction_type_id]);
            $row['transaction_type_info'] = $transaction_type;
        }

        $merchant_id = $row['merchant_id'];
        if (intval($merchant_id) > 0) {
            $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);
            $row['merchant_info'] = $merchant;
        }
        $checkout_order_id = $row['checkout_order_id'];
        if (intval($checkout_order_id) > 0) {
            $checkout_order = Tables::selectOneDataTable('checkout_order', ['id = :id', 'id' => $checkout_order_id]);
            $row['checkout_order_info'] = CheckoutOrder::setRow($checkout_order);
        }
        $payment_method_id = $row['payment_method_id'];
        if (intval($payment_method_id) > 0) {
            $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
            $row['payment_method_info'] = $payment_method;
        }

        $partner_payment_id = $row['partner_payment_id'];
        if (intval($partner_payment_id) > 0) {
            $partner_payment = Tables::selectOneDataTable('partner_payment', ['id = :id', 'id' => $partner_payment_id]);
            $row['partner_payment'] = $partner_payment;
        }

        $reason_id = $row['reason_id'];
        if (intval($reason_id) > 0) {
            $reason = Tables::selectOneDataTable('reason', ['id = :id', 'id' => $reason_id]);
            $row['reason_info'] = $reason;
        }

        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows) {
        $checkout_order_ids = array();
        foreach ($rows as $row) {
            if (intval($row['checkout_order_id']) > 0) {
                $checkout_order_ids[$row['checkout_order_id']] = $row['checkout_order_id'];
            }
        }
        if (!empty($checkout_order_ids)) {
            $checkout_orders = Tables::selectAllDataTable("checkout_order", "id IN (" . implode(',', $checkout_order_ids) . ") ", "", "id");
            $checkout_orders = CheckoutOrder::setRows($checkout_orders);

        }

        foreach ($rows as $key => $row) {
            $rows[$key]['checkout_order'] = $checkout_orders[$row['checkout_order_id']];
            $rows[$key]['operators'] = Installment::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function getPartnerPaymentAmount($transaction_info) {
        if (TransactionType::isPaymentTransactionType($transaction_info['transaction_type_id']) || TransactionType::isInstallmentTransactionType($transaction_info['transaction_type_id'])) {
            return $transaction_info['amount'] + $transaction_info['sender_fee'];
        }
        return $transaction_info['amount'];
    }

    public static function getByBankReferCode($bank_refer_code) {
        $transaction_info = Installment::find()
                ->where(['bank_refer_code' => $bank_refer_code])
                ->asArray()
                ->one();
        if (!is_null($transaction_info)) {
            return $transaction_info;
        }
        return false;
    }

    public static function getTransactionByCheckoutOrderId($checkout_order_id) {
        $transaction = Installment::find()
            ->where(['checkout_order_id' => $checkout_order_id])
            ->asArray()
            ->one();

        if (!is_null($transaction)) {
            return $transaction;
        }
        return false;
    }

    public static function getTransactionById($transaction_id) {
        $transaction = Installment::find()
            ->where(['id' => $transaction_id])
            ->asArray()
            ->one();

        if (!is_null($transaction)) {
            return $transaction;
        }
        return false;
    }


}
