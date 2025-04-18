<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "partner_card".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $bill_type
 * @property string $config
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerCard extends MyActiveRecord
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
        return 'partner_card';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'bill_type', 'status'], 'required'],
            [['bill_type', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['config'], 'string'],
            [['name', 'code'], 'string', 'max' => 255],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'bill_type' => 'Bill Type',
            'config' => 'Config',
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

    public static function getPartnerCardCodeActive($card_type_id, $bill_type, $cycle_day, &$partner_card_id = null)
    {
        $partner_card_type_info = Tables::selectOneDataTable("partner_card_type", ["card_type_id = :card_type_id AND bill_type = :bill_type AND cycle_day = :cycle_day AND status = :status ", "card_type_id" => $card_type_id, "bill_type" => $bill_type, "cycle_day" => $cycle_day, "status" => PartnerCardType::STATUS_ACTIVE]);
        if ($partner_card_type_info != false) {
            $partner_card_id = $partner_card_type_info['partner_card_id'];
            return $partner_card_type_info['partner_card_code'];
        }
        return false;
    }
}
