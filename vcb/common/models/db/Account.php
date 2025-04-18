<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "account".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property double $balance
 * @property double $balance_freezing
 * @property double $balance_pending
 * @property string $currency
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_lock
 * @property integer $time_active
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_lock
 * @property integer $user_active
 * @property integer $balance_card_voucher
 */
class Account extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    
    const MASTER_ACCOUNT_ID = 1;
    const FEE_ACCOUNT_ID = 2;
    const FEE_CARD_VOUCHER_ACCOUNT_ID = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'currency', 'status'], 'required'],
            [['merchant_id', 'status', 'time_created', 'time_updated', 'time_lock', 'time_active', 'user_created', 'user_updated', 'user_lock', 'user_active'], 'integer'],
            [['balance', 'balance_freezing', 'balance_pending'], 'number'],
            [['currency'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
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
    
    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }
    
    public static function getAccountIdByMerchantId($merchant_id, $currency) {
        $account_info = Tables::selectOneDataTable("account", ["merchant_id = :merchant_id AND currency = :currency", "merchant_id" => $merchant_id, "currency" => $currency]);
        if ($account_info != false) {
            return $account_info['id'];
        }
        return false;
    }
    
    public static function getMasterAccountId($currency) {
        return self::MASTER_ACCOUNT_ID;
    }
    
    public static function getFeeAccountId($currency) {
        return self::FEE_ACCOUNT_ID;
    }
    
    public static function getBalance($merchant_id, $currency, &$account_info = false) {
        $account_info = Tables::selectOneDataTable("account", ["merchant_id = :merchant_id AND currency = :currency", "merchant_id" => $merchant_id, "currency" => $currency]);
        if ($account_info != false) {
            return $account_info['balance'];
        }
        return 0;
    }
    
    public static function getBalanceByAccountId($account_id, &$account_info = false) {
        $account_info = Tables::selectOneDataTable("account", ["id = :id ", "id" => $account_id]);
        if ($account_info != false) {
            return $account_info['balance'];
        }
        return 0;
    }
    
    public static function checkSystemTotalBalance(&$total_balance = 0) {
        $total_balance = self::getSystemTotalBalance();
        if ($total_balance == 100000000000000) {
            return true;
        }
        return false;
    }
    
    public static function getSystemTotalBalance() {
        $sql = "SELECT SUM(balance + balance_pending + balance_freezing) AS total_balance FROM account";
        $command = Account::getDb()->createCommand($sql);
        $result = $command->queryOne();
        if ($result) {
            return floatval($result['total_balance']);
        }
        return false;
    }

    public static function getFeeCardVoucherAccountId($currency) {
        return self::FEE_CARD_VOUCHER_ACCOUNT_ID;
    }


}
