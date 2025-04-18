<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "merchant".
 *
 * @property integer $id
 * @property string $branch_code
 * @property integer $account_number
 * @property integer $merchant_id
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class CreditAccount extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'credit_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['branch_code', 'account_number', 'merchant_id', 'status'], 'required'],
            [['account_number', 'merchant_id', 'status', 'time_created', 'time_updated',
                    'user_created', 'user_updated'], 'integer'],
            [['branch_code'], 'string', 'max' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_code' => 'Branch Code',
            'account_number' => 'Account Number',
            'merchant_id' => 'Merchant ID',
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
            self::STATUS_ACTIVE => 'Kích hoạt',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

}