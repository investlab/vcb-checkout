<?php

namespace common\models\db;
use common\components\libs\Tables;

/**
 * This is the model class for table "merchant".
 *
 * @property integer $id
 * @property integer $currency_code
 * @property string $currency_name
 * @property float $buy
 * @property float $transfer
 * @property float $sell
 * @property integer $time_update

 */
class CurrencyExchange extends MyActiveRecord
{
    public static function tableName()
    {
        return 'currency_exchange';
    }

    public function rules()
    {
        return [
            [['currency_code', 'currency_name'], 'required'],
            [['buy', 'transfer', 'sell'], 'number'],
            [['time_update'], 'integer'],
            [['currency_code'], 'string', 'max' => 3],
            [['currency_name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'currency_code' => 'Mã ngoại tệ',
            'currency_name' => 'Tên ngoại tệ',
            'buy' => 'Tỉ giá khi mua',
            'transfer' => 'Tỉ giá giao dịch',
            'sell' => 'Tỉ giá khi bán',
            'time_update' => 'Thời gian cập nhật',
        ];
    }
}