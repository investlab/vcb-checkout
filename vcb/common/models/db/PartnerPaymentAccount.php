<?php

namespace common\models\db;

use common\components\libs\Tables;
use common\components\utils\Translate;
use Yii;

/**
 * This is the model class for table "partner_payment_account".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $account_id
 * @property integer $partner_payment_id
 * @property string $partner_payment_account
 * @property string $partner_merchant_id
 * @property string $partner_merchant_password
 * @property string $transaction_key
 * @property double $balance
 * @property string $currency
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerPaymentAccount extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_payment_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'account_id', 'partner_payment_id', 'currency', 'status'], 'required'],
            [['merchant_id', 'account_id', 'partner_payment_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['balance'], 'number'],
            [['partner_payment_account'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 10],
            [['merchant_id', 'partner_payment_id', 'partner_payment_account'], 'unique', 'targetAttribute' => ['merchant_id', 'partner_payment_id', 'partner_payment_account'], 'message' => 'The combination of Merchant ID, Partner Payment ID and Partner Payment Account has already been taken.']
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
            'account_id' => 'Account ID',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_account' => 'Partner Payment Account',
            'partner_merchant_id' => 'Partner Merchant ID',
            'partner_merchant_password' => 'Partner Merchant password',
            'balance' => 'Balance',
            'currency' => 'Currency',
            'status' => 'Status',
            'merchant_key' => 'Merchant key',
            'checksum_key' => 'Checksum key',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }
    
    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang sử dụng',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getPartnerPaymentAccount($id) {
        $partner_payment_account_info = Tables::selectOneDataTable("partner_payment_account", ["id = :id", "id" => $id]);
        if ($partner_payment_account_info != false) {
            return $partner_payment_account_info['partner_payment_account'];
        }
        return false;
    }
    
    public static function getPartnerPaymentAccountByMerchantId($merchant_id, $currency, $partner_payment_code) {
        $partner_payment = Tables::selectOneDataTable('partner_payment', ["code = :code AND status = :status", "code" => $partner_payment_code, "status" => self::STATUS_ACTIVE]);
        if (!empty($partner_payment)) {
            $partner_payment_account_info = Tables::selectOneDataTable("partner_payment_account", ["merchant_id = :merchant_id AND currency = :currency AND status = :status AND partner_payment_id = :partner_payment_id", "merchant_id" => $merchant_id, "currency" => $currency, "status" => self::STATUS_ACTIVE, "partner_payment_id" => $partner_payment['id']]);
            if ($partner_payment_account_info != false) {
                return $partner_payment_account_info['partner_payment_account'];
            }
        }
        return false;
    }
    
    public static function getPartnerPaymentAccountsForCashout($cashout_info) {
        $partner_payment_account_info = Tables::selectAllDataTable("partner_payment_account", ["merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND currency = :currency AND status = :status ", 
            "merchant_id" => $cashout_info['merchant_id'], 
            "partner_payment_id" => $cashout_info['partner_payment_id'],
            "currency" => $cashout_info['currency'], 
            "status" => self::STATUS_ACTIVE], "balance DESC");
        if ($partner_payment_account_info != false) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id AND status = :status ", "id" => $cashout_info['payment_method_id'], "status" => PaymentMethod::STATUS_ACTIVE]);
            if ($payment_method_info != false) {
                $result = array();               
                $min_amount = $payment_method_info['min_amount'];
                $cashout_amount = $cashout_info['amount'];
                foreach ($partner_payment_account_info as $row) {
                    $withdraw_amount = self::_getWithdrawAmount($row['balance'], $cashout_amount, $min_amount);
                    if ($withdraw_amount > 0) {
                        $result[] = array(
                            'partner_payment_account_id' => $row['id'],
                            'partner_payment_account' => $row['partner_payment_account'],
                            'amount' => $withdraw_amount,
                        );
                        $cashout_amount-= $withdraw_amount;
                    }
                    if ($cashout_amount == 0) {
                        return $result;
                    }
                }
            }
        }
        return false;
    }
    
    private static function _getWithdrawAmount($balance, $cashout_amount, $min_amount) {
        if ($balance >= $cashout_amount) {
            return $cashout_amount;
        } else {
            if ($balance >= $min_amount) {
                if (($cashout_amount - $balance) >= $min_amount) {
                    return $balance;
                } else {
                    return $cashout_amount - $min_amount;
                }
            }
        }
        return 0;
    }
        
    public static function getOperators() {
        return array(
            'add' => array('title' => Translate::get('Thêm'), 'confirm' => false, 'check-all' => true),
            'active' => array('title' => Translate::get('Mở khóa tài khoản'), 'confirm' => true),
            'lock' => array('title' => Translate::get('Khóa tài khoản'), 'confirm' => true),
            'delete' => array('title' => Translate::get('Xóa tài khoản'), 'confirm' => true)
        );
    }

    public static function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['lock'] = $operators['lock'];
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                $result['delete'] = $operators['delete'];

                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row) {
        $merchant_id = $row['merchant_id'];
        $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);
        $row['merchant_info'] = $merchant;

        $partner_payment_id = $row['partner_payment_id'];
        $partner_payment = Tables::selectOneDataTable('partner_payment', ['id = :id', 'id' => $partner_payment_id]);
        $row['partner_payment_info'] = $partner_payment;

        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows) {
        $merchant_ids = array();
        $partner_payment_ids = array();
        foreach ($rows as $row) {
            $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            $partner_payment_ids[$row['partner_payment_id']] = $row['partner_payment_id'];
        }
        $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        $partner_payments = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ") ", "", "id");

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['partner_payment_info'] = @$partner_payments[$row['partner_payment_id']];
            $rows[$key]['operators'] = PartnerPaymentAccount::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }
    
    public static function getByMerchantIdAndPartnerPaymentId($merchant_id, $partner_payment_id) {
        $partner_payment_account_info = Tables::selectOneDataTable(
                'partner_payment_account', [
                    "merchant_id = :merchant_id AND partner_payment_id = :partner_payment_id AND status = :status", 
                    "merchant_id" => $merchant_id,
                    "partner_payment_id" => $partner_payment_id,
                    "status" => self::STATUS_ACTIVE
                ]);
        if (!empty($partner_payment_account_info)) {
            return $partner_payment_account_info;
        }
        return false;
    }

    public static function getByPartnerPaymentAccount($partner_payment_account) {
        $partner_payment_account_info = PartnerPaymentAccount::find()
                ->where(['partner_payment_account' => $partner_payment_account])
                ->andWhere(['status' => self::STATUS_ACTIVE])
                ->asArray()
                ->one();
        if (!is_null($partner_payment_account_info)) {
            return $partner_payment_account_info;
        }
        return false;
    }

}
