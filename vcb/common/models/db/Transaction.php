<?php

namespace common\models\db;

use common\components\libs\Tables;
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
 * @property integer $card_token_id
 * @property integer $installment_conversion
 * @property integer $installment_fee
 * @property integer $installment_fee_merchant
 * @property integer $installment_fee_buyer
 * @property string $authorization_code
 * @property string $bank_code_payment
 * @property false|mixed|string|null $card_info
 */
class Transaction extends MyActiveRecord {

    const STATUS_NEW = 1;
    const STATUS_PAYING = 2;
    const STATUS_CANCEL = 3;
    const STATUS_PAID = 4;
    const STATUS_FAILURE = 5;
    const STATUS_REVERT = 15;
    const InstallmentConversion_NEW = 0;
    const InstallmentConversion_PAID= 1;
    const InstallmentConversion_SEND = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['transaction_type_id', 'partner_id', 'merchant_id', 'payment_method_id', 'amount', 'sender_account_id', 'receiver_account_id', 'currency', 'status'], 'required'],
            [['transaction_type_id', 'partner_id', 'merchant_id', 'checkout_order_id', 'cashout_id', 'payment_method_id', 'partner_payment_id', 'reason_id', 'status', 'sender_account_id', 'receiver_account_id', 'refer_transaction_id', 'time_created', 'time_updated', 'time_paid', 'user_created', 'user_updated', 'user_paid','installment_conversion'], 'integer'],
            [['amount', 'sender_fee', 'receiver_fee', 'partner_payment_sender_fee', 'partner_payment_receiver_fee','installment_fee','installment_fee_merchant','installment_fee_buyer'], 'number'],
            [['reason', 'partner_payment_info', 'bank_code_payment'], 'string'],
            [['partner_payment_method_refer_code', 'currency', 'bank_refer_code'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'transaction_type_id' => 'Transaction Type ID',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'checkout_order_id' => 'Checkout Order ID',
            'cashout_id' => 'cashout_id',
            'payment_method_id' => 'Payment Method ID',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_method_refer_code' => 'Partner Payment Method Refer Code',
            'partner_payment_info' => 'partner_payment_info',
            'bank_refer_code' => 'bank_refer_code',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'sender_account_id' => 'sender_account_id',
            'receiver_account_id' => 'receiver_account_id',
            'sender_fee' => 'Sender Fee',
            'receiver_fee' => 'Receiver Fee',
            'partner_payment_sender_fee' => 'partner_payment_sender_fee',
            'partner_payment_receiver_fee' => 'partner_payment_receiver_fee',
            'reason_id' => 'Reason ID',
            'reason' => 'Reason',
            'status' => 'Status',
            'refer_transaction_id' => 'Refer Transaction ID',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_cancel' => 'time_cancel',
            'user_cancel' => 'user_cancel',
            'time_paid' => 'Time Paid',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_paid' => 'User Paid',
            'installment_conversion' => 'Installment Conversion',
            'installment_fee' => 'Installment Fee',
            'installment_fee_merchant' => 'Installment Fee Merchant',
            'installment_fee_buyer' => 'Installment Fee Buyer'
        ];
    }

    public static function getStatus() {
        return array(
            self::STATUS_NEW => 'Chờ xử lý',
            self::STATUS_PAYING => 'Đang xử lý',
            self::STATUS_PAID => 'Đã hoàn thành',
            self::STATUS_CANCEL => 'Đã hủy',
        );
    }

    public static function getStatusInstallmentConversion()
    {
        return array(
            self::InstallmentConversion_NEW => 'Chưa duyệt trả góp',
            self::InstallmentConversion_PAID => 'Chưa gửi CĐTG',
            self::InstallmentConversion_SEND => 'Đã gửi CĐTG'
        );
    }

    public static function getOperators() {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'paid' => array('title' => 'Cập nhật giao dịch', 'confirm' => false),
            'cancel' => array('title' => 'Hủy giao dịch', 'confirm' => false),
            'add-deposit' => array('title' => 'Thêm mới phiếu thu', 'confirm' => false, 'check-all' => true)
        );
    }

    public static function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        if ($row['transaction_type_id'] == TransactionType::getDepositTransactionTypeId()) {
            switch ($row['status']) {
                case self::STATUS_NEW:
                    $result['paid'] = $operators['paid'];
                    $result['cancel'] = $operators['cancel'];
            }
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
        $cashout_ids = array();
        $merchant_ids = array();
        $payment_method_ids = array();
        $partner_payment_ids = array();
        $transaction_type_ids = array();
        $reason_ids = array();
        $merchants = array();
        $payment_methods = array();
        $partner_payments = array();
        $transaction_types = array();
        $checkout_orders = array();
        $cashouts = array();
        $reasons = array();
        $partner_payment_account_ids = array();

        foreach ($rows as $row) {
            if (intval($row['merchant_id']) > 0) {
                $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            }
            if (intval($row['payment_method_id']) > 0) {
                $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
            }
            if (intval($row['partner_payment_id']) > 0) {
                $partner_payment_ids[$row['partner_payment_id']] = $row['partner_payment_id'];
            }
            if (intval($row['transaction_type_id']) > 0) {
                $transaction_type_ids[$row['transaction_type_id']] = $row['transaction_type_id'];
            }
            if (intval($row['checkout_order_id']) > 0) {
                $checkout_order_ids[$row['checkout_order_id']] = $row['checkout_order_id'];
            }
            if (intval($row['cashout_id']) > 0) {
                $cashout_ids[$row['cashout_id']] = $row['cashout_id'];
            }
            if (intval($row['reason_id']) > 0) {
                $reason_ids[$row['reason_id']] = $row['reason_id'];
            }
            if (intval($row['partner_payment_account_id']) > 0) {
                $partner_payment_account_ids[$row['partner_payment_account_id']] = $row['partner_payment_account_id'];
            }
        }
        if (!empty($merchant_ids)) {
            $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        }
        if (!empty($payment_method_ids)) {
            $payment_methods = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") ", "", "id");
        }
        if (!empty($partner_payment_ids)) {
            $partner_payments = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ") ", "", "id");
        }
        if (!empty($transaction_type_ids)) {
            $transaction_types = Tables::selectAllDataTable("transaction_type", "id IN (" . implode(',', $transaction_type_ids) . ") ", "", "id");
        }
        if (!empty($checkout_order_ids)) {
            $checkout_orders = Tables::selectAllDataTable("checkout_order", "id IN (" . implode(',', $checkout_order_ids) . ") ", "", "id");
        }
        if (!empty($cashout_ids)) {
            $cashouts = Tables::selectAllDataTable("cashout", "id IN (" . implode(',', $cashout_ids) . ") ", "", "id");
        }
        if (!empty($reason_ids)) {
            $reasons = Tables::selectAllDataTable("reason", "id IN (" . implode(',', $reason_ids) . ") ", "", "id");
        }
        if (!empty($partner_payment_account_ids)) {
            $partner_payment_accounts = Tables::selectAllDataTable("partner_payment_account", "id IN (" . implode(',', $partner_payment_account_ids) . ") ", "", "id");
        }

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['payment_method_info'] = @$payment_methods[$row['payment_method_id']];
            $rows[$key]['partner_payment_info'] = @$partner_payments[$row['partner_payment_id']];
            $rows[$key]['transaction_type_info'] = @$transaction_types[$row['transaction_type_id']];
            $rows[$key]['checkout_order_info'] = @$checkout_orders[$row['checkout_order_id']];
            $rows[$key]['cashout_info'] = $cashouts[$row['cashout_id']] ?? '';
            $rows[$key]['reason_info'] = $reasons[$row['reason_id']] ?? '';
            $rows[$key]['partner_payment_account_info'] = @$partner_payment_accounts[$row['partner_payment_account_id']];
            $rows[$key]['operators'] = Transaction::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function getPartnerPaymentAmount($transaction_info) {
        if (TransactionType::isPaymentTransactionType($transaction_info['transaction_type_id'])) {
            return $transaction_info['amount'] + $transaction_info['sender_fee'];
        }elseif(TransactionType::isInstallmentTransactionType($transaction_info['transaction_type_id']))
        {
            return $transaction_info['amount'] + $transaction_info['sender_fee'] + $transaction_info['partner_payment_sender_fee'];
        }
        return $transaction_info['amount'];
    }

    public static function getByBankReferCode($bank_refer_code) {
        $transaction_info = Transaction::find()
                ->where(['bank_refer_code' => $bank_refer_code])
                ->asArray()
                ->one();
        if (!is_null($transaction_info)) {
            return $transaction_info;
        }
        return false;
    }

    public static function getTransactionByCheckoutOrderId($checkout_order_id) {
        $transaction = Transaction::find()
            ->where(['checkout_order_id' => $checkout_order_id])
            ->asArray()
            ->one();

        if (!is_null($transaction)) {
            return $transaction;
        }
        return false;
    }

    public static function getTransactionById($transaction_id) {
        $transaction = Transaction::find()
            ->where(['id' => $transaction_id])
            ->asArray()
            ->one();

        if (!is_null($transaction)) {
            return $transaction;
        }
        return false;
    }

    public static function insertCardInfo($transactionId, $data)
    {
        $transaction = Transaction::findOne(['id' => $transactionId]);
        if ($transaction) {
            $transaction->card_info = json_encode($data);
            $transaction->save();
            return true;
        }
        return false;
    }



}
