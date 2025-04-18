<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "payment_info".
 *
 * @property integer $id
 * @property string $description
 * @property integer $user_id
 * @property integer $time_updated
 */
class PaymentInfo extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['user_id', 'time_updated'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'user_id' => 'User ID',
            'time_updated' => 'Time Updated',
        ];
    }
}
