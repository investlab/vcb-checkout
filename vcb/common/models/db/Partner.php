<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner".
 *
 * @property integer $id
 * @property integer $partner_type_id
 * @property string $name
 * @property integer $parent_id
 * @property string $code
 * @property string $address
 * @property integer $zone_id
 * @property string $note
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class Partner extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_type_id', 'parent_id', 'zone_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['address', 'note'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_type_id' => 'Partner Type ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
            'code' => 'Code',
            'address' => 'Address',
            'zone_id' => 'Zone ID',
            'note' => 'Note',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
}
