<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "card_log_backup".
 *
 * @property integer $id
 * @property string $version
 * @property integer $merchant_id
 * @property string $merchant_refer_code
 * @property integer $bill_type
 * @property integer $cycle_day
 * @property integer $card_type_id
 * @property string $card_code
 * @property string $card_serial
 * @property double $card_price
 * @property double $card_amount
 * @property string $currency
 * @property integer $partner_card_id
 * @property integer $partner_card_log_id
 * @property string $partner_card_refer_code
 * @property double $percent_fee
 * @property integer $withdraw_time_limit
 * @property string $merchant_input
 * @property string $merchant_output
 * @property string $result_code
 * @property integer $card_status
 * @property integer $transaction_status
 * @property integer $card_transaction_id
 * @property integer $backup_status
 * @property integer $time_card_updated
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_create_transaction
 * @property integer $user_created
 * @property integer $user_updated
 */
class CardLogBackup extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_log_backup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'merchant_id', 'merchant_refer_code', 'bill_type', 'cycle_day', 'card_type_id', 'card_code', 'currency', 'card_status', 'transaction_status', 'backup_status'], 'required'],
            [['merchant_id', 'bill_type', 'cycle_day', 'card_type_id', 'partner_card_id', 'partner_card_log_id', 'withdraw_time_limit', 'card_status', 'transaction_status', 'card_transaction_id', 'backup_status', 'time_card_updated', 'time_created', 'time_updated', 'time_create_transaction', 'user_created', 'user_updated'], 'integer'],
            [['card_price', 'card_amount', 'percent_fee'], 'number'],
            [['merchant_input', 'merchant_output'], 'string'],
            [['version', 'card_code', 'currency', 'result_code'], 'string', 'max' => 20],
            [['merchant_refer_code', 'partner_card_refer_code'], 'string', 'max' => 255],
            [['card_serial'], 'string', 'max' => 30],
            [['merchant_id', 'merchant_refer_code'], 'unique', 'targetAttribute' => ['merchant_id', 'merchant_refer_code'], 'message' => 'The combination of Merchant ID and Merchant Refer Code has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'merchant_id' => 'Merchant ID',
            'merchant_refer_code' => 'Merchant Refer Code',
            'bill_type' => 'Bill Type',
            'cycle_day' => 'Cycle Day',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_amount' => 'Card Amount',
            'currency' => 'Currency',
            'partner_card_id' => 'Partner Card ID',
            'partner_card_log_id' => 'Partner Card Log ID',
            'partner_card_refer_code' => 'Partner Card Refer Code',
            'percent_fee' => 'Percent Fee',
            'withdraw_time_limit' => 'Withdraw Time Limit',
            'merchant_input' => 'Merchant Input',
            'merchant_output' => 'Merchant Output',
            'result_code' => 'Result Code',
            'card_status' => 'Card Status',
            'transaction_status' => 'Transaction Status',
            'card_transaction_id' => 'Card Transaction ID',
            'backup_status' => 'Backup Status',
            'time_card_updated' => 'Time Card Updated',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_create_transaction' => 'Time Create Transaction',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
}
