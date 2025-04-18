<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "card_price".
 *
 * @property integer $id
 * @property integer $card_type_id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property double $price
 * @property string $currency
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class CardPrice extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_type_id', 'name', 'code', 'price', 'currency', 'status'], 'required'],
            [['card_type_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['description'], 'string'],
            [['price'], 'number'],
            [['name', 'code'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 10],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_type_id' => 'Card Type ID',
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'price' => 'Price',
            'currency' => 'Currency',
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
