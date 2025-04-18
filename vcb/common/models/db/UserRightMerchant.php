<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_right".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $user_admin_account_id
 * @property integer $right_id
 * @property string $right_code
 */
class UserRightMerchant extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_right_merchant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_admin_account_id', 'right_id'], 'integer'],
            [['right_code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_admin_account_id' => 'user_admin_account_id',
            'right_id' => 'Right ID',
            'right_code' => 'Right Code',
        ];
    }
}
