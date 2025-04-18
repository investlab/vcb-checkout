<?php

namespace common\models\db;


/**
 * This is the model class for table "system_check_notify".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $url_check
 * @property integer $status
 * @property integer $time_last_check
 * @property integer $time_created
 * @property integer $time_updated
 * @property string $last_response
 * @property string $channel_send_notify
 * @property string $count_time_alert
 */

class SystemCheckNotify extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_NEW = 3;

    const CHANNEL_ZALO = 'Zalo';
    const CHANNEL_TELEGRAM = 'Telegram';
    const CHANNEL_MAIL = 'Mail';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_check_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url_check', 'last_response', 'channel_send_notify'], 'string'],
            [['count_time_alert'], 'integer'],
            [['id', 'merchant_id', 'status', 'time_last_check', 'time_created', 'time_updated'], 'integer', 'message' => '{attribute} không hợp lệ'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant ID',
            'url_check' => 'Link check',
            'status' => 'Trạng thái',
            'last_time_check' => 'Thời gian kiểm tra gần nhất',
            'time_created' => 'Thời gian tạo',
            'time_updated' => 'Thời gian cập nhật',
            'last_response' => 'Response gần nhất',
            'channel_send_notify' => 'Kênh gửi notify',
            'count_time_alert' => 'Số lần cảnh báo gần đây',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_INACTIVE => 'Không hoạt động',
            self::STATUS_NEW => 'Mới tạo',
        );
    }

    public static function getStatusLog()
    {
        return array(
            self::STATUS_ACTIVE => 'active',
            self::STATUS_INACTIVE => 'inactive',
            self::STATUS_NEW => 'new',
        );
    }

    public static function getChannel()
    {
        return array(
            self::CHANNEL_TELEGRAM => self::CHANNEL_TELEGRAM,
            self::CHANNEL_MAIL => self::CHANNEL_MAIL,
        );
    }

    public static function getOperators()
    {
        return array(
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
        );
    }
}