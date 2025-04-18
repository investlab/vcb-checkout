<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "partner_bank_account".
 *
 * @property integer $id
 * @property integer $bank_id
 * @property string $account_name
 * @property string $account_number
 * @property string $account_branch
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerBankAccount extends MyActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_bank_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['account_name', 'account_branch'], 'string', 'max' => 255],
            [['account_number'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_id' => 'Bank ID',
            'account_name' => 'Account Name',
            'account_number' => 'Account Number',
            'account_branch' => 'Account Branch',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
}
