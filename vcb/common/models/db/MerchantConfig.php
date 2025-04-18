<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "bank".
 *
 * @property integer $id
 * @property string $name
 * @property string $trade_name
 * @property string $code
 * @property string $description
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class MerchantConfig extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    const AUTO_SETTLE_CYBER_SOURCE_ON = 1;
    const AUTO_SETTLE_CYBER_SOURCE_OFF = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'trade_name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
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
            'name' => 'Name',
            'trade_name' => 'Trade Name',
            'code' => 'Code',
            'description' => 'Description',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

}
