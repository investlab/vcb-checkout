<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "cashout".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property integer $time_begin
 * @property integer $time_end
 * @property double $amount
 * @property double $receiver_fee
 * @property string $currency
 * @property integer $method_id
 * @property integer $bank_id
 * @property integer $payment_method_id
 * @property integer $partner_payment_id
 * @property string $bank_account_code
 * @property string $bank_account_name
 * @property string $bank_account_branch
 * @property string $bank_card_month
 * @property string $bank_card_year
 * @property string $partner_payment_data
 * @property integer $transaction_id
 * @property integer $reason_id
 * @property string $reason
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_request
 * @property integer $time_accept
 * @property integer $time_reject
 * @property integer $time_paid
 * @property integer $time_cancel
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_request
 * @property integer $user_accept
 * @property integer $user_reject
 * @property integer $user_paid
 * @property integer $user_cancel
 */
class Cashout extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_WAIT_VERIFY = 2;
    const STATUS_VERIFY = 3;
    const STATUS_WAIT_ACCEPT = 4;
    const STATUS_REJECT = 5;
    const STATUS_ACCEPT = 6;
    const STATUS_PAID = 7;
    const STATUS_CANCEL = 8;

    const TYPE_CHECKOUT_ORDER = 1;
    const TYPE_CARD_TRANSACTION = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cashout';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'partner_id', 'merchant_id', 'bank_account_code', 'time_begin', 'time_end', 'amount', 'currency', 'status'], 'required'],
            [['type', 'partner_id', 'merchant_id', 'time_begin', 'time_end', 'method_id', 'bank_id', 'payment_method_id', 'partner_payment_id', 'transaction_id', 'reason_id', 'status', 'time_created', 'time_updated', 'time_request', 'time_accept', 'time_reject', 'time_paid', 'time_cancel', 'user_created', 'user_updated', 'user_request', 'user_accept', 'user_reject', 'user_paid', 'user_cancel'], 'integer'],
            [['amount', 'receiver_fee'], 'number'],
            [['bank_account_code', 'bank_account_name'], 'string', 'max' => 255],
            [['reason', 'bank_account_branch', 'partner_payment_data'], 'string'],
            [['currency', 'bank_card_month', 'bank_card_year'], 'string', 'max' => 10],
            [['reference_code_merchant', 'reference_code'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'type',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'time_begin' => 'Time Begin',
            'time_end' => 'Time End',
            'amount' => 'Amount',
            'receiver_fee' => 'Receiver Fee',
            'currency' => 'Currency',
            'method_id' => 'method_id',
            'bank_id' => 'bank_id',
            'payment_method_id' => 'Payment Method ID',
            'partner_payment_id' => 'partner_payment_id',
            'bank_account_code' => 'bank_account_code',
            'bank_account_name' => 'bank_account_name',
            'bank_account_branch' => 'bank_account_branch',
            'bank_card_month' => 'bank_card_month',
            'bank_card_year' => 'bank_card_year',
            'partner_payment_data' => 'partner_payment_data',
            'transaction_id' => 'Transaction ID',
            'reason_id' => 'Reason ID',
            'reason' => 'Reason',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_request' => 'Time Request',
            'time_accept' => 'Time Accept',
            'time_reject' => 'Time Reject',
            'time_paid' => 'Time Paid',
            'time_cancel' => 'Time Cancel',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_request' => 'User Request',
            'user_accept' => 'User Accept',
            'user_reject' => 'User Reject',
            'user_paid' => 'User Paid',
            'user_cancel' => 'User Cancel',
            'reference_code_merchant' => 'reference_code merchant',
            'reference_code' => 'reference_code'
        ];
    }

    public static function getStatus()
    {
        return array(
           // self::STATUS_NEW => 'Mới tạo',
           // self::STATUS_WAIT_VERIFY => 'Đợi merchant xác nhận',
            self::STATUS_VERIFY => 'Mới tạo',
            self::STATUS_WAIT_ACCEPT => 'Đã gửi, đợi duyệt',
            self::STATUS_REJECT => 'Từ chối duyệt',
            self::STATUS_ACCEPT => 'Đã duyệt',
            self::STATUS_PAID => 'Đã chuyển ngân',
            self::STATUS_CANCEL => 'Đã hủy',
        );
    }

    public static function getTypes()
    {
        return array(
            self::TYPE_CHECKOUT_ORDER => 'Đơn thanh toán',
            self::TYPE_CARD_TRANSACTION => 'Thẻ cào',
        );
    }

    public static function getTimeBegin($merchant_id)
    {
        $cashin_info = Tables::selectOneDataTable("cashin", ["merchant_id = :merchant_id AND status IN (:status)", "merchant_id" => $merchant_id, "status" => [Cashout::STATUS_ACCEPT, Cashout::STATUS_PAID]], "time_end DESC, id DESC ");
        if ($cashin_info != false) {
            return $cashin_info['time_end'];
        } else {
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["merchant_id = :merchant_id AND status = :status", "merchant_id" => $merchant_id], "time_created ASC, id ASC ");
            if ($checkout_order_info != false) {
                return $checkout_order_info['time_created'];
            }
        }
        return 0;
    }

    public static function checkAddCashout($merchant_id, $type)
    {
        $cashout_info = Tables::selectOneDataTable("cashout", ["merchant_id = :merchant_id AND type = :type AND status IN(:status)", "merchant_id" => $merchant_id, "type" => $type, "status" => [Cashout::STATUS_NEW, Cashout::STATUS_WAIT_VERIFY, Cashout::STATUS_VERIFY, Cashout::STATUS_WAIT_ACCEPT]]);
        if ($cashout_info == false) {
            return true;
        }
        return false;
    }

    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'add-checkout-order' => array('title' => 'Thêm yêu cầu rút tiền đơn hàng', 'confirm' => false, 'check-all' => true),
            'import-checkout-order' => array('title' => 'Import yêu cầu rút tiền đơn hàng', 'confirm' => false, 'check-all' => true),
            'add-card-transaction' => array('title' => 'Thêm yêu cầu rút tiền thẻ cào', 'confirm' => false, 'check-all' => true),
            'update-status-wait-verify' => array('title' => 'Thông báo Merchant xác nhận YC rút', 'confirm' => true),
            'update-status-verify' => array('title' => 'Xác nhận YC rút', 'confirm' => true),
            'update-status-wait-accept' => array('title' => 'Gửi duyệt YC rút', 'confirm' => false),
            'update-status-reject' => array('title' => 'Từ chối YC rút', 'confirm' => false),
            'update-status-accept' => array('title' => 'Duyệt YC rút', 'confirm' => true),
            'update-status-paid' => array('title' => 'Đã chuyển ngân YC rút', 'confirm' => false),
            'update-status-cancel' => array('title' => 'Hủy YC rút', 'confirm' => false),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        switch ($row['status']) {
            case self::STATUS_NEW:
                $result['update-status-wait-verify'] = $operators['update-status-wait-verify'];
                $result['update-status-cancel'] = $operators['update-status-cancel'];
                break;
            case self::STATUS_WAIT_VERIFY:
                $result['update-status-verify'] = $operators['update-status-verify'];
                $result['update-status-cancel'] = $operators['update-status-cancel'];
                break;
            case self::STATUS_VERIFY:
                $result['update-status-wait-accept'] = $operators['update-status-wait-accept'];
                $result['update-status-cancel'] = $operators['update-status-cancel'];
                break;
            case self::STATUS_WAIT_ACCEPT:
                $result['update-status-reject'] = $operators['update-status-reject'];
                $result['update-status-accept'] = $operators['update-status-accept'];
                break;
            case self::STATUS_ACCEPT:
                $result['update-status-paid'] = $operators['update-status-paid'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function setRow(&$row)
    {
        $merchant_id = $row['merchant_id'];
        if (intval($merchant_id) > 0) {
            $merchant = Tables::selectOneDataTable('merchant', ['id = :id', 'id' => $merchant_id]);
            $row['merchant_info'] = $merchant;
        }

        $partner_id = $row['partner_id'];
        if (intval($partner_id) > 0) {
            $partner = Tables::selectOneDataTable('partner', ['id = :id', 'id' => $partner_id]);
            $row['partner_info'] = $partner;
        }
        $method_id = $row['method_id'];
        if (intval($method_id) > 0) {
            $method = Tables::selectOneDataTable('method', ['id = :id', 'id' => $method_id]);
            $row['method_info'] = $method;
        }
        $bank_id = $row['bank_id'];
        if (intval($bank_id) > 0) {
            $bank = Tables::selectOneDataTable('bank', ['id = :id', 'id' => $bank_id]);
            $row['bank_info'] = $bank;
        }
        $payment_method_id = $row['payment_method_id'];
        if (intval($payment_method_id) > 0) {
            $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
            $row['payment_method_info'] = $payment_method;
        }
        $partner_payment_id = $row['partner_payment_id'];
        if (intval($partner_payment_id) > 0) {
            $partner_payment = Tables::selectOneDataTable('partner_payment', ['id = :id', 'id' => $partner_payment_id]);
            $row['partner_payment_info'] = $partner_payment;
        }
        $transaction_id = $row['transaction_id'];
        if (intval($transaction_id) > 0) {
            $transaction_info = Tables::selectOneDataTable('transaction', ['id = :id', 'id' => $transaction_id]);
            $row['transaction_info'] = Transaction::setRow($transaction_info);
        }

        $reason_id = $row['reason_id'];
        if (intval($reason_id) > 0) {
            $reason = Tables::selectOneDataTable('reason', ['id = :id', 'id' => $reason_id]);
            $row['reason_info'] = $reason;
        }
        if (trim($row['partner_payment_data']) != '') {
            $row['partner_payment_data'] = json_decode($row['partner_payment_data'], true);
        }
        
        $types = self::getTypes();
        $row['type_name'] = $types[$row['type']];

        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows, $set_row = true)
    {
        $partner_ids = array();
        $merchant_ids = array();
        $method_ids = array();
        $payment_method_ids = array();
        $partner_payment_ids = array();
        $transaction_ids = array();
        $bank_ids = array();
        $reason_ids = array();

        $partners = array();
        $merchants = array();
        $methods = array();
        $payment_methods = array();
        $partner_payments = array();
        $transactions = array();
        $banks = array();
        $reasons = array();
        foreach ($rows as $row) {
            if (intval($row['merchant_id']) > 0) {
                $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            }
            if (intval($row['partner_id']) > 0) {
                $partner_ids[$row['partner_id']] = $row['partner_id'];
            }
            if (intval($row['method_id']) > 0) {
                $method_ids[$row['method_id']] = $row['method_id'];
            }
            if (intval($row['payment_method_id']) > 0) {
                $payment_method_ids[$row['payment_method_id']] = $row['payment_method_id'];
            }
            if (intval($row['partner_payment_id']) > 0) {
                $partner_payment_ids[$row['partner_payment_id']] = $row['partner_payment_id'];
            }
            if (intval($row['transaction_id']) > 0) {
                $transaction_ids[$row['transaction_id']] = $row['transaction_id'];
            }
            if (intval($row['bank_id']) > 0) {
                $bank_ids[$row['bank_id']] = $row['bank_id'];
            }
            if (intval($row['reason_id']) > 0) {
                $reason_ids[$row['reason_id']] = $row['reason_id'];
            }

        }

        if (!empty($merchant_ids)) {
            $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        }
        if (!empty($partner_ids)) {
            $partners = Tables::selectAllDataTable("partner", "id IN (" . implode(',', $partner_ids) . ") ", "", "id");
        }
        if (!empty($method_ids)) {
            $methods = Tables::selectAllDataTable("method", "id IN (" . implode(',', $method_ids) . ") ", "", "id");
        }
        if (!empty($payment_method_ids)) {
            $payment_methods = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ") ", "", "id");
        }
        if (!empty($partner_payment_ids)) {
            $partner_payments = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ") ", "", "id");
        }
        if (!empty($bank_ids)) {
            $banks = Tables::selectAllDataTable("bank", "id IN (" . implode(',', $bank_ids) . ") ", "", "id");
        }
        if (!empty($transaction_ids)) {
            $transactions_info = Tables::selectAllDataTable("transaction", "id IN (" . implode(',', $transaction_ids) . ") ", "", "id");
            if ($set_row) {
                $transactions = Transaction::setRows($transactions_info);
            }
        }
        if (!empty($reason_ids)) {
            $reasons = Tables::selectAllDataTable("reason", "id IN (" . implode(',', $reason_ids) . ") ", "", "id");
        }
        $types = self::getTypes();
        foreach ($rows as $key => $row) {
            $rows[$key]['type_name'] = $types[$row['type']];
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['partner_info'] = @$partners[$row['partner_id']];
            $rows[$key]['method_info'] = @$methods[$row['method_id']];
            $rows[$key]['payment_method_info'] = @$payment_methods[$row['payment_method_id']];
            $rows[$key]['partner_payment_info'] = @$partner_payments[$row['partner_payment_id']];
            $rows[$key]['transaction_info'] = @$transactions[$row['transaction_id']];
            $rows[$key]['bank_info'] = @$banks[$row['bank_id']];
            $rows[$key]['reason_info'] = @$reasons[$row['reason_id']];
            $rows[$key]['operators'] = Cashout::getOperatorsByStatus($row);
        }

        User::setUsernameForRows($rows);
        return $rows;
    }
    
    public static function getWithdrawTransactionIsPaying($cashout_id) {
        $transaction_info = Tables::selectAllDataTable("transaction", ["cashout_id = :cashout_id AND status = :status ", "cashout_id" => $cashout_id, "status" => Transaction::STATUS_PAYING], "id ASC", "id");
        if ($transaction_info != false) {
            return $transaction_info;
        }
        return false;
    }
    
    public static function getNganLuongAuthorizationReferenceCodeByCashoutId($cashout_id) {
        $withdraw_transactions = self::getWithdrawTransactionIsPaying($cashout_id);
        if ($withdraw_transactions != false) {
            return self::getNganLuongAuthorizationReferenceCode($withdraw_transactions);
        }
        return false;
    }
    
    public static function getNganLuongAuthorizationReferenceCode($withdraw_transactions) {
        $authorization_reference_code = $GLOBALS['PREFIX'];
        foreach ($withdraw_transactions as $row) {
            $authorization_reference_code.= '-'.$row['id'];
        }
        return $authorization_reference_code;
    }
}
