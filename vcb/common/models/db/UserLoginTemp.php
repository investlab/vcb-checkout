<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_login_temp".
 *
 * @property integer $id
 * @property string $fullname
 * @property string $email
 * @property string $mobile
 * @property string $password
 * @property integer $gender
 * @property integer $birthday
 * @property integer $user_login_id
 * @property integer $time_limit
 * @property integer $time_created
 * @property integer $time_updated
 */
class UserLoginTemp extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_login_temp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'password', 'time_limit'], 'required'],
            [['id', 'gender', 'birthday', 'user_login_id', 'time_limit', 'time_created', 'time_updated'], 'integer'],
            [['fullname', 'email'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fullname' => 'Fullname',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'password' => 'Password',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'user_login_id' => 'User Login ID',
            'time_limit' => 'Time Limit',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }
}
