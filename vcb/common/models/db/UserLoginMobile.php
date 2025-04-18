<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_login_mobile".
 *
 * @property integer $id
 * @property integer $user_login_id
 * @property string $mobile
 * @property integer $time_created
 */
class UserLoginMobile extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_login_mobile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_login_id', 'mobile'], 'required'],
            [['user_login_id', 'time_created'], 'integer'],
            [['mobile'], 'string', 'max' => 20],
            [['user_login_id'], 'unique'],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_login_id' => 'User Login ID',
            'mobile' => 'Mobile',
            'time_created' => 'Time Created',
        ];
    }
}
