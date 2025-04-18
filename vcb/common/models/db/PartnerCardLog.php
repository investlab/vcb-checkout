<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "partner_card_log".
 *
 * @property integer $id
 * @property integer $partner_card_id
 * @property integer $type
 * @property string $function
 * @property string $input
 * @property string $output
 * @property string $session_id
 * @property string $result
 * @property string $refer_code
 * @property integer $card_log_id
 * @property integer $card_type_id
 * @property string $card_code
 * @property string $card_serial
 * @property double $card_price
 * @property integer $card_status
 * @property integer $status
 * @property integer $backup_status
 * @property integer $time_backup
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_card_updated
 */
class PartnerCardLog extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_PROCESSED = 3;

    const CARD_STATUS_ERROR = 1;
    const CARD_STATUS_TIMEOUT = 2;
    const CARD_STATUS_NOT_PROCESS = 3;
    const CARD_STATUS_SUCCESS = 4;

    const TYPE_CARD_CHARGE = 1;
    const TYPE_GET_SESSION = 2;

    const BACKUP_STATUS_NEW = 1;
    const BACKUP_STATUS_PROCESSING = 2;
    const BACKUP_STATUS_PROCESSED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_card_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_card_id', 'type', 'function', 'input', 'status'], 'required'],
            [['partner_card_id', 'type', 'card_log_id', 'card_type_id', 'card_status', 'status', 'backup_status', 'time_backup', 'time_created', 'time_updated', 'time_card_updated'], 'integer'],
            [['input', 'output'], 'string'],
            [['card_price'], 'number'],
            [['function', 'session_id', 'result', 'refer_code'], 'string', 'max' => 255],
            [['card_code'], 'string', 'max' => 20],
            [['card_serial'], 'string', 'max' => 30]
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
            'type' => 'Type',
            'function' => 'Function',
            'input' => 'Input',
            'output' => 'Output',
            'session_id' => 'Session ID',
            'result' => 'Result',
            'refer_code' => 'Refer Code',
            'card_log_id' => 'Card Log ID',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_status' => 'Card Status',
            'status' => 'Status',
            'backup_status' => 'backup_status',
            'time_backup' => 'time_backup',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_card_updated' => 'Time Card Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Mới tạo',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_PROCESSED => 'Đã xử lý',
        );
    }

    public static function getCardStatus()
    {
        return array(
            self::CARD_STATUS_ERROR => 'Thẻ lỗi',
            self::CARD_STATUS_TIMEOUT => 'Timeout',
            self::CARD_STATUS_NOT_PROCESS => 'Thẻ chưa bị gạch',
            self::CARD_STATUS_SUCCESS => 'Gạch thẻ thành công',
        );
    }

    public static function getTimelimitBackup()
    {
        $today = getdate();
        $time_limit = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        return $time_limit;
    }

    public static function checkBackup($timeout)
    {
        $time_limit = self::getTimelimitBackup();
        $partner_card_log_info = Tables::selectOneDataTable("partner_card_log", "time_created < $time_limit AND (backup_status = " . PartnerCardLog::BACKUP_STATUS_NEW . " OR (backup_status = " . PartnerCardLog::BACKUP_STATUS_PROCESSING . " AND time_backup < $timeout )) ");
        if ($partner_card_log_info != false) {
            return true;
        }
        return false;
    }
}
