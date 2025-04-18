<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_login_social_network".
 *
 * @property integer $id
 * @property integer $user_login_id
 * @property integer $social_network_id
 * @property string $social_network_account_id
 * @property integer $time_created
 */
class UserLoginSocialNetwork extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_login_social_network';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_login_id', 'social_network_id', 'social_network_account_id'], 'required'],
            [['user_login_id', 'social_network_id', 'time_created'], 'integer'],
            [['social_network_account_id'], 'string', 'max' => 255],
            [['social_network_id', 'social_network_account_id'], 'unique', 'targetAttribute' => ['social_network_id', 'social_network_account_id'], 'message' => 'The combination of Social Network ID and Social Network Account ID has already been taken.']
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
            'social_network_id' => 'Social Network ID',
            'social_network_account_id' => 'Social Network Account ID',
            'time_created' => 'Time Created',
        ];
    }
}
