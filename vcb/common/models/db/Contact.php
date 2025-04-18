<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "contact".
 *
 * @property integer $id
 * @property string $fullname
 * @property string $email
 * @property string $address
 * @property string $phone
 * @property string $title
 * @property string $content
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_updated
 * @property integer $status
 */
class Contact extends MyActiveRecord
{
    const STATUS_UNREAD = 1;
    const STAUS_READ = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'content'], 'string'],
            [['time_created', 'time_updated', 'status', 'user_updated'], 'integer'],
            [['fullname', 'email', 'title'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 50]
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
            'address' => 'Address',
            'phone' => 'Phone',
            'title' => 'Title',
            'content' => 'Content',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_updated' => 'User Updated',
            'status' => 'Status',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_UNREAD => 'Chưa xem',
            self::STAUS_READ => 'Đã liên hệ'
        );
    }
}
