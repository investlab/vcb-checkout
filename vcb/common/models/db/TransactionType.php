<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "transaction_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $time_created
 * @property integer $user_created
 */
class TransactionType extends \yii\db\ActiveRecord {

    const REFUND = 3;
    const WITHDRAW_CARD_VOUCHER = 6;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'transaction_type';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['time_created', 'user_created'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }

    public static function getPaymentTransactionTypeId() {
        return 1;
    }

    public static function getWithdrawTransactionTypeId() {
        return 2;
    }

    public static function getRefundTransactionTypeId() {
        return self::REFUND;
    }

    public static function getDepositTransactionTypeId() {
        return 4;
    }

    public static function getInstallmentTransactionTypeId() {
        return 5;
    }

    public static function getWithdrawTransactionCardVoucherTypeId(): int
    {
        return 6;
    }


    public static function isPaymentTransactionType($transaction_type_id) {
        return (self::getPaymentTransactionTypeId() == $transaction_type_id);
    }

    public static function isWithdrawTransactionType($transaction_type_id) {
        return (self::getWithdrawTransactionTypeId() == $transaction_type_id);
    }

    public static function isRefundTransactionType($transaction_type_id) {
        return (self::getRefundTransactionTypeId() == $transaction_type_id);
    }

    public static function isDepositTransactionType($transaction_type_id) {
        return (self::getDepositTransactionTypeId() == $transaction_type_id);
    }

    public static function isInstallmentTransactionType($transaction_type_id) {
        return (self::getInstallmentTransactionTypeId() == $transaction_type_id);
    }

    public static function isWithdrawTransactionCardVoucherType($transaction_type_id): bool
    {
        return (self::getWithdrawTransactionCardVoucherTypeId() == $transaction_type_id);
    }


}
