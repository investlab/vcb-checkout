<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use yii\db\Query;

/**
 * This is the model class for table "checkout_order_backup".
 *
 * @property integer $id
 * @property string $token_code
 * @property integer $partner_id
 * @property integer $merchant_id
 * @property string $version
 * @property integer $language_id
 * @property string $order_code
 * @property string $order_description
 * @property double $amount
 * @property double $cashin_amount
 * @property double $cashout_amount
 * @property double $sender_fee
 * @property double $receiver_fee
 * @property string $currency
 * @property string $return_url
 * @property string $cancel_url
 * @property string $notify_url
 * @property integer $time_limit
 * @property string $buyer_fullname
 * @property string $buyer_email
 * @property string $buyer_mobile
 * @property string $buyer_address
 * @property integer $transaction_id
 * @property integer $refund_transaction_id
 * @property integer $cashout_id
 * @property integer $status
 * @property integer $callback_status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_paid
 * @property integer $time_success
 * @property integer $time_refund
 * @property integer $time_withdraw
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_paid
 * @property integer $user_success
 * @property integer $user_refund
 * @property string $user_withdraw
 */
class CheckoutOrderBackup extends MyActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_PAYING = 2;
    const STATUS_PAID = 3;
    const STATUS_CANCEL = 4;
    const STATUS_REVIEW = 5;
    const STATUS_WAIT_REFUND = 6;
    const STATUS_REFUND = 7;
    const STATUS_WAIT_WIDTHDAW = 8;
    const STATUS_WIDTHDAW = 9;
    const CALLBACK_STATUS_NEW = 1;
    const CALLBACK_STATUS_PROCESSING = 2;
    const CALLBACK_STATUS_ERROR = 3;
    const CALLBACK_STATUS_SUCCESS = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'checkout_order_backup';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token_code', 'language_id', 'partner_id', 'merchant_id', 'version', 'order_code', 'amount', 'currency', 'status', 'callback_status'], 'required'],
            [['partner_id', 'language_id', 'merchant_id', 'time_limit', 'transaction_id', 'refund_transaction_id', 'cashout_id', 'status', 'callback_status', 'time_created', 'time_updated', 'time_paid', 'time_success', 'time_refund', 'time_withdraw', 'user_created', 'user_updated', 'user_paid', 'user_success', 'user_refund', 'user_withdraw'], 'integer'],
            [['order_description', 'return_url', 'cancel_url', 'notify_url', 'buyer_address'], 'string'],
            [['amount', 'cashin_amount', 'cashout_amount', 'sender_fee', 'receiver_fee'], 'number'],
            [['version', 'currency'], 'string', 'max' => 10],
            [['order_code'], 'string', 'max' => 25],
            [['buyer_fullname', 'buyer_email', 'token_code'], 'string', 'max' => 255],
            [['buyer_mobile'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token_code' => 'token_code',
            'partner_id' => 'Partner ID',
            'merchant_id' => 'Merchant ID',
            'version' => 'Version',
            'language_id' => 'language_id',
            'order_code' => 'Order Code',
            'order_description' => 'Order Description',
            'amount' => 'Amount',
            'cashin_amount' => 'Cashin Amount',
            'cashout_amount' => 'Cashout Amount',
            'sender_fee' => 'Sender Fee',
            'receiver_fee' => 'Receiver Fee',
            'currency' => 'Currency',
            'return_url' => 'Return Url',
            'cancel_url' => 'Cancel Url',
            'notify_url' => 'Notify Url',
            'time_limit' => 'Time Limit',
            'buyer_fullname' => 'Buyer Fullname',
            'buyer_email' => 'Buyer Email',
            'buyer_mobile' => 'Buyer Mobile',
            'buyer_address' => 'Buyer Address',
            'transaction_id' => 'Transaction ID',
            'refund_transaction_id' => 'refund_transaction_id',
            'cashout_id' => 'cashout_id',
            'status' => 'Status',
            'callback_status' => 'callback_status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_paid' => 'Time Paid',
            'time_success' => 'Time Success',
            'time_refund' => 'Time Refund',
            'time_withdraw' => 'Time Withdraw',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_paid' => 'User Paid',
            'user_success' => 'User Success',
            'user_refund' => 'User Refund',
            'user_withdraw' => 'User Withdraw',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NEW => 'Chưa thanh toán',
            self::STATUS_PAYING => 'Đang thanh toán',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_CANCEL => 'Đã hủy',
            self::STATUS_REVIEW => 'Bị review',
            self::STATUS_WAIT_REFUND => 'Đang đợi hoàn tiền',
            self::STATUS_REFUND => 'Đã hoàn tiền',
            //self::STATUS_WAIT_WIDTHDAW => 'Đang rút tiền',
            //self::STATUS_WIDTHDAW => 'Đã rút tiền',
        );
    }

    public static function getCallbackStatus()
    {
        return array(
            self::CALLBACK_STATUS_NEW => 'Chưa gọi merchant',
            self::CALLBACK_STATUS_PROCESSING => 'Đang gọi merchant',
            self::CALLBACK_STATUS_ERROR => 'Lỗi khi gọi lại merchant',
            self::CALLBACK_STATUS_SUCCESS => 'Gọi lại merchant thành công',
        );
    }

    public static function getCheckoutUrl($version, $token_code, $params = array())
    {
        return ROOT_URL . 'vi/checkout/version_' . str_replace('.', '_', $version) . '/index/' . $token_code;
    }

    public static function getNotifyUrl($checkout_order_info)
    {
        return trim($checkout_order_info['notify_url']);
    }

    public static function getParamsForNotifyUrl($checkout_order_info)
    {
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
            }
        }
        return array(
            'token_code' => $checkout_order_info['token_code'],
            'version' => strval($checkout_order_info['version']),
            'order_code' => $checkout_order_info['order_code'],
            'order_description' => $checkout_order_info['order_description'],
            'amount' => $checkout_order_info['amount'],
            'sender_fee' => floatval($checkout_order_info['sender_fee']),
            'receiver_fee' => floatval($checkout_order_info['receiver_fee']),
            'currency' => $checkout_order_info['currency'],
            'return_url' => $checkout_order_info['return_url'],
            'cancel_url' => $checkout_order_info['cancel_url'],
            'notify_url' => $checkout_order_info['notify_url'],
            'status' => intval($checkout_order_info['status']),
            'payment_method_code' => @$payment_method_info['code'],
            'payment_method_name' => @$payment_method_info['name'],
        );
    }

    private static function _getCode($checkout_order_id)
    {
        return strtoupper('CO' . substr(md5($checkout_order_id . 'checkout_order' . rand()), 9, 10));
    }

    public static function getTokenCode($checkout_order_id)
    {
        return $checkout_order_id . '-' . self::_getCode($checkout_order_id);
    }

    public static function checkTokenCode($token_code, &$checkout_order_info = null)
    {
        if (preg_match('/^(\d+)-(CO[A-Z0-9]{10})$/', $token_code, $temp)) {
            $checkout_order_id = intval($temp[1]);
            $checkout_order_info = Tables::selectOneDataTable("checkout_order_backup", ["id = :id AND token_code = :token_code ", "id" => $checkout_order_id, "token_code" => $token_code]);
            if ($checkout_order_info != false) {
                return true;
            }
        }
        return false;
    }

    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'merchant-call-back' => array('title' => 'Gọi lại merchant', 'confirm' => true),
            'update-status-paid' => array('title' => 'Cập nhật thanh toán thành công', 'confirm' => false),
            'update-status-wait-refund' => array('title' => 'Cập nhật đợi hoàn tiền', 'confirm' => false),
            'update-status-refund' => array('title' => 'Cập nhật hoàn tiền thành công', 'confirm' => false),
            'cancel-wait-refund' => array('title' => 'Hủy hoàn tiền', 'confirm' => false),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        $result['detail'] = $operators['detail'];
        switch ($row['status']) {
            case self::STATUS_PAID:
                if ($row['callback_status'] == self::CALLBACK_STATUS_ERROR || $row['callback_status'] == self::CALLBACK_STATUS_SUCCESS) {
                    $result['merchant-call-back'] = $operators['merchant-call-back'];
                }
                $result['update-status-wait-refund'] = $operators['update-status-wait-refund'];
                break;
            case self::STATUS_PAYING:
                $result['update-status-paid'] = $operators['update-status-paid'];
                break;
            case self::STATUS_WAIT_REFUND:
                $result['update-status-refund'] = $operators['update-status-refund'];
                $result['cancel-wait-refund'] = $operators['cancel-wait-refund'];
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

        $checkout_order_id = $row['id'];
        if (intval($checkout_order_id) > 0) {
            // Giao dịch
            $transaction_info = Tables::selectAllDataTable("transaction", ["checkout_order_id = :checkout_order_id", "checkout_order_id" => $checkout_order_id]);
            $row['transaction_info'] = Transaction::setRows($transaction_info);

//            Lịch sử gọi lại merchant
            $checkout_order_callback_history = Tables::selectAllDataTable("checkout_order_callback_history", ["checkout_order_id = :checkout_order_id", "checkout_order_id" => $checkout_order_id]);
            $row['checkout_order_callback_history_info'] = $checkout_order_callback_history;
        }

        // Giao dịch hiện tại
        $transaction_current_id = $row['transaction_id'];
        if (intval($transaction_current_id) > 0) {
            $transaction_current = Tables::selectOneDataTable('transaction', ['id = :id', 'id' => $transaction_current_id]);
            $payment_method_id = $transaction_current['payment_method_id'];
            if (intval($payment_method_id) > 0) {
                $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
                $row['payment_method_name'] = @$payment_method['name'];
            }
        }

        // Giao dịch hoàn tiền
        $refund_transaction_id = $row['refund_transaction_id'];
        if (intval($refund_transaction_id) > 0) {
            $refund_transaction_info = Tables::selectOneDataTable('transaction', ['id = :id', 'id' => $refund_transaction_id]);
            $row['refund_transaction'] = $refund_transaction_info;
        }




        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows)
    {
        $checkout_order_ids = array();
        $merchant_ids = array();
        $transaction_ids = array();
        $cashout_ids = array();

        $merchants = array();
        $checkout_order_callback_history = array();
        $transaction_currents = array();
        $transactions = array();

        foreach ($rows as $row) {
            if (intval($row['merchant_id']) > 0) {
                $merchant_ids[$row['merchant_id']] = $row['merchant_id'];
            }
            if (intval($row['transaction_id']) > 0) {
                $transaction_ids[$row['transaction_id']] = $row['transaction_id'];
            }
            if (intval($row['refund_transaction_id']) > 0) {
                $transaction_ids[$row['refund_transaction_id']] = $row['refund_transaction_id'];
            }
            if (intval($row['cashout_id']) > 0) {
                $cashout_ids[$row['cashout_id']] = $row['cashout_id'];
            }
            if (intval($row['id']) > 0) {
                $checkout_order_ids[$row['id']] = $row['id'];
            }
        }

        if (!empty($merchant_ids)) {
            $merchants = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ") ", "", "id");
        }
        if (!empty($cashout_ids)) {
            $cashouts = Tables::selectAllDataTable("cashout", "id IN (" . implode(',', $cashout_ids) . ") ", "", "id");
            $cashouts = Cashout::setRows($cashouts);
        }
        if (!empty($transaction_ids)) {
            $transaction_info = Tables::selectAllDataTable("transaction", "id IN (" . implode(',', $transaction_ids) . ") ", "", "id");
            $transactions = Transaction::setRows($transaction_info);
        }
        if (!empty($checkout_order_ids)) {

            $checkout_order_callback_history = Tables::selectAllDataTable("checkout_order_callback_history", "checkout_order_id IN (" . implode(',', $checkout_order_ids) . ") ", "", "id");
        }
        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['checkout_order_callback_history_info'] = @$checkout_order_callback_history[$row['id']];
            $rows[$key]['transaction_info'] = @$transactions[$row['transaction_id']];
            $rows[$key]['refund_transaction_info'] = @$transactions[$row['refund_transaction_id']];
            $rows[$key]['cashout_info'] = @$cashouts[$row['cashout_id']];
            $rows[$key]['transactions_info'] = @$transactions[$row['id']];
            $rows[$key]['transaction_current_info'] = @$transactions[$row['transaction_id']];
            $rows[$key]['operators'] = CheckoutOrderBackup::getOperatorsByStatus($row);
        }

        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function getTotalCashoutAmountForCashout($merchant_id, $currency, $time_begin, $time_end, $time_request)
    {
        $query = new Query();
        $result = $query->select("SUM(cashout_amount) AS total_cashout_amount")
            ->from("checkout_order_backup")
            ->where("merchant_id = :merchant_id AND time_created >= :time_begin AND time_created <= :time_end AND currency = :currency AND status = :status ", [
                "merchant_id" => $merchant_id,
                "time_begin" => $time_begin,
                "time_end" => $time_end,
                "currency" => $currency,
                "status" => CheckoutOrder::STATUS_PAID,
            ])->one();
        if ($result) {
            return $result['total_cashout_amount'];
        }
        return 0;
    }

    public static function getTotalCashoutAmountByCashoutId($cashout_id)
    {
        $query = new \yii\db\Query();
        $result = $query->select("SUM(cashout_amount) AS total_cashout_amount")
            ->from("checkout_order_backup")
            ->where("cashout_id = :cashout_id ", [
                "cashout_id" => $cashout_id,
            ])->one();
        if ($result) {
            return $result['total_cashout_amount'];
        }
        return 0;
    }

}
