<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_history".
 *
 * @property integer $idi
 * @property integer $user_id
 * @property string $username
 * @property integer $refer_type
 * @property integer $refer_id
 * @property string $description
 * @property integer $time_created
 * @property integer $user_created
 */
class UserHistory extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'username', 'refer_type'], 'required'],
            [['user_id', 'refer_type', 'refer_id', 'time_created', 'user_created'], 'integer'],
            [['description'], 'string'],
            [['username'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idi' => 'Idi',
            'user_id' => 'User ID',
            'username' => 'Username',
            'refer_type' => 'Refer Type',
            'refer_id' => 'Refer ID',
            'description' => 'Description',
            'time_created' => 'Time Created',
            'user_created' => 'User Created',
        ];
    }
}
