<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_group_right".
 *
 * @property integer $id
 * @property integer $user_group_id
 * @property integer $right_id
 * @property string $right_code
 */
class UserGroupRight extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_group_right';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_group_id', 'right_id'], 'integer'],
            [['right_code'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_group_id' => 'User Group ID',
            'right_id' => 'Right ID',
            'right_code' => 'Right Code',
        ];
    }

    public function getright()
    {
        return $this->hasOne(Right::className(), ['id' => 'right_id']);
    }
}
