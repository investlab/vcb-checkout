<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_card_refer_code".
 *
 * @property integer $id
 * @property integer $card_log_id
 * @property integer $partner_card_log_id
 * @property integer $partner_card_id
 * @property string $partner_card_refer_code
 * @property integer $time_created
 */
class PartnerCardReferCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_card_refer_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_log_id', 'partner_card_log_id', 'partner_card_id', 'partner_card_refer_code'], 'required'],
            [['card_log_id', 'partner_card_log_id', 'partner_card_id', 'time_created'], 'integer'],
            [['partner_card_refer_code'], 'string', 'max' => 255],
            [['partner_card_log_id'], 'unique'],
            [['card_log_id'], 'unique'],
            [['partner_card_id', 'partner_card_refer_code'], 'unique', 'targetAttribute' => ['partner_card_id', 'partner_card_refer_code'], 'message' => 'The combination of Partner Card ID and Partner Card Refer Code has already been taken.']
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
            'partner_card_log_id' => 'Partner Card Log ID',
            'partner_card_id' => 'Partner Card ID',
            'partner_card_refer_code' => 'Partner Card Refer Code',
            'time_created' => 'Time Created',
        ];
    }
}
