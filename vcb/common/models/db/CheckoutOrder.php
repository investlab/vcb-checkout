<?php

namespace common\models\db;

use common\components\utils\Encryption;
use common\components\utils\Strings;
use Exception;
use Yii;
use common\components\libs\Tables;
use yii\db\Query;

/**
 * This is the model class for table "checkout_order".
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
 * @property string installment_info
 * @property string $object_code
 * @property string $object_name
 * @property int $installment_cycle
 * @property mixed|null $currency_exchange
 * @property mixed|string|null $seamless_info
 * @property string $client_info
 * @property string $receipt_url
 */
class CheckoutOrder extends MyActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_PAYING = 2;
    const STATUS_PAID = 3;
    const STATUS_CANCEL = 4;
    const STATUS_REVIEW = 5;
    const STATUS_WAIT_REFUND = 6;
    const STATUS_REFUND = 7;
    const STATUS_REFUND_PARTIAL = 11;
    const STATUS_WAIT_WIDTHDAW = 8;
    const STATUS_WIDTHDAW = 9;
    const STATUS_INSTALLMENT_WAIT = 10;
    const STATUS_FAILURE = 12;
    const STATUS_REVERT = 15;
    const CALLBACK_STATUS_NEW = 1;
    const CALLBACK_STATUS_PROCESSING = 2;
    const CALLBACK_STATUS_ERROR = 3;
    const CALLBACK_STATUS_SUCCESS = 4;

    const EXPORTED_3C_FLAG = 'EXPORTED 3C';
    /**
     * @var int|mixed|null
     */
    private $card_token_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'checkout_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token_code', 'language_id', 'partner_id', 'merchant_id', 'version', 'order_code', 'amount', 'currency', 'status', 'callback_status'], 'required'],
            [['partner_id', 'language_id', 'merchant_id', 'time_limit', 'transaction_id', 'refund_transaction_id', 'cashout_id', 'status', 'callback_status', 'time_created', 'time_updated', 'time_paid', 'time_success', 'time_refund', 'time_withdraw', 'user_created', 'user_updated', 'user_paid', 'user_success', 'user_refund', 'user_withdraw'], 'integer'],
            [['order_description', 'return_url', 'cancel_url', 'notify_url', 'buyer_address', 'client_info', 'receipt_url'], 'string'],
            [['amount', 'cashin_amount', 'cashout_amount', 'sender_fee', 'receiver_fee'], 'number'],
            [['version', 'currency'], 'string', 'max' => 10],
            [['order_code'], 'string', 'max' => 150],
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
            'object_code' => 'Tên sản phẩm',
            'object_name' => 'Mã khách hàng',
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
            self::STATUS_REFUND => 'Đã hoàn toàn bộ',
            self::STATUS_REFUND_PARTIAL => 'Đã hoàn một phần',
            self::STATUS_INSTALLMENT_WAIT => 'Đã thanh toán, đợi duyệt trả góp',
            self::STATUS_FAILURE => 'Giao dịch thất bại',
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

    public static function getPaymentMethod($params)
    {
        $method = self::getMethodCode($params['payment_method_code']);

        return strtoupper($params['bank_code'] . '-' . $method);
    }

    public static function getMethodCode($method_code)
    {
        if (strtoupper($method_code) == 'QRCODE' || strtoupper($method_code) == 'QR-CODE') {
            $method_code = 'QR-CODE';
        } else if (strtoupper($method_code) == 'ATM-CARD' || strtoupper($method_code) == 'ATM-ONLINE') {
            $method_code = 'ATM-CARD';
        }

        return $method_code;
    }

    public static function getCheckoutUrl($version, $token_code, $params = array())
    {
        $payment_method = '';
        $result_url = ROOT_URL . $params['language'] . '/checkout/version_' . str_replace('.', '_', $version) . '/index/' . $token_code;

        // Check các merchant được bật thanh toán không cần chọn phương thức
        //if (in_array($params['merchant_site_code'], $GLOBALS['MERCHANT_ON_SEAMLESS'])) {

        if (!empty($params['payment_method_code']) && !empty($params['bank_code'])) {

            $payment_method = self::getPaymentMethod($params);
            $result_url = ROOT_URL . 'vi/checkout/version_' . str_replace('.', '_', $version) . '/request/' . $token_code . '/' . $payment_method;
        }
        if (!empty($params['payment_method_code']) && empty($params['bank_code'])) {
            $result_url = ROOT_URL . $params['language'] . '/checkout/version_' . str_replace('.', '_', $version) . '/index/' . $token_code . '?method_code=' . $params['payment_method_code'];
        }
        //}

        return $result_url;
    }

    public static function getNotifyUrl($checkout_order_info)
    {
        return trim($checkout_order_info['notify_url']);
    }

    public static function getParamsForNotifyUrl($checkout_order_info)
    {
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if (in_array($payment_method_info['code'], ['VISA-TOKENIZATION', 'MASTERCARD-TOKENIZATION', 'JCB-TOKENIZATION'])) {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
        }
        $result = array(
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => isset($transaction_info['reason']) ? $transaction_info['reason'] : '',
        );
        if (isset($checkout_order_info['customer_field']) && $checkout_order_info['customer_field'] != "") {
            $result['customer_field'] = $checkout_order_info['customer_field'];
        }


        return $result;
    }

    public static function getParamsForNotifyUrlXanhPon($checkout_order_info)
    {
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if (in_array($payment_method_info['code'], ['VISA-TOKENIZATION', 'MASTERCARD-TOKENIZATION', 'JCB-TOKENIZATION'])) {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
        }
        $result = array(
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => isset($transaction_info['reason']) ? $transaction_info['reason'] : '',
            'time_paid' => $checkout_order_info['time_paid'],
        );
        if (isset($checkout_order_info['customer_field']) && $checkout_order_info['customer_field'] != "") {
            $result['customer_field'] = $checkout_order_info['customer_field'];
        }


        return $result;
    }

    /** Cho MC Viet Ha Chi */
    public static function getParamsForNotifyUrlVhc($checkout_order_info)
    {
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if (in_array($payment_method_info['code'], ['VISA-TOKENIZATION', 'MASTERCARD-TOKENIZATION', 'JCB-TOKENIZATION'])) {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
        }

        if(isset($transaction_info) && isset($transaction_info['id'])){
            $authCode = $transaction_info['id'];
            $refNo = $GLOBALS['PREFIX'] .  $transaction_info['id'];
        } else{
            $authCode = '';
            $refNo = '';
        }

        $result = array(
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => isset($transaction_info) && isset($transaction_info['reason']) ? $transaction_info['reason'] : '',
            'va_account_name' => 'KHACH HANG',
            'va_account_number' => '**********',
            'va_bank_code' => $transaction_info['bank_code_payment'] ?? '',
            'authCode' => $authCode,
            'refNo' => $refNo,
        );
        if (isset($checkout_order_info['customer_field']) && $checkout_order_info['customer_field'] != "") {
            $result['customer_field'] = $checkout_order_info['customer_field'];
        }


        return $result;
    }

    public static function getParamsForNotifyUrlForBCA($checkout_order_info)
    {
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if (in_array($payment_method_info['code'], ['VISA-TOKENIZATION', 'MASTERCARD-TOKENIZATION', 'JCB-TOKENIZATION'])) {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
        }

        //TODO Sửa trường amount theo yc BCA
        if (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_BCA'])) {
            $curency_info = json_decode($checkout_order_info['currency_exchange'], true);
            $currency_exchange_field = isset($curency_info['transfer']) ? $curency_info['transfer'] : "";
            $amount_vnd = isset($checkout_order_info['amount']) ? $checkout_order_info['amount'] : "";
            $amount = empty($checkout_order_info['currency_exchange']) ? $checkout_order_info['amount'] : $checkout_order_info['amount'] / json_decode(@$checkout_order_info['currency_exchange'])->transfer;
        } else {
            //OLD
            $amount = $checkout_order_info['amount'];
            $amount_vnd = '';
        }

        $result = array(
            'token_code' => $checkout_order_info['token_code'],
            'version' => strval($checkout_order_info['version']),
            'order_code' => $checkout_order_info['order_code'],
            'order_description' => $checkout_order_info['order_description'],
//            'amount' => $checkout_order_info['amount'],
            'amount' => $amount,
            'sender_fee' => floatval($checkout_order_info['sender_fee']),
            'receiver_fee' => floatval($checkout_order_info['receiver_fee']),
            'currency' => empty($checkout_order_info['currency_exchange']) ? $checkout_order_info['currency'] : "USD",
            'return_url' => $checkout_order_info['return_url'],
            'cancel_url' => $checkout_order_info['cancel_url'],
            'notify_url' => $checkout_order_info['notify_url'],
            'status' => intval($checkout_order_info['status']),
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => isset($transaction_info['reason']) ? $transaction_info['reason'] : '',
            'time_paid' => !empty($checkout_order_info['time_paid']) ? $checkout_order_info['time_paid'] : '',
        );
        if (isset($checkout_order_info['customer_field']) && $checkout_order_info['customer_field'] != "") {
            $result['customer_field'] = $checkout_order_info['customer_field'];
        }
        if (in_array($checkout_order_info['merchant_id'], $GLOBALS['MERCHANT_BCA'])) {
            $result['amount_vnd'] = $amount_vnd;
            $result['currency_exchange'] = $currency_exchange_field;
        }


        return $result;
    }

    public static function getParamsForNotifyUrlQni($checkout_order_info)
    {

        $merchant_pass = Merchant::getApiKey($checkout_order_info['merchant_id']);
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if ($payment_method_info['code'] == 'VISA-TOKENIZATION') {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => $transaction_info['reason'] ?? '',
            'qni_verify' => $merchant_pass,
        );
    }

    public static function getParamsForNotifyUrlFubon($checkout_order_info)
    {
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if ($payment_method_info['code'] == 'VISA-TOKENIZATION') {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => $payment_method_info['name'] ?? '',
            'message' => $result_message,
            'message_error' => $transaction_info['reason'] ?? '',
        );
    }

    public static function getParamsForNotifyUrlDni($checkout_order_info)
    {

        $merchant_pass = Merchant::getApiKey($checkout_order_info['merchant_id']);
        $result_message = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if ($payment_method_info['code'] == 'VISA-TOKENIZATION') {
                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
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
            'payment_method_code' => $payment_method_info['code'] ?? '',
            'payment_method_name' => Strings::_convertToSMS($payment_method_info['name']) ?? '',
            'message' => $result_message,
            'message_error' => $transaction_info['reason'] ?? '',
        );
    }

    public static function getParamsForNotifyUrlBCA($checkout_order_info): array
    {
        $so_the = '';
        $bank_code = '';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id AND checkout_order_id = :checkout_order_id ", "id" => $checkout_order_info['transaction_id'], "checkout_order_id" => $checkout_order_info['id']]);
            if ($transaction_info != false) {
                $payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $transaction_info['payment_method_id']]);
                if ($payment_method_info['code'] == 'VISA-TOKENIZATION') {


                    return self::getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info);
                }
                $bank_info = Tables::selectOneDataTable("bank", ["id = :id", "id" => $payment_method_info['bank_id']]);
                if ($bank_info) {
                    $bank_code = $bank_info['code'];
                }
            }
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_FAILURE) {
                $result_message = 'Giao dịch thất bại';
            }
        } else {
            $result_message = 'Mới tạo';
        }
        if (!empty($transaction_info['card_info']) && (json_decode($transaction_info['card_info'], true)) && isset(json_decode($transaction_info['card_info'], true)['card_number'])) {
            $so_the = json_decode($transaction_info['card_info'], true)['card_number'];
        }
        if ($checkout_order_info['transaction_timeout'] == 1) {
            $transaction_timeout = 'true';
        } else {
            $transaction_timeout = 'false';
        }
//        Fix cứng test BCA QUANGNT

        //TODO Thêm amount_vnd va currency_exchange
        $curency_info = json_decode($checkout_order_info['currency_exchange'], true);
        $currency_exchange_field = isset($curency_info['transfer']) ? $curency_info['transfer'] : "";
        $amount_vnd = isset($checkout_order_info['amount']) ? $checkout_order_info['amount'] : "";

        return array(
            'order_code' => $checkout_order_info['order_code'],
            'token_code' => $checkout_order_info['token_code'],
            'version' => strval($checkout_order_info['version']),
            'amount' => empty($checkout_order_info['currency_exchange']) ? $checkout_order_info['amount'] : $checkout_order_info['amount'] / json_decode($checkout_order_info['currency_exchange'])->transfer,
            'currency' => empty($checkout_order_info['currency_exchange']) ? $checkout_order_info['currency'] : "USD",
            'amount_vnd' => $amount_vnd,
            'currency_exchange' => $currency_exchange_field,
            'status' => intval($checkout_order_info['status']),
            'so_the' => $so_the,
            'transaction_timeout' => $transaction_timeout,
            'order_description' => $checkout_order_info['order_description'],
            'bank_code' => $bank_code,
            'thoi_gian_gd' => !empty($checkout_order_info['time_paid']) ? date('d-m-Y H:i:s', $checkout_order_info['time_paid']) : date('d-m-Y H:i:s', time())
        );
    }


    public static function getParamsForNotifyUrlCardToken($checkout_order_info, $transaction_info)
    {
        $result_message = '';
        $result_code = '0001';
        if (intval($checkout_order_info['transaction_id']) != 0) {
            $result_error = json_decode($transaction_info['partner_payment_info'], false);
            if (intval($checkout_order_info['status']) == self::STATUS_NEW) {
                $result_message = 'Mới tạo';
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAYING) {
                if (!isset($result_error->error_message) || $result_error->error_message == '') {
                    $result_message = 'Đang thanh toán';
                } else {
                    $result_message = $result_error->error_message;
                }
            } else if (intval($checkout_order_info['status']) == self::STATUS_PAID) {
                $result_message = 'Đã thanh toán';
                $result_code = '0000';
            } else if (intval($checkout_order_info['status']) == self::STATUS_CANCEL) {
                $result_message = 'Đã hủy';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REVIEW) {
                $result_message = 'Bị review';
            } else if (intval($checkout_order_info['status']) == self::STATUS_WAIT_REFUND) {
                $result_message = 'Đang hoàn tiền';
            } else if (intval($checkout_order_info['status']) == self::STATUS_REFUND) {
                $result_message = 'Đã hoàn tiền';
            }
        } else {
            $result_message = 'Mới tạo';
        }
        return [
            'result_code' => $result_code,
            'result_message' => $result_message,
            'result_data' => [
                'token_code' => $checkout_order_info['token_code'],
                'version' => strval($checkout_order_info['version']),
                'order_code' => $checkout_order_info['order_code'],
                'order_description' => $checkout_order_info['order_description'],
                'amount' => $checkout_order_info['amount'],
                'sender_fee' => floatval($checkout_order_info['sender_fee']),
                'receiver_fee' => floatval($checkout_order_info['receiver_fee']),
                'notify_url' => $checkout_order_info['notify_url'],
                'status' => intval($checkout_order_info['status']),
                'checksum' => self::_getChecksumNotifyUrlForCardToken($checkout_order_info)
            ]
        ];
    }

    private static function _getChecksumNotifyUrlForCardToken($params)
    {
        $list_data = [
            'token_code',
            'version',
            'order_code',
            'order_description',
            'amount',
            'sender_fee',
            'receiver_fee',
        ];

        $string_data = '';
        $is_first_key = false;
        foreach ($list_data as $key) {
            if ($is_first_key) {
                $string_data .= '&' . $key . '=' . $params[$key];
            } else {
                $string_data .= $key . '=' . $params[$key];
                $is_first_key = true;
            }
        }
        $merchant_pass = self::getMerchantPass($params['merchant_id']);


        return Encryption::hashHmacSHA256($string_data, $merchant_pass);
    }

    private static function getMerchantPass($merchant_id)
    {
        $merchant_password = '';
        $merchant_info = Merchant::findOne(['id' => $merchant_id]);
        if ($merchant_info->password) {
            $merchant_password = @$merchant_info->password;
        }
        return $merchant_password;

    }


    private static function _getCode($checkout_order_id)
    {
        return strtoupper('CO' . substr(md5($checkout_order_id . 'checkout_order' . rand()), 9, 10));
    }

    public static function getTokenCode($checkout_order_id)
    {
        return $checkout_order_id . '-' . self::_getCode($checkout_order_id);
    }

    public static function getTransactionRefund($params)
    {
        $status = 2;
        $time = time();


        if ($params['transaction_refund_id'] == $params['checkout_order_info']['refund_transaction_id']) {
            $checkout_order_info = $params['checkout_order_info'];
            if ($checkout_order_info != false) {
                $transaction_info = Tables::selectOneDataTable("transaction", ["id = :id ", "id" => $params['transaction_refund_id']]);
                if ($transaction_info != false) {
                    if ($checkout_order_info['status'] == 7 || $checkout_order_info['status'] == 11) {
                        $status = 1;
                    } elseif ($checkout_order_info['status'] == 6) {
                        $status = 3;
                    }
                    return [
                        'error_code' => '0000',
                        'result_data' => [
                            'ref_code_refund' => $params['ref_code_refund'],
                            'amount' => $transaction_info['amount'],
                            'transaction_status' => $status,
                            'transaction_refund_id' => $params['transaction_refund_id'],
                            'token_code' => $checkout_order_info['token_code'],
                            'checksum' => hash('sha256', $params['ref_code_refund'] . ' ' . $checkout_order_info['token_code'] . ' ' . $checkout_order_info['refund_transaction_id'] . ' ' . Merchant::getApiKey($checkout_order_info['merchant_id'])),
                        ]


                    ];
                } else {
                    return [
                        'error_code' => '0001',
                        'result_data' => [
                        ]


                    ];
                }

            }
        }


        return [
            'error_code' => '0001',
            'result_data' => [
            ]


        ];

    }

    public static function checkTokenCode($token_code, &$checkout_order_info = null)
    {
        if (preg_match('/^(\d+)-(CO[A-Z0-9]{10})$/', $token_code, $temp)) {
            $checkout_order_id = intval($temp[1]);
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND token_code = :token_code ", "id" => $checkout_order_id, "token_code" => $token_code]);
            if ($checkout_order_info != false) {
                return true;
            }
        }
        return false;
    }

    public static function getByOrderId($order_id, $merchant_id = false)
    {
        $obj = CheckoutOrder::find()
            ->where(['order_code' => $order_id]);
        if ($merchant_id) {
            $obj->andWhere(['merchant_id' => $merchant_id]);
        }
        $checkout_orders = $obj->all();
//        $checkout_orders = $obj->createCommand()->getRawSql();
        if ($checkout_orders) {
            return $checkout_orders;
        } else {
            return false;
        }

    }
    public static function getByOrderMposCode($order_id,$merchant_id)
    {
        $obj = CheckoutOrder::find()
            ->where(['order_code' => 'MPOS_'.$order_id]);
        if ($merchant_id) {
            $obj->andWhere(['merchant_id' => $merchant_id]);
        }
        $checkout_orders = $obj->all();
//        $checkout_orders = $obj->createCommand()->getRawSql();
        if ($checkout_orders) {
            return $checkout_orders;
        } else {
            return false;
        }

    }

    public static function checkRefCodeRefund($ref_code_refund, &$checkout_order_info = null)
    {
        $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["ref_code_refund = :ref_code_refund ", "ref_code_refund" => $ref_code_refund]);
        if ($checkout_order_info != false) {
            return true;
        }
        return false;
    }

    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'merchant-call-back' => array('title' => 'Gọi lại merchant', 'confirm' => true),
            'update-status-paid' => array('title' => 'Cập nhật thanh toán thành công', 'confirm' => false),
            'update-status-wait-refund' => array('title' => 'Hoàn tiền', 'confirm' => false),
            'update-status-refund' => array('title' => 'Cập nhật hoàn tiền thành công', 'confirm' => false),
            'cancel-wait-refund' => array('title' => 'Hủy hoàn tiền', 'confirm' => false),
            'active-review' => array('title' => 'Duyệt giao dịch', 'confirm' => true),
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
            case self::STATUS_REVIEW:
                $result['active-review'] = $operators['active-review'];
                break;
            case self::STATUS_REFUND_PARTIAL:
                $result['update-status-wait-refund'] = $operators['update-status-wait-refund'];
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
            $payment_method_id = isset($transaction_current['payment_method_id']) ? $transaction_current['payment_method_id'] : 0;
            if (intval($payment_method_id) > 0) {
                $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
                $row['payment_method_name'] = @$payment_method['name'];
            }

            $payment_method_id = isset($transaction_current['payment_method_id']) ? $transaction_current['payment_method_id'] : 0;
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

    public static function setRowForApp(&$row)
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
        $bank_name = '';
        // Giao dịch hiện tại
        $transaction_current_id = $row['transaction_id'];
        if (intval($transaction_current_id) > 0) {
            $transaction_current = Tables::selectOneDataTable('transaction', ['id = :id', 'id' => $transaction_current_id]);
            $payment_method_id = isset($transaction_current['payment_method_id']) ? $transaction_current['payment_method_id'] : 0;
            if (intval($payment_method_id) > 0) {
                $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
                $row['payment_method_name'] = @$payment_method['name'];
                if ($payment_method['bank_id'] > 0) {
                    $bank_info = Tables::selectOneDataTable('bank', ['id = :id', 'id' => $payment_method['bank_id']]);
                    if ($bank_info['name'] && str_contains($bank_info['name'], 'Ngân hàng') != false) {

                        $bank_name = strtoupper(trim(str_replace('Ngân hàng', '', $bank_info['name'])));
                    }

                }
            }
            $row['bank_name'] = $bank_name;

            $payment_method_id = isset($transaction_current['payment_method_id']) ? $transaction_current['payment_method_id'] : 0;
            if (intval($payment_method_id) > 0) {
                $payment_method = Tables::selectOneDataTable('payment_method', ['id = :id', 'id' => $payment_method_id]);
                $row['payment_method_name'] = @$payment_method['name'];
            }
        }
        $refunded_amount = 0;
        $refunding_amount = 0;
        // Giao dịch hoàn tiền
        foreach ($row['transaction_info'] as $key) {
            if ($key['transaction_type_id'] == 3) {
                if ($key['status'] == Transaction::STATUS_NEW || $key['status'] == Transaction::STATUS_PAYING) {
                    $refunding_amount += $key['amount'];

                }
                if ($key['status'] == Transaction::STATUS_PAID) {
                    $refunded_amount += $key['amount'];

                }
            }


        }

        $row['refund_transaction']['amount'] = $refunded_amount + $refunding_amount;
        $row['refund_transaction']['refunding_amount'] = $refunding_amount;
        $row['refund_transaction']['refunded_amount'] = $refunded_amount;


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
//            $transaction_refund = Transaction::findAll(['checkout_order_id' => $row['id'], 'transaction_type_id' => TransactionType::getRefundTransactionTypeId(), 'status' => Transaction::STATUS_PAID]);
//            $transaction_refund_paying = Transaction::find()->where([
//                'checkout_order_id' => $row['id'],
//                'transaction_type_id' => TransactionType::getRefundTransactionTypeId(),
//                'status' => [Transaction::STATUS_NEW, Transaction::STATUS_PAYING]
//            ])->all();

            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['checkout_order_callback_history_info'] = $checkout_order_callback_history[$row['id']] ?? '';
            $rows[$key]['transaction_info'] = $transactions[$row['transaction_id']] ?? "";
//            $rows[$key]['refund_transaction_info'] = @$transactions[$row['refund_transaction_id']];
//            $rows[$key]['list_refund_transaction'] = self::getTransactionId($transaction_refund);
//            $rows[$key]['refund_transaction_info']['amount'] = self::countAmountByKey($transaction_refund, 'amount');
//            $rows[$key]['refund_paying'] = self::countAmountByKey($transaction_refund_paying, 'amount');
//            $rows[$key]['refund_transaction_info']['sender_fee'] = self::countAmountByKey($transaction_refund, 'sender_fee');
//            $rows[$key]['cashout_info'] = @$cashouts[$row['cashout_id']];
            $rows[$key]['transactions_info'] = $transactions[$row['id']] ?? '';
            $rows[$key]['transaction_current_info'] = $transactions[$row['transaction_id']] ?? '';
            $rows[$key]['operators'] = CheckoutOrder::getOperatorsByStatus($row);
        }

        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function setRowsForApp(&$rows)
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

        if (!empty($transaction_ids)) {
            $transaction_info = Tables::selectAllDataTable("transaction", "id IN (" . implode(',', $transaction_ids) . ") ", "", "id");
            $transactions = Transaction::setRows($transaction_info);
        }

        foreach ($rows as $key => $row) {
            $rows[$key]['merchant_info'] = @$merchants[$row['merchant_id']];
            $rows[$key]['transaction_info'] = @$transactions[$row['transaction_id']];
            $bank_code = @$transactions[$row['transaction_id']]['payment_method_info']['code'];
            if (strpos($bank_code, '-QR-CODE')) {
                $bank_name = str_replace('-QR-CODE', '', $bank_code);

            } else {
                $bank_name = 'default';
            }
            $rows[$key]['logobank'] = ROOT_URL . 'vi/checkout/bank/ie/' . $bank_name . '.png';

        }

        return $rows;
    }

    public static function getTotalCashoutAmountForCashout($merchant_id, $currency, $time_begin, $time_end, $time_request)
    {
        $query = new Query();
        $result = $query->select("SUM(cashout_amount) AS total_cashout_amount")
            ->from("checkout_order")
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
            ->from("checkout_order")
            ->where("cashout_id = :cashout_id ", [
                "cashout_id" => $cashout_id,
            ])->one();
        if ($result) {
            return $result['total_cashout_amount'];
        }
        return 0;
    }

    public static function getCheckoutOrderById($checkout_order_id)
    {
        $checkout_order = CheckoutOrder::find()
            ->where(['id' => $checkout_order_id])
            ->asArray()
            ->one();

        if (!is_null($checkout_order)) {
            return $checkout_order;
        }
        return false;
    }

    public static function getBanks()
    {
        $rs = PaymentMethod::find()->where(['status' => 1, 'transaction_type_id' => 1])->all();

        $ignore = ["TOKENIZATION", "SWIPE-CARD", "QRCODE_OFFLINE"];
        $paymentMethod = array();
        foreach ($rs as $k => $v) {
            $arr = explode('-', $v->code);
            $bank_code = $arr[0];
            $method = str_replace($bank_code . '-', '', $v->code);
            if (!in_array($method, $ignore))
                $paymentMethod[$method][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);
        }

        return $paymentMethod;
    }

    public static function getBanksByType($type)
    {
        $rs = PaymentMethod::find()->where(['status' => 1, 'transaction_type_id' => 1])->all();

        $ignore = [$type];
        $paymentMethod = array();
        foreach ($rs as $k => $v) {
            $arr = explode('-', $v->code);
            $bank_code = $arr[0];
            $method = str_replace($bank_code . '-', '', $v->code);
            if (in_array($method, $ignore))
                $paymentMethod[$method][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);
        }

        return $paymentMethod;
    }

    public static function getBanksPos()
    {
        $rs = PaymentMethod::find()->where(['status' => 1, 'transaction_type_id' => 1])->all();

        $ignore = ["TOKENIZATION", "SWIPE-CARD", "QRCODE_OFFLINE"];
        $paymentMethod = array();
        foreach ($rs as $k => $v) {
            $arr = explode('-', $v->code);
            $bank_code = $arr[0];
            $method = str_replace($bank_code . '-', '', $v->code);
            if (!in_array($method, $ignore)) {
                {
                    if ($method == 'QR-CODE') {
                        if (in_array($bank_code, ['NGANLUONG', 'VIETTELPAY', 'MOMO', 'VINID', 'SMARTPAY', 'WCP'])) {
                            $paymentMethod[$method]['QR-CODE-WALLET'][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                        } else {
                            $paymentMethod[$method]['QR-CODE-BANK'][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                        }
                    } else {
                        $paymentMethod[$method][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                    }

                }
            }

        }

        return $paymentMethod;
    }

    public static function getBanksByTypePos($type)
    {
        $rs = PaymentMethod::find()->where(['status' => 1, 'transaction_type_id' => 1])->all();

        $ignore = [$type];
        $paymentMethod = array();
        foreach ($rs as $k => $v) {
            $arr = explode('-', $v->code);
            $bank_code = $arr[0];
            $method = str_replace($bank_code . '-', '', $v->code);

            if (in_array($method, $ignore))
                if ($type == 'QR-CODE') {
                    if (in_array($bank_code, ['NGANLUONG', 'VIETTELPAY', 'MOMO', 'VINID', 'SMARTPAY', 'WCP'])) {
                        $paymentMethod[$method]['QR-CODE-WALLET'][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                    } else {
                        $paymentMethod[$method]['QR-CODE-BANK'][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                    }
                } else {
                    $paymentMethod[$method][] = array("code" => $bank_code, "logo" => ROOT_URL . 'vi/checkout/bank/ie/' . $bank_code . '.png', "name" => $v->name);

                }

        }

        return $paymentMethod;
    }

    public static function countAmountByKey($data, $key)
    {
        $total = 0;
        foreach ($data as $item) {
            $total += $item[$key];
        }
        return $total;
    }

    private static function getTransactionId($data)
    {
        return array_map(function ($item) {
            return $item['id'];
        }, $data);
    }

    public static function getCheckoutOrderForGetReceiptsTool($description_arr, $time_begin, &$existingDescriptions): array
    {
//        $interval = 86400 * 30;
        $interval = 86400 * 50;
        $checkoutOrders = CheckoutOrder::find()
            ->where(['>=', 'time_created', $time_begin])
            ->andWhere(['<=', 'time_created', $time_begin + $interval])
            ->andWhere(['status' => CheckoutOrder::STATUS_PAID])
            ->andWhere(['or', ['receipt_url' => null], ['receipt_url' => ''], ['<>', 'receipt_url', self::EXPORTED_3C_FLAG]])
            ->andWhere(['IN', 'order_description', $description_arr])
            ->select(['id', 'order_description', 'token_code'])
//                ->createCommand()->getRawSql();
            ->all();

        // Tạo danh sách các order_description đã tìm được từ kết quả truy vấn
        $existingDescriptions = array_column($checkoutOrders, 'order_description');
//        var_dump($existingDescriptions);

        return $checkoutOrders;
    }

    /** clone từ getCheckoutOrderForGetReceiptsTool nhưung dùng cho đơn Evisa */
    public static function getCheckoutOrderForGetReceiptsToolAsOrderCode($order_code_arr, $time_begin, &$existingDescriptions): array
    {
//        $interval = 86400 * 30;
        $interval = 86400 * 50;
        $checkoutOrders = CheckoutOrder::find()
            ->where(['>=', 'time_created', $time_begin])
            ->andWhere(['<=', 'time_created', $time_begin + $interval])
            ->andWhere(['status' => CheckoutOrder::STATUS_PAID])
            ->andWhere(['or', ['receipt_url' => null], ['receipt_url' => ''], ['<>', 'receipt_url', self::EXPORTED_3C_FLAG]])
            ->andWhere(['IN', 'order_code', $order_code_arr])
            ->select(['id', 'order_description', 'token_code', 'order_code'])
//                ->createCommand()->getRawSql();
            ->all();

        // Tạo danh sách các order_description đã tìm được từ kết quả truy vấn
        $existingDescriptions = array_column($checkoutOrders, 'order_code');
//        var_dump($existingDescriptions);

        return $checkoutOrders;
    }

    public static function getInstallmentFee($card_fee_bearer, $amount_order, $sender_flat_fee, $sender_percent_fee, $fee)
    {
        $result = false;
        $amount_fee = 0;
        if(in_array($card_fee_bearer, [1,2,3])){
            $result = true;
            $amount_fee = $amount_order + self::getInstallmentSenderFee($card_fee_bearer, $amount_order, $fee) + $sender_flat_fee + $amount_order * ($sender_percent_fee / 100);
        }

        return [
            'result' => $result,
            'amount_fee' => $amount_fee
        ];
    }

    public static function getInstallmentFeeVer2($card_fee_bearer, $amount_order, $sender_flat_fee, $sender_percent_fee,$fee,$card_owner_fix_fee)
    {
        $result = false;
        $amount_fee = 0;
        if(in_array($card_fee_bearer, [1,2,3])){
            $result = true;
            $amount_fee = $amount_order + self::getInstallmentSenderFee($card_fee_bearer, $amount_order, $fee) + $sender_flat_fee + $amount_order * ($sender_percent_fee / 100) + $card_owner_fix_fee;
        }

        return [
            'result' => $result,
            'amount_fee' => $amount_fee
        ];
    }

    /**
     * Calculate installment fee version 3.
     *
     * @param  $amount_order The order amount.
     * @param  $sender_flat_fee The sender's flat fee.
     * @param  $sender_percent_fee The sender's percentage fee.
     * @param  $card_owner_percent_instalment_fee The card owner's percentage installment fee.
     * @param  $card_owner_fix_instalment_fee The card owner's fixed installment fee.
     * @return array The result and calculated amount fee.
     */
    public static function getInstallmentFeeVer3(
         $amount_order,
         $sender_flat_fee,
         $sender_percent_fee,
         $card_owner_percent_instalment_fee,
         $card_owner_fix_instalment_fee
    ): array {
        // Tính phần trăm phí
        $sender_percent_fee /= 100;
        $instalment_fee = $card_owner_percent_instalment_fee / 100;
        $denominator = 1 - $instalment_fee - $sender_percent_fee;

        if ($denominator <= 0) {
            throw new Exception("Lỗi: Mẫu số bằng hoặc nhỏ hơn 0, không thể tính toán.");
        }

        // Tính phí người gửi
        $sender_fee = ceil(($amount_order * $sender_percent_fee + $sender_flat_fee) / $denominator);

        // Tính phí trả góp của chủ thẻ
        $result_installment_fee = CheckoutOrder::getInstallmentFeeCardOwnerVer3(
            $amount_order,
            $sender_percent_fee * 100, // Chuyển về dạng phần trăm cho đúng kiểu dữ liệu gốc
            $card_owner_fix_instalment_fee,
            $card_owner_percent_instalment_fee
        );

        // Tổng phí
        $amount_fee = $amount_order + $sender_fee + ($result_installment_fee['amount_fee'] ?? 0);

        return [
            'result' => true,
            'amount_fee' => $amount_fee
        ];
    }


    public static function getInstallmentFeeCardOwnerVer3(
        $amount_order,
        $sender_percent_fee,
        $card_owner_fix_instalment_fee,
        $card_owner_percent_instalment_fee
    ): array
    {
        $amount_fee = ceil(($amount_order*$card_owner_percent_instalment_fee/100 + $card_owner_fix_instalment_fee)/(1-$card_owner_percent_instalment_fee/100 - $sender_percent_fee/100));
        return [
            'result' => true,
            'amount_fee' => $amount_fee
        ];
    }

    public static function getInstallmentFeeMerchantVer3(
        $amount_order,
        $sender_flat_fee,
        $sender_percent_fee,
        $merchant_percent_instalment_fee,
        $merchant_fix_instalment_fee,
        $card_owner_fix_instalment_fee,
        $card_owner_percent_instalment_fee
    ): array
    {
        $amount_fee = ceil($merchant_percent_instalment_fee/100*($amount_order+$sender_flat_fee +$card_owner_fix_instalment_fee)/(1-$card_owner_percent_instalment_fee/100 - $sender_percent_fee/100) + $merchant_fix_instalment_fee);
        return [
            'result' => true,
            'amount_fee' => $amount_fee
        ];
    }

    public static function getInstallmentSenderFee($card_fee_bearer, $amount_order, $fee)
    {
        if ($card_fee_bearer == 3) {  //Cả 2 người chịu phí
            $installment_sender_fee = ($amount_order * (($fee / 2) / 100));
        } elseif ($card_fee_bearer == 2) { //Người mua chịu phí
            $installment_sender_fee = ($amount_order * $fee) / 100;
        } else if ($card_fee_bearer == 1) { //Người bán chịu phí
            $installment_sender_fee = 0;
        }
        return $installment_sender_fee;
    }

    public static function isInstallmentByPaymentMethodCode($payment_method_code)
    {
        if (stripos($payment_method_code, 'TRA-GOP')) {
            return true;
        }
        return false;
    }

    public static function getCheckoutOrderForExportReceiptsTool($description_arr, $time_begin, &$existingDescriptions): array
    {
        $interval = 86400 * 30;
        $checkoutOrders = CheckoutOrder::find()
            ->where(['>=', 'time_created', $time_begin])
            ->andWhere(['<=', 'time_created', $time_begin + $interval])
            ->andWhere(['status' => CheckoutOrder::STATUS_PAID])
            ->andWhere(['IN', 'order_description', $description_arr])
//                ->createCommand()->getRawSql();
            ->all();

        // Tạo danh sách các order_description đã tìm được từ kết quả truy vấn
        $existingDescriptions = array_column($checkoutOrders, 'order_description');
//        var_dump($existingDescriptions);

        return $checkoutOrders;
    }

    /**
     * @param $token_code
     * @param $order_code
     * @param $checkout_order_info
     * @return array|bool[]|false[]
     */
    public static function checkTokenCodeAndOrderCode($token_code, $order_code, &$checkout_order_info = null)
    {
        if (preg_match('/^(\d+)-(CO[A-Z0-9]{10})$/', $token_code, $temp)) {
            $checkout_order_id = intval($temp[1]);
            $checkout_order_info = Tables::selectOneDataTable("checkout_order", ["id = :id AND token_code = :token_code ", "id" => $checkout_order_id, "token_code" => $token_code]);
            if ($checkout_order_info != false) {
                // Token code hợp lệ -> check order_code có đúng ko
                if ($order_code !== ''
                    && $checkout_order_info['order_code'] == $order_code) {
                    return [
                        'token_code_result' => true,
                        'order_code_result' => true
                    ];
                }

                return [
                    'token_code_result' => true,
                    'order_code_result' => false
                ];
            }
        }
        return [
            'token_code_result' => false,
            'order_code_result' => false
        ];
    }

    public static function getCheckoutUrlSeamless($version, $token_code, $params = array())
    {
        $payment_method = '';
        $result_url = ROOT_URL . $params['language'] . '/checkout/version_' . str_replace('.', '_', $version) . '/index/' . $token_code;

        // Check các merchant được bật thanh toán không cần chọn phương thức
        if (in_array($params['merchant_site_code'], $GLOBALS['MERCHANT_ON_SEAMLESS'])) {
            if (!empty($params['payment_method_code']) && !empty($params['bank_code'])) {
                $payment_method = self::getPaymentMethod($params);
                $result_url = ROOT_URL . 'vi/checkout/version_' . str_replace('.', '_', $version) . '/request/' . $token_code . '/' . $payment_method;
            }
        }

        return $result_url;
    }
}
