<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_contact".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property string $fullname
 * @property string $position_name
 * @property string $email
 * @property string $mobile
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerContact extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['fullname', 'position_name', 'email'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_id' => 'Partner ID',
            'fullname' => 'Fullname',
            'position_name' => 'Position Name',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
}
