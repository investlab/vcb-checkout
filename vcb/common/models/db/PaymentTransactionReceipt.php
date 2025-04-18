<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "payment_transaction_receipt".
 *
 * @property integer $id
 * @property integer $payment_transaction_id
 * @property integer $payment_transaction_type
 * @property integer $payment_method_id
 * @property integer $partner_payment_id
 * @property string $partner_payment_method_receipt
 * @property integer $time_created
 */
class PaymentTransactionReceipt extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_transaction_receipt';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_transaction_id', 'payment_transaction_type', 'payment_method_id', 'partner_payment_id', 'partner_payment_method_receipt'], 'required'],
            [['payment_transaction_id', 'payment_transaction_type', 'payment_method_id', 'partner_payment_id', 'time_created'], 'integer'],
            [['partner_payment_method_receipt'], 'string', 'max' => 50],
            [['payment_transaction_id'], 'unique'],
            [['payment_transaction_type', 'payment_method_id', 'partner_payment_id', 'partner_payment_method_receipt'], 'unique', 'targetAttribute' => ['payment_transaction_type', 'payment_method_id', 'partner_payment_id', 'partner_payment_method_receipt'], 'message' => 'The combination of payment_transaction_type Payment Method ID, Partner Payment ID and Partner Payment Method Receipt has already been taken.']
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
            'payment_transaction_type' => 'payment_transaction_type',
            'payment_method_id' => 'Payment Method ID',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_method_receipt' => 'Partner Payment Method Receipt',
            'time_created' => 'Time Created',
        ];
    }

    public function beforeValidate()
    {
        $this->partner_payment_method_receipt = trim($this->partner_payment_method_receipt);
        return parent::beforeValidate();
    }

    public static function isExists($receipt, $payment_transaction_id, &$receipt_info = null)
    {
        $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $payment_transaction_id . " ");
        if ($payment_transaction_info != false) {
            $receipt_info = Tables::selectOneDataTable("payment_transaction_receipt", "payment_transaction_type = " . $payment_transaction_info['type'] . " AND payment_method_id = " . $payment_transaction_info['payment_method_id'] . " AND partner_payment_id = " . $payment_transaction_info['partner_payment_id'] . " AND partner_payment_method_receipt = '$receipt' ");
            if ($receipt_info != false) {
                return true;
            }
        }
        return false;

    }
}
