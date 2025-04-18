<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "account_log".
 *
 * @property integer $id
 * @property integer $account_id
 * @property double $balance
 * @property double $balance_freezing
 * @property double $balance_pending
 * @property integer $time_created
 */
class AccountLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id'], 'required'],
            [['account_id', 'time_created'], 'integer'],
            [['balance', 'balance_freezing', 'balance_pending'], 'number'],           
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Account ID',
            'balance' => 'Balance',
            'balance_freezing' => 'Balance Freezing',
            'balance_pending' => 'Balance Pending',
            'time_created' => 'Time Created',
        ];
    }
}
