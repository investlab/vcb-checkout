<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "card_log_full".
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
class CardLogFull extends MyActiveRecord {

    const CARD_STATUS_FAIL = 1;
    const CARD_STATUS_TIMEOUT = 2;
    const CARD_STATUS_NO_SUCCESS = 3;
    const CARD_STATUS_SUCCESS = 4;

    public static function getCardStatus() {
        return array(
            self::CARD_STATUS_FAIL => 'Thẻ sai',
            self::CARD_STATUS_TIMEOUT => 'Thẻ timeout',
            self::CARD_STATUS_NO_SUCCESS => 'Thẻ chưa bị gạch',
            self::CARD_STATUS_SUCCESS => 'Gạch thẻ thành công'
        );
    }

    const TRANSACTION_STATUS_NEW = 1;
    const TRANSACTION_STATUS_CREATING = 2;
    const TRANSACTION_STATUS_CREATED = 3;
    const TRANSACTION_STATUS_ERROR = 4;

    public static function getTransactionStatus() {
        return array(
            self::TRANSACTION_STATUS_NEW => 'Chưa tạo giao dịch',
            self::TRANSACTION_STATUS_CREATING => 'Đang tạo giao dịch',
            self::TRANSACTION_STATUS_CREATED => 'Đã tạo giao dịch',
            self::TRANSACTION_STATUS_ERROR => 'Lỗi khi tạo giao dịch',
        );
    }

    const BACKUP_STATUS_NEW = 1;
    const BACKUP_STATUS_CREATING = 2;
    const BACKUP_STATUS_CREATED = 3;

    public static function getBackupStatus() {
        return array(
            self::BACKUP_STATUS_NEW => 'Chưa backup',
            self::BACKUP_STATUS_CREATING => 'Đang backup',
            self::BACKUP_STATUS_CREATED => 'Đã backup'
        );
    }

    const BILL_TYPE_VAT = 1;
    const BILL_TYPE_NOT_VAT = 2;

    public static function getBillType() {
        return array(
            self::BILL_TYPE_VAT => 'Thẻ loại 1',
            self::BILL_TYPE_NOT_VAT => 'Thẻ loại 2',
        );
    }

    public static function getDb() {
        return Yii::$app->get('db_report');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'card_log_full';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['version', 'merchant_id', 'merchant_refer_code', 'bill_type', 'cycle_day', 'card_type_id', 'card_code', 'currency', 'card_status', 'transaction_status', 'backup_status'], 'required'],
            [['merchant_id', 'bill_type', 'cycle_day', 'card_type_id', 'partner_card_id', 'partner_card_log_id', 'withdraw_time_limit', 'card_status', 'transaction_status', 'card_transaction_id', 'backup_status', 'time_card_updated', 'time_created', 'time_updated', 'time_create_transaction', 'time_backup', 'user_created', 'user_updated'], 'integer'],
            [['card_price', 'card_amount', 'percent_fee'], 'number'],
            [['merchant_input', 'merchant_output'], 'string'],
            [['version', 'card_code', 'currency', 'result_code'], 'string', 'max' => 20],
            [['merchant_refer_code', 'partner_card_refer_code'], 'string', 'max' => 255],
            [['card_serial'], 'string', 'max' => 30],
            [['merchant_id', 'merchant_refer_code'], 'unique', 'targetAttribute' => ['merchant_id', 'merchant_refer_code'], 'message' => 'The combination of Merchant ID and Merchant Refer Code has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'merchant_id' => 'Merchant ID',
            'merchant_refer_code' => 'Merchant Refer Code',
            'bill_type' => 'Bill Type',
            'cycle_day' => 'Cycle Day',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_amount' => 'Card Amount',
            'currency' => 'Currency',
            'partner_card_id' => 'Partner Card ID',
            'partner_card_log_id' => 'Partner Card Log ID',
            'partner_card_refer_code' => 'Partner Card Refer Code',
            'percent_fee' => 'Percent Fee',
            'withdraw_time_limit' => 'Withdraw Time Limit',
            'merchant_input' => 'Merchant Input',
            'merchant_output' => 'Merchant Output',
            'result_code' => 'Result Code',
            'card_status' => 'Card Status',
            'transaction_status' => 'Transaction Status',
            'card_transaction_id' => 'Card Transaction ID',
            'backup_status' => 'Backup Status',
            'time_card_updated' => 'Time Card Updated',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_create_transaction' => 'Time Create Transaction',
            'time_backup' => 'Time Backup',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getOperators() {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'update-success' => array('title' => 'Cập nhật thẻ thành công', 'confirm' => false),
            'export' => array('title' => 'Xuất Excel', 'confirm' => false, 'check-all' => true),
        );
    }

    public static function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        switch ($row['card_status']) {
            case self::CARD_STATUS_TIMEOUT:
                $result['update-success'] = $operators['update-success'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row) {
        $merchant_id = $row['merchant_id'];
        if (intval($merchant_id) > 0) {
            $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);
            $row['merchant_info'] = $merchant;
        }
        $card_type_id = $row['card_type_id'];
        if (intval($card_type_id) > 0) {
            $card_type = Tables::selectOneDataTable('card_type', ['id = :id', 'id' => $card_type_id]);
            $row['card_type_info'] = $card_type;
        }
        $partner_card_id = $row['partner_card_id'];
        if (intval($partner_card_id) > 0) {
            $partner_card = Tables::selectOneDataTable('partner_card', ['id = :id', 'id' => $partner_card_id]);
            $row['partner_card_info'] = $partner_card;
        }
        $bill_type = self::getBillType();
        $row['bill_type_name'] = $bill_type[$row['bill_type']];

        $cycle_day = $GLOBALS['CYCLE_DAYS'];
        $row['cycle_day_name'] = $cycle_day[$row['cycle_day']];


        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows, $set_row = true) {
        $merchant_ids = array();
        $card_type_ids = array();
        $partner_card_ids = array();

        $merchants = array();
        $card_types = array();
        $partner_cards = array();

        foreach ($rows as $row) {
            if (intval($row['merchant_id']) > 0) {
                $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            }
            if (intval($row['card_type_id']) > 0) {
                $card_type_ids[$row['card_type_id']] = $row['card_type_id'];
            }
            if (intval($row['partner_card_id']) > 0) {
                $partner_card_ids[$row['partner_card_id']] = $row['partner_card_id'];
            }
        }

        if (!empty($merchant_ids)) {
            $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        }
        if (!empty($card_type_ids)) {
            $card_types = Tables::selectAllDataTable("card_type", "id IN (" . implode(',', $card_type_ids) . ") ", "", "id");
        }
        if (!empty($partner_card_ids)) {
            $partner_cards = Tables::selectAllDataTable("partner_card", "id IN (" . implode(',', $partner_card_ids) . ") ", "", "id");
        }

        $bill_type = self::getBillType();
        $card_status = self::getCardStatus();
        $transaction_status = self::getTransactionStatus();
        $backup_status = self::getBackupStatus();
        $cycle_day = $GLOBALS['CYCLE_DAYS'];

        foreach ($rows as $key => $row) {
            $rows[$key]['card_code'] = self::_getCardCode($row);
            $rows[$key]['bill_type_name'] = $bill_type[$row['bill_type']];
            $rows[$key]['card_status_name'] = $card_status[$row['card_status']];
            $rows[$key]['transaction_status_name'] = $transaction_status[$row['transaction_status']];
            $rows[$key]['backup_status_name'] = $backup_status[$row['backup_status']];
            $rows[$key]['cycle_day_name'] = $cycle_day[$row['cycle_day']];
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['card_type_info'] = @$card_types[$row['card_type_id']];
            $rows[$key]['partner_card_info'] = @$partner_cards[$row['partner_card_id']];

            $rows[$key]['operators'] = CardLogFull::getOperatorsByStatus($row);
        }

        User::setUsernameForRows($rows);
        return $rows;
    }

    private static function _getCardCode($row) {
        if ($row['card_status'] == self::CARD_STATUS_TIMEOUT) {
            return substr($row['card_code'], 0, strlen($row['card_code']) - 8) . '****' . substr($row['card_code'], -4);
        }
        return $row['card_code'];
    }

}
