<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_card_session".
 *
 * @property integer $id
 * @property integer $partner_card_id
 * @property string $session_id
 * @property integer $session_time_limit
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class PartnerCardSession extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_WAIT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_card_session';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_card_id', 'status'], 'required'],
            [['partner_card_id', 'session_time_limit', 'status', 'time_created', 'time_updated'], 'integer'],
            [['session_id'], 'string', 'max' => 255],
            [['partner_card_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_card_id' => 'Partner Card ID',
            'session_id' => 'Session ID',
            'session_time_limit' => 'Session Time Limit',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Được sử dụng',
            self::STATUS_WAIT => 'Đang lấy session_id mới',
        );
    }
}
