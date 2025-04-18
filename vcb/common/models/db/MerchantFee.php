<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;
use common\models\db\Method;
use common\models\db\Merchant;
use common\models\db\Partner;
use common\models\db\PaymentMethod;

/**
 * This is the model class for table "merchant_fee".
 *
 * @property integer $id
 * @property integer $method_id
 * @property integer $payment_method_id
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property double $min_amount
 * @property double $sender_flat_fee
 * @property double $sender_percent_fee
 * @property double $receiver_flat_fee
 * @property double $receiver_percent_fee
 * @property string $currency
 * @property integer $status
 * @property integer $time_begin
 * @property integer $time_end
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_active
 * @property integer $time_lock
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_active
 * @property integer $user_lock
 */
class MerchantFee extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_REQUEST = 2;
    const STATUS_REJECT = 3;
    const STATUS_ACTIVE = 4;
    const STATUS_LOCK = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant_fee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['method_id', 'partner_id', 'currency', 'status'], 'required'],
            [['method_id', 'payment_method_id', 'partner_id', 'merchant_id', 'status', 'time_begin', 'time_end', 'time_created', 'time_updated', 'time_active', 'time_lock', 'user_created', 'user_updated', 'user_active', 'user_lock'], 'integer'],
            [['min_amount', 'sender_flat_fee', 'sender_percent_fee', 'receiver_flat_fee', 'receiver_percent_fee'], 'number'],
            [['currency'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'method_id' => 'Method ID',
            'payment_method_id' => 'Payment Method ID',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'min_amount' => 'Min Amount',
            'sender_flat_fee' => 'Sender Flat Fee',
            'sender_percent_fee' => 'Sender Percent Fee',
            'receiver_flat_fee' => 'Receiver Flat Fee',
            'receiver_percent_fee' => 'Receiver Percent Fee',
            'currency' => 'Currency',
            'status' => 'Status',
            'time_begin' => 'Time Begin',
            'time_end' => 'Time End',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_active' => 'Time Active',
            'time_lock' => 'Time Lock',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_active' => 'User Active',
            'user_lock' => 'User Lock',
        ];
    }


    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Mới tạo',
            self::STATUS_REQUEST => 'Đang đợi duyệt',
            self::STATUS_REJECT => 'Từ chối',
            self::STATUS_ACTIVE => 'Đã duyệt',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request)
    {
        $partner_id = Merchant::getPartnerIdByMerchantId($merchant_id);
        if ($partner_id != false) {
            $method_id = PaymentMethod::getMethodIdByPaymentMethodId($payment_method_id);
            if ($method_id != false) {
                $merchant_fee_info = Tables::selectOneDataTable("merchant_fee",
                    ["partner_id = :partner_id "
                        . "AND (merchant_id = :merchant_id OR merchant_id = 0) "
                        . "AND method_id = :method_id "
                        . "AND (payment_method_id = :payment_method_id OR payment_method_id = 0) "
                        . "AND min_amount <= :amount "
                        . "AND time_begin <= :time_begin "
                        . "AND (time_end > :time_end OR time_end = 0) "
                        . "AND currency = :currency "
                        . "AND status = :status ",
                        "partner_id" => $partner_id, "merchant_id" => $merchant_id,
                        "method_id" => $method_id, "payment_method_id" => $payment_method_id,
                        "amount" => $amount, "time_begin" => $time_request, "time_end" => $time_request,
                        "currency" => $currency,
                        "status" => MerchantFee::STATUS_ACTIVE], "merchant_id DESC, payment_method_id DESC, min_amount DESC, time_begin DESC, id DESC ");
                if ($merchant_fee_info != false) {
                    return $merchant_fee_info;
                }
            }
        }
        return false;
    }

    public static function getWithdrawFee($merchant_id, $payment_method_id, $amount, $currency, $time_request)
    {
        return self::getPaymentFee($merchant_id, $payment_method_id, $amount, $currency, $time_request);
    }

    public static function getSenderFee($merchant_fee_info, $amount)
    {
       if ($merchant_fee_info['amount_fee_min'] != 0 && $amount <= $merchant_fee_info['amount_fee_min'])
       {
           return ceil($merchant_fee_info['sender_flat_fee'] + (($merchant_fee_info['amount_fee_min'] * $merchant_fee_info['sender_percent_fee']) / 100));
       }
       if ($merchant_fee_info['amount_fee_max'] != 0 && $amount >= $merchant_fee_info['amount_fee_max'])
       {
           return ceil($merchant_fee_info['sender_flat_fee'] + (($merchant_fee_info['amount_fee_max'] * $merchant_fee_info['sender_percent_fee']) / 100));
       }
        return ceil($merchant_fee_info['sender_flat_fee'] + $merchant_fee_info['sender_percent_fee'] * $amount / 100);
    }

    public static function getReceiverFee($merchant_fee_info, $amount)
    {
        return ceil($merchant_fee_info['receiver_flat_fee'] + $merchant_fee_info['receiver_percent_fee'] * $amount / 100);
    }
    
    public static function getSenderFeeForWithdraw($merchant_fee_info, $amount)
    {
        return ceil($merchant_fee_info['sender_flat_fee'] + $merchant_fee_info['sender_percent_fee'] * $amount / 100);        
        // phi trong
        //return round(($amount + $merchant_fee_info['sender_flat_fee']) / (1 - $merchant_fee_info['sender_percent_fee']/100)) - $amount;
    }

    public static function getReceiverFeeForWithdraw($merchant_fee_info, $amount)
    {
        return ceil($merchant_fee_info['receiver_flat_fee'] + $merchant_fee_info['receiver_percent_fee'] * $amount / 100);
    }
    
    public static function getSenderFeeForRefund($merchant_fee_info, $amount)
    {
        return ceil($merchant_fee_info['sender_flat_fee'] + $merchant_fee_info['sender_percent_fee'] * $amount / 100);        
        // phi trong
        //return round(($amount + $merchant_fee_info['sender_flat_fee']) / (1 - $merchant_fee_info['sender_percent_fee']/100)) - $amount;
    }

    public static function getReceiverFeeForRefund($merchant_fee_info, $amount)
    {
        return ceil($merchant_fee_info['receiver_flat_fee'] + $merchant_fee_info['receiver_percent_fee'] * $amount / 100);
    }

    public static function getOperators()
    {
        return array(
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
            'lock' => array('title' => 'Khóa phí', 'confirm' => true),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        if ($row['status'] != self::STATUS_LOCK) {
            $result['lock'] = $operators['lock'];
        }

        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row)
    {
        $merchant_id = $row['merchant_id'];
        $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);

        $method_id = $row['method_id'];
        $method = Tables::selectOneDataTable('method', ['id = :id', 'id' => $method_id]);

        $payment_method_id = $row['payment_method_id'];
        $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);

        $row['merchant_info'] = $merchant;
        $row['method_info'] = $method;
        $row['payment_method_info'] = $payment_method;

        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows)
    {
        $merchant_ids = array();
        $method_ids = array();
        $payment_method_ids = array();
        foreach ($rows as $row) {
            $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            $method_ids[$row['method_id']] = $row['method_id'];
            $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
        }
        $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        $methods = Tables::selectAllDataTable("method", "id IN (" . implode(',', $method_ids) . ") ", "", "id");
        $payment_methods = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") ", "", "id");

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['method_info'] = @$methods[$row['method_id']];
            $rows[$key]['payment_method_info'] = @$payment_methods[$row['payment_method_id']];
            $rows[$key]['operators'] = MerchantFee::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }
}
