<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use yii\db\Query;

/**
 * This is the model class for table "card_transaction".
 *
 * @property integer $id
 * @property string $version
 * @property integer $merchant_id
 * @property string $merchant_refer_code
 * @property integer $bill_type
 * @property integer $cycle_day
 * @property integer $card_log_id
 * @property integer $card_type_id
 * @property string $card_code
 * @property string $card_serial
 * @property double $card_price
 * @property double $card_amount
 * @property string $currency
 * @property integer $partner_card_id
 * @property string $partner_card_refer_code
 * @property integer $partner_card_log_id
 * @property double $percent_fee
 * @property integer $withdraw_time_limit
 * @property integer $status
 * @property integer $cashout_id
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_withdraw
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_withdraw
 */
class CardTransaction extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_WITHDRAW = 3;

    const BILL_TYPE_VAT = 1;
    const BILL_TYPE_NOT_VAT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'merchant_id', 'merchant_refer_code', 'currency', 'bill_type', 'cycle_day', 'card_log_id', 'card_type_id', 'card_code', 'card_price', 'card_amount', 'partner_card_id', 'partner_card_refer_code', 'status'], 'required'],
            [['merchant_id', 'bill_type', 'cycle_day', 'card_log_id', 'card_type_id', 'partner_card_id', 'partner_card_log_id', 'withdraw_time_limit', 'status', 'cashout_id', 'time_created', 'time_updated', 'time_withdraw', 'user_created', 'user_updated', 'user_withdraw'], 'integer'],
            [['card_price', 'card_amount', 'percent_fee'], 'number'],
            [['merchant_refer_code', 'partner_card_refer_code'], 'string', 'max' => 255],
            [['card_code', 'version', 'currency'], 'string', 'max' => 20],
            [['card_serial'], 'string', 'max' => 30],
            [['merchant_id', 'merchant_refer_code'], 'unique', 'targetAttribute' => ['merchant_id', 'merchant_refer_code'], 'message' => 'The combination of Merchant ID and Merchant Refer Code has already been taken.'],
            [['card_log_id'], 'unique'],
            [['card_type_id', 'card_code', 'card_serial'], 'unique', 'targetAttribute' => ['card_type_id', 'card_code', 'card_serial'], 'message' => 'The combination of Card Type ID, Card Code and Card Serial has already been taken.']
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
            'card_log_id' => 'Card Log ID',
            'card_type_id' => 'Card Type ID',
            'card_code' => 'Card Code',
            'card_serial' => 'Card Serial',
            'card_price' => 'Card Price',
            'card_amount' => 'Card Amount',
            'currency' => 'currency',
            'partner_card_id' => 'Partner Card ID',
            'partner_card_refer_code' => 'Partner Card Refer Code',
            'partner_card_log_id' => 'Partner Card Log ID',
            'percent_fee' => 'Percent Fee',
            'withdraw_time_limit' => 'Withdraw Time Limit',
            'status' => 'Status',
            'cashout_id' => 'Cashout ID',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_withdraw' => 'Time Withdraw',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_withdraw' => 'User Withdraw',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Chưa rút',
            self::STATUS_PROCESSING => 'Đang rút',
            self::STATUS_WITHDRAW => 'Đã rút',
        );
    }

    public static function getBillType()
    {
        return array(
            self::BILL_TYPE_VAT => 'Thẻ loại 1',
            self::BILL_TYPE_NOT_VAT => 'Thẻ loại 2',
        );
    }
    
    public static function getDataReport($merchant_id, $time_begin, $time_end) {
        $result = array();
        $card_type_info = Tables::selectAllDataTable("card_type", ["status = :status", "status" => CardType::STATUS_ACTIVE], "id ASC ");
        if ($card_type_info != false) {
            foreach ($card_type_info as $row) {
                $result[$row['id']] = $row;
                $result[$row['id']]['total_card'] = 0;
                $result[$row['id']]['total_price'] = 0;
                $result[$row['id']]['total_amount'] = 0;
            }
            $sql = "SELECT card_type_id, COUNT(id) AS total_card, SUM(card_price) AS total_price, SUM(card_amount) AS total_amount "
                    . "FROM card_transaction "
                    . "WHERE merchant_id = $merchant_id "
                    . "AND time_created >= $time_begin "
                    . "AND time_created < $time_end "
                    . "GROUP BY card_type_id ";
            $command = Yii::$app->get('db_report')->createCommand($sql);       
            $data = $command->queryAll();
            if ($data) {
                foreach ($data as $row) {
                    $result[$row['card_type_id']]['total_card'] = $row['total_card'];
                    $result[$row['card_type_id']]['total_price'] = $row['total_price'];
                    $result[$row['card_type_id']]['total_amount'] = $row['total_amount'];
                }
            }
        }
        return $result;
    }


    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'export' => array('title' => 'Xuất Excel', 'confirm' => false, 'check-all' => true),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row)
    {
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
        $cashout_id = $row['cashout_id'];
        if (intval($cashout_id) > 0) {
            $cashout = Tables::selectOneDataTable('cashout', ['id = :id', 'id' => $cashout_id]);
            $row['cashout_info'] = $cashout;
        }

        $bill_type = self::getBillType();
        $row['bill_type_name'] = $bill_type[$row['bill_type']];

        $cycle_day = $GLOBALS['CYCLE_DAYS'];
        $row['cycle_day_name'] = $cycle_day[$row['cycle_day']];


        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows, $set_row = true)
    {
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
        $status = self::getStatus();
        $cycle_day = $GLOBALS['CYCLE_DAYS'];

        foreach ($rows as $key => $row) {
            $rows[$key]['bill_type_name'] = $bill_type[$row['bill_type']];
            $rows[$key]['status_name'] = $status[$row['status']];
            $rows[$key]['cycle_day_name'] = $cycle_day[$row['cycle_day']];
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['card_type_info'] = @$card_types[$row['card_type_id']];
            $rows[$key]['partner_card_info'] = @$partner_cards[$row['partner_card_id']];

            $rows[$key]['operators'] = CardTransaction::getOperatorsByStatus($row);
        }

        User::setUsernameForRows($rows);
        return $rows;
    }
    
    public static function getTotalCardAmountForCashout($merchant_id, $currency, $time_begin, $time_end, $time_request)
    {
        $now = time();
        $query = new Query();
        $result = $query->select("SUM(card_amount) AS total_card_amount")
            ->from("card_transaction")
            ->where("merchant_id = :merchant_id AND time_created >= :time_begin AND time_created <= :time_end AND withdraw_time_limit <= $now AND currency = :currency AND status = :status ", [
                "merchant_id" => $merchant_id,
                "time_begin" => $time_begin,
                "time_end" => $time_end,
                "currency" => $currency,
                "status" => CardTransaction::STATUS_NEW,
            ])->one();
        if ($result) {
            return $result['total_card_amount'];
        }
        return 0;
    }

    public static function getTotalCardAmountByCashoutId($cashout_id)
    {
        $query = new \yii\db\Query();
        $result = $query->select("SUM(card_amount) AS total_card_amount")
            ->from("card_transaction")
            ->where("cashout_id = :cashout_id ", [
                "cashout_id" => $cashout_id,
            ])->one();
        if ($result) {
            return $result['total_card_amount'];
        }
        return 0;
    }
}
