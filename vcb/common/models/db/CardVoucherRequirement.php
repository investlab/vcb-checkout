<?php

namespace common\models\db;

use common\components\utils\Translate;

/**
 * @property mixed|string|null $require_id
 * @property int|mixed|string|null $user_created
 * @property int|mixed|null $status
 * @property int|mixed|null $checkpoint
 * @property array|mixed|string|string[]|null $amount
 * @property array|mixed|string|string[]|null $card_voucher_status
 * @property int|mixed|null $type
 * @property mixed|null $card_voucher_id
 * @property mixed|null $balance
 * @property int $time_created
 * @property int|mixed|null $order_code
 * @property int $time_updated
 */
class CardVoucherRequirement extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_REJECT = 3;
    const TYPE_TOP_UP = 1;
    const TYPE_WITH_DRAW = 2;
    const TYPE_ACTIVE = 3;
    const TYPE_LOCK = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_voucher_requirement';
    }

    public function rules()
    {
        return [
            [['require_id', 'card_voucher_id', 'type', 'status'], 'required'],
            [['card_voucher_id', 'type', 'amount', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
//            [['require_id'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'require_id' => 'Mã yêu cầu',
            'card_voucher_id' => 'Đối tượng yêu cầu',
            'type' => 'Loại',
            'amount' => 'Số tiền',
            'status' => 'Trạng thái',
            'serial' => 'Serial máy',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->time_created = time();
        }
        $this->time_updated = time();
        return parent::beforeSave($insert);
    }

    public function getCardVoucher()
    {
        return $this->hasOne(CardVoucher::className(), ['id' => 'card_voucher_id']);
    }

    public function getMerchant()
    {
        return $this->hasOne(Merchant::className(), ['id' => 'merchant_id'])->via("cardVoucher");
    }

    public static function getListTypeName()
    {
        return array(
            self::TYPE_ACTIVE => Translate::get('Kích hoạt thẻ'),
            self::TYPE_LOCK => Translate::get('Khoá thẻ'),
            self::TYPE_TOP_UP => Translate::get('Nạp tiền'),
            self::TYPE_WITH_DRAW => Translate::get('Rút tiền'),
        );
    }

    public static function getListStatus()
    {
        return array(
            self::STATUS_NEW => Translate::get('Mới tạo'),
            self::STATUS_REJECT => Translate::get('Từ chối'),
            self::STATUS_ACCEPT => Translate::get('Đồng ý'),
        );
    }

    public static function getStatus($status)
    {
        $list_status = self::getListStatus();
        if (array_key_exists($status, $list_status)) {
            return $list_status[$status];
        } else {
            return Translate::get("Không rõ");
        }
    }


    public static function getTypeName($type)
    {
        $list = self::getListTypeName();
        if (array_key_exists($type, $list)) {
            return $list[$type];
        } else {
            return Translate::get("Không rõ");
        }
    }

    public static function getColorType($type)
    {
        $ls = array(
            self::TYPE_ACTIVE => "text-success",
            self::TYPE_LOCK => "text-danger",
            self::TYPE_TOP_UP => "text-warning",
            self::TYPE_WITH_DRAW => "text-danger",
        );
        return $ls[$type];
    }

    public static function getOperators()
    {
        return array(
            'detail-requirement' => array('title' => 'Chi tiết', 'confirm' => false),
        );
    }


    public function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
//        switch ($row['status']) {
//            case self::STATUS_NEW:
//                $result['detail-requirement'] = $operators['detail-requirement'];
//
//                break;
//            case self::STATUS_REJECT:
//                $result['detail-requirement'] = $operators['detail-requirement'];
//
//                break;
//            case self::STATUS_ACCEPT:
//                $result['detail-requirement'] = $operators['detail-requirement'];
//                break;
//        }
        $result['detail-requirement'] = $operators['detail-requirement'];
        return self::getOperatorsForUser($row, $result);
    }

    public function getActionByStatus()
    {
        return self::getOperatorsByStatus($this);
    }


}