<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "support_online".
 *
 * @property integer $id
 * @property string $group_name
 * @property string $name
 * @property string $nick_sky
 * @property string $nick_yahoo
 * @property string $email
 * @property string $mobile
 * @property string $phone
 * @property integer $status
 * @property integer $position
 */
class SupportOnline extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'support_online';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'position'], 'integer'],
            [['group_name', 'name', 'nick_sky', 'nick_yahoo', 'email'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => 'Group Name',
            'name' => 'Name',
            'nick_sky' => 'Nick Sky',
            'nick_yahoo' => 'Nick Yahoo',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'phone' => 'Phone',
            'status' => 'Status',
            'position' => 'Position',
        ];
    }
}
