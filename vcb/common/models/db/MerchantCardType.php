<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "merchant_card_type".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $card_type_id
 * @property integer $bill_type
 * @property integer $cycle_day
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class MerchantCardType extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant_card_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'card_type_id', 'bill_type', 'cycle_day', 'status', 'time_created'], 'required'],
            [['merchant_id', 'card_type_id', 'bill_type', 'cycle_day', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['merchant_id', 'card_type_id'], 'unique', 'targetAttribute' => ['merchant_id', 'card_type_id'], 'message' => 'The combination of Merchant ID and Card Type ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant ID',
            'card_type_id' => 'Card Type ID',
            'bill_type' => 'Bill Type',
            'cycle_day' => 'Cycle Day',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }
}
