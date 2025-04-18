<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "payment_transaction_refund".
 *
 * @property integer $id
 * @property integer $payment_transaction_id
 * @property integer $refund_transaction_id
 * @property double $amount
 * @property integer $time_created
 * @property integer $user_created
 */
class PaymentTransactionRefund extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_transaction_refund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_transaction_id', 'refund_transaction_id', 'amount'], 'required'],
            [['payment_transaction_id', 'refund_transaction_id', 'time_created', 'user_created'], 'integer'],
            [['amount'], 'number'],
            [['refund_transaction_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_transaction_id' => 'Payment Transaction ID',
            'refund_transaction_id' => 'Refund Transaction ID',
            'amount' => 'Amount',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }
}
