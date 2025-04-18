<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "partner_card_type".
 *
 * @property integer $id
 * @property integer $partner_card_id
 * @property string $partner_card_code
 * @property integer $bill_type
 * @property integer $card_type_id
 * @property integer $cycle_day
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerCardType extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    const BILL_TYPE_VAT = 1;
    const BILL_TYPE_NOT_VAT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_card_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_card_id', 'partner_card_code', 'bill_type', 'card_type_id', 'cycle_day', 'status'], 'required'],
            [['partner_card_id', 'bill_type', 'card_type_id', 'cycle_day', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['partner_card_code'], 'string'],
            [['partner_card_id', 'card_type_id', 'cycle_day'], 'unique', 'targetAttribute' => ['partner_card_id', 'card_type_id', 'cycle_day'], 'message' => 'The combination of Partner Card ID, Card Type ID and Cycle Day has already been taken.']
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
            'partner_card_code' => 'Partner Card Code',
            'bill_type' => 'bill_type',
            'card_type_id' => 'Card Type ID',
            'cycle_day' => 'Cycle Day',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
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
        $card_type_id = $row['card_type_id'];
        $card_type = Tables::selectOneDataTable('card_type', ['id = :id', 'id' => $card_type_id]);

        $partner_id = $row['partner_id'];
        $partner = Tables::selectOneDataTable('partner', ['id = :id', 'id' => $partner_id]);

        $row['card_type_info'] = $card_type;
        $row['partner_info'] = $partner;

        return $row;
    }

    public static function setRows(&$rows)
    {
        $card_type_ids = array();
        $partner_card_ids = array();
        foreach ($rows as $row) {
            $card_type_ids[$row['card_type_id']] = $row['card_type_id'];
            $partner_card_ids[$row['partner_card_id']] = $row['partner_card_id'];
        }

        $card_types = Tables::selectAllDataTable("card_type", "id IN (" . implode(',', $card_type_ids) . ") ", "", "id");
        $partner_cards = Tables::selectAllDataTable("partner_card", "id IN (" . implode(',', $partner_card_ids) . ") ", "", "id");
        $cycle_days = $GLOBALS['CYCLE_DAYS'];
        $bill_types = MerchantCardFee::getBillType();
        foreach ($rows as $key => $row) {
            $rows[$key]['card_type_info'] = @$card_types[$row['card_type_id']];
            $rows[$key]['partner_card_info'] = @$partner_cards[$row['partner_card_id']];
            $rows[$key]['cycle_day_name'] = @$cycle_days[$row['cycle_day']];
            $rows[$key]['bill_type_name'] = @$bill_types[$row['bill_type']];
        }
        return $rows;
    }

    public static function setRowsGetPartnerCard(&$rows)
    {
        $partner_card_ids = array();
        foreach ($rows as $row) {
            $partner_card_ids[$row['partner_card_id']] = $row['partner_card_id'];
        }
        $partner_cards = Tables::selectAllDataTable("partner_card", "id IN (" . implode(',', $partner_card_ids) . ") ", "", "id");
        foreach ($rows as $key => $row) {
            $rows[$key]['partner_card_info'] = @$partner_cards[$row['partner_card_id']];
        }
        return $rows;
    }
}
