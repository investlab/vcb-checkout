<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "otp_user".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $refer_type
 * @property integer $refer_id
 * @property string $code
 * @property string $email
 * @property string $mobile
 * @property integer $time_limit
 * @property integer $number
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class OtpUser extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    const TYPE_EMAIL = 1;
    const TYPE_MOBILE = 2;

    const REFER_TYPE_USER_LOGIN_TEMP = 1;
    const REFER_TYPE_USER_LOGIN = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'otp_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'refer_type', 'refer_id', 'code', 'time_limit', 'number', 'status'], 'required'],
            [['type', 'refer_type', 'refer_id', 'time_limit', 'number', 'status', 'time_created', 'time_updated'], 'integer'],
            [['code', 'email', 'mobile'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'refer_type' => 'Refer Type',
            'refer_id' => 'Refer ID',
            'code' => 'Code',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'time_limit' => 'Time Limit',
            'number' => 'Number',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getCode()
    {
        return strtoupper(substr(md5(time() . rand(0, 99)), 0, 6));
    }

    public static function encryptCode($code)
    {
        return md5($code);
    }
}
