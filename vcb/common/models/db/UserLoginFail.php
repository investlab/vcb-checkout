<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_login_fail".
 *
 * @property integer $id
 * @property integer $user_login_id
 * @property integer $time_failed
 */
class UserLoginFail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_login_fail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_login_id', 'time_failed'], 'required'],
            [['user_login_id', 'time_failed'], 'integer'],
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
            'time_failed' => 'Time Failed',
        ];
    }
}
