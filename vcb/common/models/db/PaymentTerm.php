<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "payment_term".
 *
 * @property integer $id
 * @property string $name
 * @property integer $day_in_month
 * @property integer $day_in_week
 * @property integer $number_day
 * @property string $description
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PaymentTerm extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_term';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['day_in_month', 'day_in_week', 'number_day', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'day_in_month' => 'Day In Month',
            'day_in_week' => 'Day In Week',
            'number_day' => 'Number Day',
            'description' => 'Description',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }
}
