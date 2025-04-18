<?php

namespace common\models\db;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "account".
 *
 * @property integer $id
 * @property integer $transaction_id
 * @property string $eci
 * @property string $pares_status
 * @property string $veres_enrolled
 * @property string $three_ds_version
 * @property string $three_server_transaction_id
 * @property string $xid
 * @property string $avs
 * @property string $authentication_type
 */
class CyberSourceTransaction extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'cyber_source_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['transaction_id'], 'required'],
            [['eci', 'pares_status', 'veres_enrolled', 'three_ds_version', 'three_server_transaction_id', 'xid', 'avs'], 'string'],
            [['authentication_type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant ID',
            'balance' => 'Balance',
            'balance_freezing' => 'Balance Freezing',
            'balance_pending' => 'Balance Pending',
            'currency' => 'Currency',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_lock' => 'Time Lock',
            'time_active' => 'Time Active',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_lock' => 'User Lock',
            'user_active' => 'User Active',
        ];
    }


}
