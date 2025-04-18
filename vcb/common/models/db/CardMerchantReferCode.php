<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "card_merchant_refer_code".
 *
 * @property integer $id
 * @property integer $card_log_id
 * @property integer $merchant_id
 * @property string $merchant_refer_code
 * @property integer $time_created
 */
class CardMerchantReferCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_merchant_refer_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_log_id', 'merchant_id', 'merchant_refer_code'], 'required'],
            [['card_log_id', 'merchant_id', 'time_created'], 'integer'],
            [['merchant_refer_code'], 'string', 'max' => 255],
            [['card_log_id'], 'unique'],
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
            'card_log_id' => 'Card Log ID',
            'merchant_id' => 'Merchant ID',
            'merchant_refer_code' => 'Merchant Refer Code',
            'time_created' => 'Time Created',
        ];
    }
}
