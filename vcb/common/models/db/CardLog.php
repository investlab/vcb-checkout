<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

/**
 * This is the model class for table "card_log".
 *
 * @property integer $id
 * @property string $version
 * @property integer $merchant_id
 * @property string $merchant_refer_code
 * @property integer $bill_type
 * @property integer $cycle_day
 * @property integer $card_type_id
 * @property string $card_code
 * @property string $card_serial
 * @property double $card_price
 * @property double $card_amount
 * @property string $currency
 * @property integer $partner_card_id
 * @property integer $partner_card_log_id
 * @property string $partner_card_refer_code
 * @property double $percent_fee
 * @property integer $withdraw_time_limit
 * @property string $merchant_input
 * @property string $merchant_output
 * @property string $result_code
 * @property integer $card_status
 * @property integer $transaction_status
 * @property integer $card_transaction_id
 * @property integer $backup_status
 * @property integer $time_card_updated
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_create_transaction
 * @property integer $time_backup
 * @property integer $user_created
 * @property integer $user_updated
 */
class CardLog extends MyActiveRecord
{

    const TRANSACTION_STATUS_NEW = 1;
    const TRANSACTION_STATUS_PROCESSING = 2;
    const TRANSACTION_STATUS_PROCESSED = 3;
    const TRANSACTION_STATUS_ERROR = 4;
    
    const BACKUP_STATUS_NEW = 1;
    const BACKUP_STATUS_PROCESSING = 2;
    const BACKUP_STATUS_PROCESSED = 3;
    
    const CARD_STATUS_ERROR = 1;
    const CARD_STATUS_TIMEOUT = 2;
    const CARD_STATUS_NOT_PROCESS = 3;
    const CARD_STATUS_SUCCESS = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'merchant_id', 'merchant_refer_code', 'currency', 'bill_type', 'cycle_day', 'card_type_id', 'card_code', 'card_status', 'transaction_status', 'backup_status'], 'required'],
            [['merchant_id', 'bill_type', 'cycle_day', 'card_type_id', 'partner_card_id', 'partner_card_log_id', 'withdraw_time_limit', 'card_status', 'transaction_status', 'card_transaction_id', 'backup_status', 'time_card_updated', 'time_created', 'time_updated', 'time_create_transaction', 'time_backup', 'user_created', 'user_updated'], 'integer'],
            [['card_price', 'percent_fee', 'card_amount'], 'number'],
            [['merchant_input', 'merchant_output'], 'string'],
            [['merchant_refer_code', 'partner_card_refer_code'], 'string', 'max' => 255],
            [['version', 'currency', 'card_code', 'result_code'], 'string', 'max' => 20],
            [['card_serial'], 'string', 'max' => 30],
            [['merchant_id', 'merchant_refer_code'], 'unique', 'targetAttribute' => ['merchant_id', 'merchant_refer_code'], 'message' => 'The combination of Merchant ID and Merchant Refer Code has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'version',
            'merchant_id' => 'Merchant ID',
            'merchant_refer_code' => 'Merchant Refer Code',
            'bill_type' => 'Bill Type',
            'cycle_day' => 'Cycle Day',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_amount' => 'card_amount',
            'currency' => 'currency',
            'partner_card_id' => 'Partner Card ID',
            'partner_card_log_id' => 'Partner Card Log ID',
            'partner_card_refer_code' => 'Partner Card Refer Code',
            'percent_fee' => 'Percent Fee',
            'withdraw_time_limit' => 'Withdraw Time Limit',
            'merchant_input' => 'Merchant Input',
            'merchant_output' => 'Merchant Output',
            'result_code' => 'result_code',
            'card_status' => 'Card Status',
            'transaction_status' => 'Transaction Status',
            'card_transaction_id' => 'Card Transaction ID',
            'backup_status' => 'Backup Status',
            'time_card_updated' => 'Time Card Updated',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_create_transaction' => 'time_create_transaction',
            'time_backup' => 'time_backup',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getTransactionStatus()
    {
        return array(
            self::TRANSACTION_STATUS_NEW => 'Chưa tạo giao dịch',
            self::TRANSACTION_STATUS_PROCESSING => 'Đang tạo giao dịch',
            self::TRANSACTION_STATUS_PROCESSED => 'Đã tạo giao dịch',
            self::TRANSACTION_STATUS_ERROR => 'Lỗi khi tạo giao dịch',
        );
    }

    public static function getBackupStatus()
    {
        return array(
            self::BACKUP_STATUS_NEW => 'Chưa backup',
            self::BACKUP_STATUS_PROCESSING => 'Đang backup',
            self::BACKUP_STATUS_PROCESSED => 'Đã backup',
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

    public static function getWithdrawTimeLimit($bill_type, $cycle_day, $time_card_charge)
    {
        $time_limit = $time_card_charge;
        if ($cycle_day > 0) {
            if ($cycle_day == 60) {
                $time = getdate($time_card_charge);
                $day_limit = 1;
                $mon_limit = $time['mon'] + 2;
                $year_limit = $time['year'];
                if ($mon_limit > 12) {
                    $mon_limit = $mon_limit - 12;
                    $year_limit = $year_limit + 1;
                }
                $time_limit = mktime(0, 0, 1, $mon_limit, $day_limit, $year_limit);
            } else {
                $time = getdate($time_card_charge);
                $day_limit = ceil($time['mday'] / $cycle_day) * $cycle_day + 1;
                $mon_limit = $time['mon'];
                $year_limit = $time['year'];
                if ($day_limit > 30) {
                    $day_limit = 1;
                    $mon_limit++;
                    if ($mon_limit > 12) {
                        $mon_limit = 1;
                        $year_limit++;
                    }
                }
                $time_limit = mktime(0, 0, 1, $mon_limit, $day_limit, $year_limit);
            }
        }
        return $time_limit;
    }

    public static function getInfoByMerchantReferCode($merchant_id, $merchant_refer_code)
    {
        $card_merchant_refer_code_info = Tables::selectOneDataTable("card_merchant_refer_code", ["merchant_id = :merchant_id AND merchant_refer_code = :merchant_refer_code ", "merchant_id" => $merchant_id, "merchant_refer_code" => $merchant_refer_code]);
        if ($card_merchant_refer_code_info != false) {
            $card_log_info = Tables::selectOneDataTable("card_log", ["id = :id", "id" => $card_merchant_refer_code_info['card_log_id']]);
            if ($card_log_info != false) {
                return $card_log_info;
            }
        }
        return false;
    }

    public static function checkCreateCardTransaction($timeout)
    {
        $card_log_info = Tables::selectOneDataTable("card_log", "card_status = " . CardLog::CARD_STATUS_SUCCESS . " AND (transaction_status = " . CardLog::TRANSACTION_STATUS_NEW . " OR (transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSING . " AND time_create_transaction < $timeout ))");
        if ($card_log_info != false) {
            return true;
        }
        return false;
    }

    public static function getTimelimitBackup()
    {
        $today = getdate();
        $time_limit = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        return $time_limit;
    }

    public static function checkBackup($timeout)
    {
        $time_limit = CardLog::getTimelimitBackup();
        $card_log_info = Tables::selectOneDataTable("card_log", "time_created < $time_limit AND ((card_status = " . CardLog::CARD_STATUS_SUCCESS . " AND transaction_status = " . CardLog::TRANSACTION_STATUS_PROCESSED . ") OR card_status != " . CardLog::CARD_STATUS_SUCCESS . ") AND (backup_status = " . CardLog::BACKUP_STATUS_NEW . " OR (backup_status = " . CardLog::BACKUP_STATUS_PROCESSING . " AND time_backup < $timeout )) ");
        if ($card_log_info != false) {
            return true;
        }
        return false;
    }


}
