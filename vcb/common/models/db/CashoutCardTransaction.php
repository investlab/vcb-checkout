<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "cashout_card_transaction".
 *
 * @property integer $id
 * @property integer $cashout_id
 * @property integer $card_transaction_id
 * @property integer $time_created
 */
class CashoutCardTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cashout_card_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cashout_id', 'card_transaction_id'], 'required'],
            [['cashout_id', 'card_transaction_id', 'time_created'], 'integer'],
            [['card_transaction_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cashout_id' => 'Cashout ID',
            'card_transaction_id' => 'Card Transaction ID',
            'time_created' => 'Time Created',
        ];
    }
}
