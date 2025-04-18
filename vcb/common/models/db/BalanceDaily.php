<?php

namespace common\models\db;

use common\components\libs\Weblib;
use common\components\utils\Translate;
use Yii;
use common\components\libs\Tables;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "balance_daily".
 *
 * @property int $id
 * @property int $merchant_id
 * @property int $period
 * @property int $balance_after
 * @property int $balance_before
 * @property int $increased_amount
 * @property int $decreased_amount
 * @property string $time_created
 * @property string $time_updated
 */
class BalanceDaily extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance_daily';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'id', 'merchant_id', 'period', 'increased_amount', 'decreased_amount'], 'integer'],
            [['time_created', 'time_updated'], 'integer'],
            [['balance_after', 'balance_before'], 'double'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant',
            'period' => 'Kỳ',
            'balance_after' => 'Số dư cuối kỳ',
            'balance_before' => 'Số dư cuối kỳ',
            'increased_amount' => 'Phát sinh tăng',
            'decreased_amount' => 'Phát sinh giảm',
            'time_created' => 'Thời gian tạo',
            'time_updated' => 'Thời gian cập nhật',
        ];
    }





}
