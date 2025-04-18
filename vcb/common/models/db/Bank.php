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
class Bank extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bank';
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

    public function getInstallment_bank()
    {
        return $this->hasOne(InstallmentBank::className(), ['bank_id' => 'id']);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['code', 'id']);
    }

}
