<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_payment_refer_code".
 *
 * @property integer $id
 * @property integer $partner_payment_id
 * @property string $partner_payment_refer_code
 * @property integer $transaction_type_id
 * @property integer $transaction_id
 * @property integer $time_created
 * @property integer $user_created
 */
class PartnerPaymentReferCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_payment_refer_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_payment_id', 'partner_payment_refer_code', 'transaction_type_id', 'transaction_id'], 'required'],
            [['partner_payment_id', 'transaction_type_id', 'transaction_id', 'time_created', 'user_created'], 'integer'],
            [['partner_payment_refer_code'], 'string', 'max' => 50],
            [['partner_payment_id', 'partner_payment_refer_code', 'transaction_type_id'], 'unique', 'targetAttribute' => ['partner_payment_id', 'partner_payment_refer_code', 'transaction_type_id'], 'message' => 'The combination of Partner Payment ID, Partner Payment Refer Code and Transaction Type ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_refer_code' => 'Partner Payment Refer Code',
            'transaction_type_id' => 'Transaction Type ID',
            'transaction_id' => 'Transaction ID',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }
}
