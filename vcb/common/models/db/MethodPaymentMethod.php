<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "method_payment_method".
 *
 * @property integer $id
 * @property integer $method_id
 * @property integer $payment_method_id
 * @property integer $time_created
 * @property integer $user_created
 */
class MethodPaymentMethod extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'method_payment_method';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['method_id', 'payment_method_id'], 'required'],
            [['method_id', 'payment_method_id', 'time_created', 'user_created'], 'integer'],
            [['method_id', 'payment_method_id'], 'unique', 'targetAttribute' => ['method_id', 'payment_method_id'], 'message' => 'The combination of Method ID and Payment Method ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'method_id' => 'Method ID',
            'payment_method_id' => 'Payment Method ID',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }
}
