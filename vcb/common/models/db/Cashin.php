<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "cashin".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $bill_id
 * @property double $amount
 * @property integer $payment_method_id
 * @property double $payment_method_fee
 * @property string $payment_info
 * @property integer $partner_payment_id
 * @property double $partner_payment_fee
 * @property string $partner_payment_transaction_id
 * @property integer $partner_payment_account_id
 * @property integer $receiver_account_id
 * @property integer $deposit_transaction_id
 * @property integer $fee_transaction_id
 * @property integer $installment_bank_id
 * @property integer $installment_period
 * @property integer $installment_amount
 * @property integer $installment_fee
 * @property integer $installment_transaction_id
 * @property integer $installment_bank_refer_code
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_paid
 * @property integer $time_perform
 * @property integer $time_cancel
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_paid
 * @property integer $user_perform
 * @property integer $user_cancel
 */
class Cashin extends MyActiveRecord
{

    const TYPE_PAYMENT = 1;
    const TYPE_DEPOSIT = 2;
    const STATUS_NOT_PAYMENT = 1;
    const STATUS_PAYING = 2;
    const STATUS_PAID = 3;
    const STATUS_PERFORM = 4;
    const STATUS_CANCEL = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cashin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'amount', 'payment_method_id', 'partner_payment_id', 'receiver_account_id', 'status'], 'required'],
            [['type', 'bill_id', 'payment_method_id', 'partner_payment_id', 'partner_payment_account_id', 'receiver_account_id', 'deposit_transaction_id',
                'fee_transaction_id', 'installment_bank_id', 'installment_period', 'installment_transaction_id',
                'status', 'time_created', 'time_updated', 'time_paid', 'time_perform', 'time_cancel', 'user_created',
                'user_updated', 'user_paid', 'user_perform', 'user_cancel'], 'integer'],
            [['amount', 'payment_method_fee', 'partner_payment_fee', 'installment_amount', 'installment_fee'], 'number'],
            [['payment_info', 'installment_bank_refer_code'], 'string'],
            [['partner_payment_transaction_id'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'bill_id' => 'Bill ID',
            'amount' => 'Amount',
            'payment_method_id' => 'Payment Method ID',
            'payment_method_fee' => 'Payment Method Fee',
            'payment_info' => 'Payment Info',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_fee' => 'Partner Payment Fee',
            'partner_payment_transaction_id' => 'Partner Payment Transaction ID',
            'partner_payment_account_id' => 'Partner Payment Account ID',
            'receiver_account_id' => 'Receiver Account ID',
            'deposit_transaction_id' => 'Deposit Transaction ID',
            'fee_transaction_id' => 'Fee Transaction ID',
            'installment_bank_id' => 'Ngân hàng trả góp',
            'installment_period' => 'Kỳ trả góp',
            'installment_amount' => 'Số tiền trả góp',
            'installment_fee' => 'Phí trả góp',
            'installment_transaction_id' => 'Mã giao dịch trả góp',
            'installment_bank_refer_code' => 'Mã chuẩn chi',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_paid' => 'Time Paid',
            'time_perform' => 'Time Perform',
            'time_cancel' => 'Time Cancel',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_paid' => 'User Paid',
            'user_perform' => 'User Perform',
            'user_cancel' => 'User Cancel',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NOT_PAYMENT => 'Chưa thanh toán',
            self::STATUS_PAYING => 'Đang thanh toán',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_PERFORM => 'Đã chuyển ngân',
            self::STATUS_CANCEL => 'Đã hủy',
        );
    }

}
