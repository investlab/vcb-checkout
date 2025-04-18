<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "partner_payment_fee".
 *
 * @property integer $id
 * @property integer $partner_payment_id
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
class PartnerPaymentFee extends MyActiveRecord
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
        return 'partner_payment_fee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_payment_id', 'method_id', 'currency', 'status'], 'required'],
            [['partner_payment_id', 'method_id', 'payment_method_id', 'partner_id', 'merchant_id', 'status', 'time_begin', 'time_end', 'time_created', 'time_updated', 'time_active', 'time_lock', 'user_created', 'user_updated', 'user_active', 'user_lock'], 'integer'],
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
            'partner_payment_id' => 'Partner Payment ID',
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
            self::STATUS_LOCK => 'Đã khóa',
        );
    }

    public static function getPaymentFee($partner_payment_id, $merchant_id, $payment_method_id, $amount, $currency, $time_request)
    {
        $method_id = PaymentMethod::getMethodIdByPaymentMethodId($payment_method_id);
        if ($method_id != false) {
            $partner_id = Merchant::getPartnerIdByMerchantId($merchant_id);
            if ($partner_id != false) {
                $partner_payment_fee_info = Tables::selectOneDataTable("partner_payment_fee",
                    ["partner_payment_id = :partner_payment_id "
                        . "AND method_id = :method_id "
                        . "AND (payment_method_id = :payment_method_id OR payment_method_id = 0) "
                        . "AND (partner_id = :partner_id OR partner_id = 0) "
                        . "AND (merchant_id = :merchant_id OR merchant_id = 0) "
                        . "AND min_amount <= :amount "
                        . "AND time_begin <= :time_begin "
                        . "AND (time_end > :time_end OR time_end = 0) "
                        . "AND currency = :currency "
                        . "AND status = :status ",
                        "partner_payment_id" => $partner_payment_id,
                        "method_id" => $method_id, "payment_method_id" => $payment_method_id,
                        "partner_id" => $partner_id, "merchant_id" => $merchant_id,                    
                        "amount" => $amount, "time_begin" => $time_request, "time_end" => $time_request,
                        "currency" => $currency,
                        "status" => PartnerPaymentFee::STATUS_ACTIVE], "partner_id DESC, merchant_id DESC, payment_method_id DESC, min_amount DESC, time_begin DESC, id DESC ");
                if ($partner_payment_fee_info != false) {
                    return $partner_payment_fee_info;
                }
            }
        }
        return false;
    }
    
    public static function getSenderFee($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['sender_flat_fee'] + $partner_payment_fee_info['sender_percent_fee'] * $amount / 100);
    }
    
    public static function getSenderFeeForWithdraw($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['sender_flat_fee'] + $partner_payment_fee_info['sender_percent_fee'] * $amount / 100);
        // phi trong
        //return round(($amount + $partner_payment_fee_info['sender_flat_fee']) / (1 - $partner_payment_fee_info['sender_percent_fee']/100)) - $amount;
    }
    
    public static function getSenderFeeForRefund($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['sender_flat_fee'] + $partner_payment_fee_info['sender_percent_fee'] * $amount / 100);        
        // phi trong
        //return round(($amount + $partner_payment_fee_info['sender_flat_fee']) / (1 - $partner_payment_fee_info['sender_percent_fee']/100)) - $amount;
    }

    public static function getReceiverFee($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['receiver_flat_fee'] + $partner_payment_fee_info['receiver_percent_fee'] * $amount / 100);
    }
    
    public static function getReceiverFeeForWithdraw($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['receiver_flat_fee'] + $partner_payment_fee_info['receiver_percent_fee'] * $amount / 100);
    }
    
    public static function getReceiverFeeForRefund($partner_payment_fee_info, $amount)
    {
        // phi ngoai
        return ceil($partner_payment_fee_info['receiver_flat_fee'] + $partner_payment_fee_info['receiver_percent_fee'] * $amount / 100);
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

        $partner_payment_id = $row['partner_payment_id'];
        $partner_payment = Tables::selectOneDataTable('partner_payment', ['id = :id', 'id' => $partner_payment_id]);

        $partner_id = $row['partner_id'];
        $partner = Tables::selectOneDataTable('partner', ['id = :id', 'id' => $partner_id]);

        $row['merchant_info'] = $merchant;
        $row['method_info'] = $method;
        $row['payment_method_info'] = $payment_method;
        $row['partner_payment_info'] = $partner_payment;
        $row['partner_info'] = $partner_id;

        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows)
    {
        $merchant_ids = array();
        $method_ids = array();
        $payment_method_ids = array();
        $partner_ids = array();
        $partner_payment_ids = array();
        foreach ($rows as $row) {
            $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            $method_ids[$row['method_id']] = $row['method_id'];
            $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
            $partner_payment_ids[$row['partner_payment_id']] = $row['partner_payment_id'];
            $partner_ids[$row['partner_id']] = $row['partner_id'];
        }
        $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        $methods = Tables::selectAllDataTable("method", "id IN (" . implode(',', $method_ids) . ") ", "", "id");
        $payment_methods = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") ", "", "id");
        $partner_payments = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ") ", "", "id");
        $partners = Tables::selectAllDataTable("partner", "id IN (" . implode(',', $partner_ids) . ") ", "", "id");

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['method_info'] = @$methods[$row['method_id']];
            $rows[$key]['payment_method_info'] = @$payment_methods[$row['payment_method_id']];
            $rows[$key]['partner_payment_info'] = @$partner_payments[$row['partner_payment_id']];
            $rows[$key]['partner_info'] = @$partners[$row['partner_id']];
            $rows[$key]['operators'] = PartnerPaymentFee::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }
}
