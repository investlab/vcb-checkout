<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "merchant_card_fee".
 *
 * @property integer $id
 * @property integer $card_type_id
 * @property integer $bill_type
 * @property integer $cycle_day
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property integer $time_begin
 * @property integer $time_end
 * @property double $percent_fee
 * @property string $currency
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_active
 * @property integer $time_lock
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_active
 * @property integer $user_lock
 */
class MerchantCardFee extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_REQUEST = 2;
    const STATUS_REJECT = 3;
    const STATUS_ACTIVE = 4;
    const STATUS_LOCK = 5;

    const BILL_TYPE_VAT = 1;
    const BILL_TYPE_NOT_VAT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant_card_fee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_type_id', 'bill_type', 'cycle_day', 'partner_id', 'time_begin', 'percent_fee', 'currency', 'status'], 'required'],
            [['card_type_id', 'bill_type', 'cycle_day', 'partner_id', 'merchant_id', 'time_begin', 'time_end', 'status', 'time_created', 'time_updated', 'time_active', 'time_lock', 'user_created', 'user_updated', 'user_active', 'user_lock'], 'integer'],
            [['percent_fee'], 'number'],
            [['currency'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_type_id' => 'Card Type ID',
            'bill_type' => 'Bill Type',
            'cycle_day' => 'Cycle Day',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'time_begin' => 'Time Begin',
            'time_end' => 'Time End',
            'percent_fee' => 'Percent Fee',
            'currency' => 'Currency',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_active' => 'Time Active',
            'time_lock' => 'Time Lock',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_active' => 'User Active',
            'user_lock' => 'User Lock',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Mới tạo',
            self::STATUS_REQUEST => 'Đang đợi duyệt',
            self::STATUS_REJECT => 'Từ chối',
            self::STATUS_ACTIVE => 'Đã duyệt',
            self::STATUS_LOCK => 'Đã khóa',
        );
    }

    public static function getBillType()
    {
        return array(
            self::BILL_TYPE_VAT => 'Thẻ loại 1',
            self::BILL_TYPE_NOT_VAT => 'Thẻ loại 2',
        );
    }


    public static function setRow(&$row)
    {
        $merchant_id = $row['merchant_id'];
        $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);

        $card_type_id = $row['card_type_id'];
        $card_type = Tables::selectOneDataTable('card_type', ['id = :id', 'id' => $card_type_id]);

        $partner_id = $row['partner_id'];
        $partner = Tables::selectOneDataTable('partner', ['id = :id', 'id' => $partner_id]);

        $row['merchant_info'] = $merchant;
        $row['card_type_info'] = $card_type;
        $row['partner_info'] = $partner;

        return $row;
    }

    public static function setRows(&$rows)
    {
        $merchant_ids = array();
        $card_type_ids = array();
        $partner_ids = array();
        foreach ($rows as $row) {
            $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            $card_type_ids[$row['card_type_id']] = $row['card_type_id'];
            $partner_ids[$row['partner_id']] = $row['partner_id'];
        }
        $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        $card_types = Tables::selectAllDataTable("card_type", "id IN (" . implode(',', $card_type_ids) . ") ", "", "id");
        $partners = Tables::selectAllDataTable("partner", "id IN (" . implode(',', $partner_ids) . ") ", "", "id");
        $cycle_days = $GLOBALS['CYCLE_DAYS'];
        $bill_types = MerchantCardFee::getBillType();
        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['card_type_info'] = @$card_types[$row['card_type_id']];
            $rows[$key]['partner_info'] = @$partners[$row['partner_id']];
            $rows[$key]['cycle_day_name'] = @$cycle_days[$row['cycle_day']];
            $rows[$key]['bill_type_name'] = @$bill_types[$row['bill_type']];
        }
        return $rows;
    }


    public static function getFee($merchant_info, $card_type_id, $time_card_charge)
    {
        $merchant_card_fe_info = Tables::selectOneDataTable("merchant_card_fee", ["partner_id = " . $merchant_info['partner_id'] . " "
            . "AND (merchant_id = " . $merchant_info['id'] . " OR merchant_id = 0) "
            . "AND card_type_id = " . $card_type_id . " "
            . "AND time_begin <= " . $time_card_charge . " "
            . "AND (time_end > " . $time_card_charge . " OR time_end = 0) "
            . "AND status = " . MerchantCardFee::STATUS_ACTIVE . " "], "merchant_id DESC, time_begin DESC ");
        if ($merchant_card_fe_info != false) {
            return $merchant_card_fe_info;
        }
        return false;
    }

    public static function calculateFee($card_price, $percent_fee)
    {
        return ceil($card_price * $percent_fee / 100);
    }
}
