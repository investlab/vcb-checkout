<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "cashout_checkout_order".
 *
 * @property integer $id
 * @property integer $cashout_id
 * @property integer $checkout_order_id
 * @property integer $time_created
 * @property integer $user_created
 */
class CashoutCheckoutOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cashout_line';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cashout_id', 'checkout_order_id'], 'required'],
            [['cashout_id', 'checkout_order_id', 'time_created', 'user_created'], 'integer'],
            [['checkout_order_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cashout_id' => 'Cashout ID',
            'checkout_order_id' => 'Checkout Order ID',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }
}
