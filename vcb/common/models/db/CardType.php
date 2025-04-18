<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "card_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $currency
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class CardType extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    public static $card_type_ids = array(
        'VMS' => 6,
        'VNP' => 5,
        'VIETTEL' => 4,
    );

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'currency', 'status'], 'required'],
            [['status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 20],
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
            'currency' => 'currency',
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

    public static function getIdByCode($card_type_code)
    {
        return isset(self::$card_type_ids[$card_type_code]) ? self::$card_type_ids[$card_type_code] : false;
    }

    public static function getCodeById($card_type_id)
    {
        foreach (self::$card_type_ids as $code => $id) {
            if ($card_type_id == $id) {
                return $code;
            }
        }
        return false;
    }
}
