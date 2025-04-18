<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "merchant_payment_method".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property integer $payment_method_id
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class MerchantPaymentMethod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant_payment_method';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'merchant_id', 'payment_method_id'], 'required'],
            [['partner_id', 'merchant_id', 'payment_method_id', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['merchant_id', 'payment_method_id'], 'unique', 'targetAttribute' => ['merchant_id', 'payment_method_id'], 'message' => 'The combination of Merchant ID and Payment Method ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'payment_method_id' => 'Payment Method ID',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
}
