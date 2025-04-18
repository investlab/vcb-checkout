<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $fee
 * @property integer $type
 * @property integer $position
 * @property string $description
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class Payment extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fee', 'type', 'position', 'status', 'time_created', 'time_updated'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50]
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
            'code' => 'Code',
            'fee' => 'Fee',
            'type' => 'Type',
            'position' => 'Position',
            'description' => 'Description',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }
}
