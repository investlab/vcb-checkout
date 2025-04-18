<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;
use yii\base\Exception;

/**
 * This is the model class for table "payment_transaction".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $checkout_order_id
 * @property integer $type
 * @property integer $bill_id
 * @property integer $invoice_id
 * @property string $invoice_code
 * @property integer $payment_method_id
 * @property double $payment_method_fee
 * @property string $bank_receipt
 * @property string $partner_payment_method_receipt
 * @property integer $partner_payment_id
 * @property double $partner_payment_method_fee
 * @property double $amount
 * @property string $currency
 * @property string $payer_fullname
 * @property string $payer_email
 * @property string $payer_mobile
 * @property string $payer_address
 * @property string $payer_zone_id
 * @property string $payer_id_number
 * @property string $payer_id_type
 * @property integer $time_limit
 * @property string $reason
 * @property integer $reason_id
 * @property integer $related_payment_transaction_id
 * @property integer $installment_bank_id
 * @property integer $installment_period
 * @property double $installment_amount
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_paid
 * @property integer $time_cancel
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_paid
 * @property integer $user_cancel
 */
class PaymentTransaction extends MyActiveRecord
{

    const STATUS_NOT_PAYMENT = 1;
    const STATUS_PAYING = 2;
    const STATUS_CANCEL = 3;
    const STATUS_PAID = 4;
    const STATUS_WAIT_REFUND = 5;
    const STATUS_REFUND = 6;
    const STATUS_REVIEW = 7;
    const STATUS_VERIFY = 8;

    const TYPE_PAYMENT = 1;
    const TYPE_REFUND = 2;
    const TYPE_PAYMENT_INVOICE = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'checkout_order_id', 'type', 'bill_id', 'invoice_id', 'payment_method_id', 'partner_payment_id', 'payer_zone_id', 'payer_id_number', 'payer_id_type', 'time_limit', 'reason_id', 'related_payment_transaction_id', 'installment_bank_id', 'installment_period', 'status', 'time_created', 'time_updated', 'time_paid', 'time_cancel', 'user_created', 'user_updated', 'user_paid', 'user_cancel'], 'integer'],
            [['type', 'payment_method_id', 'amount', 'currency', 'time_limit', 'status'], 'required'],
            [['payment_method_fee', 'partner_payment_method_fee', 'amount', 'installment_amount'], 'number'],
            [['payer_address', 'reason'], 'string'],
            [['invoice_code', 'partner_payment_method_receipt', 'bank_receipt'], 'string', 'max' => 50],
            [['currency'], 'string', 'max' => 10],
            [['payer_fullname', 'payer_email'], 'string', 'max' => 255],
            [['payer_mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'merchant_id',
            'checkout_order_id' => 'checkout_order_id',
            'type' => 'Type',
            'bill_id' => 'Bill ID',
            'invoice_id' => 'Invoice ID',
            'invoice_code' => 'Invoice Code',
            'payment_method_id' => 'Payment Method ID',
            'payment_method_fee' => 'Payment Method Fee',
            'bank_receipt' => 'bank_receipt',
            'partner_payment_method_receipt' => 'Partner Payment Method Receipt',
            'partner_payment_id' => 'Partner Payment ID',
            'partner_payment_method_fee' => 'Partner Payment Method Fee',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'payer_fullname' => 'Payer Fullname',
            'payer_email' => 'Payer Email',
            'payer_mobile' => 'Payer Mobile',
            'payer_address' => 'Payer Address',
            'payer_zone_id' => 'payer_zone_id',
            'payer_id_number' => 'payer_id_number',
            'payer_id_type' => 'payer_id_type',
            'time_limit' => 'Time Limit',
            'reason' => 'Reason',
            'reason_id' => 'Reason ID',
            'related_payment_transaction_id' => 'related_payment_transaction_id',
            'installment_bank_id' => 'Installment Bank ID',
            'installment_period' => 'Installment Period',
            'installment_amount' => 'Installment Amount',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_paid' => 'Time Paid',
            'time_cancel' => 'Time Cancel',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_paid' => 'User Paid',
            'user_cancel' => 'User Cancel',
        ];
    }

    public function beforeValidate()
    {
        $this->partner_payment_method_receipt = trim($this->partner_payment_method_receipt);
        return parent::beforeValidate();
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NOT_PAYMENT => 'Chưa thanh toán',
            self::STATUS_PAYING => 'Đang thanh toán',
            self::STATUS_CANCEL => 'Đã hủy',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_WAIT_REFUND => 'Đợi hoàn tiền',
            self::STATUS_REFUND => 'Đã hoàn tiền',
            self::STATUS_VERIFY => 'Đã xác nhận',
        );
    }

    public static function getType()
    {
        return array(
            self::TYPE_PAYMENT => 'Giao dịch thanh toán cho đơn hàng',
            self::TYPE_REFUND => 'Giao dịch hoàn tiền',
            self::TYPE_PAYMENT_INVOICE => 'Giao dịch thanh toán cho hóa đơn',
        );
    }

    public static function getListNotPaidByBillId($bill_id)
    {
        $payment_transaction_info = Tables::selectAllDataTable("payment_transaction", "bill_id = $bill_id AND status IN (" . self::STATUS_NOT_PAYMENT . "," . self::STATUS_PAYING . ")", "", "id");
        if ($payment_transaction_info != false) {
            return self::setRows($payment_transaction_info);
        }
        return false;
    }

    public static function getListNotConvertInstallmentByBillId($bill_id)
    {
        $payment_transaction_info = Tables::selectAllDataTable("payment_transaction", "id NOT IN (SELECT payment_transaction_id FROM installment_transaction WHERE bill_id = $bill_id) AND bill_id = $bill_id AND status = " . self::STATUS_PAID . " AND amount >= 3000000 ", "", "id");
        if ($payment_transaction_info != false) {
            return self::setRows($payment_transaction_info);
        }
        return false;
    }

    public static function setRow(&$row)
    {
        $payment_method_info = Tables::selectOneDataTable("payment_method", "id = " . intval($row['payment_method_id']));
        $row['payment_method_info'] = @$payment_method_info;
        $row['info'] = 'Mã GD ' . $row['id'] . ' - ' . ObjInput::makeCurrency($row['amount']) . $row['currency'] . ' - ' . @$payment_method_info['name'];
        $row['card_number'] = self::getCardNumber($row);
        $status = self::getStatus();
        $row['status_name'] = $status[$row['status']];
        $row['operators'] = self::getOperatorsByStatus($row);
        return $row;
    }

    public static function getCardNumber($row)
    {
        try {
            if (trim($row['partner_payment_info']) != '') {
                $payment_info = json_decode($row['partner_payment_info'], true);

                if (isset($payment_info['card_number']) && !empty($payment_info['card_number'])) {
                    return $payment_info['card_number'];
                }
            }
        } catch (Exception $ex) {

        }
        return '';
    }

    public static function setRows(&$rows)
    {
        $payment_method_ids = array();
        $partner_payment_ids = array();
        $bill_ids = array();
        $checkout_order_ids = array();
        $merchant_ids = array();
        foreach ($rows as $row) {
            $payment_method_ids[$row['payment_method_id']] = intval($row['payment_method_id']);
            $partner_payment_ids[$row['partner_payment_id']] = intval($row['partner_payment_id']);
            $bill_ids[$row['bill_id']] = intval($row['bill_id']);
            $checkout_order_ids[$row['checkout_order_id']] = intval($row['checkout_order_id']);
            $merchant_ids[$row['merchant_id']] = intval($row['merchant_id']);
        }
        $payment_method_info = Tables::selectAllDataTable("payment_method", "id IN (" . implode(',', $payment_method_ids) . ")", "", "id");
        $partner_payment_info = Tables::selectAllDataTable("partner_payment", "id IN (" . implode(',', $partner_payment_ids) . ")", "", "id");
        $bill_info = Tables::selectAllDataTable("bill", "id IN (" . implode(',', $bill_ids) . ")", "", "id");
        $checkout_order_info = Tables::selectAllDataTable("checkout_order", "id IN (" . implode(',', $checkout_order_ids) . ")", "", "id");
        $merchant_info = Tables::selectAllDataTable("merchant", "id IN (" . implode(',', $merchant_ids) . ")", "", "id");
        $status = self::getStatus();
        foreach ($rows as $key => $row) {
            $rows[$key] = $row;
            $rows[$key]['card_number'] = self::getCardNumber($row);
            $rows[$key]['status_name'] = $status[$row['status']];
            $rows[$key]['payment_method_info'] = @$payment_method_info[$row['payment_method_id']];
            $rows[$key]['partner_payment'] = @$partner_payment_info[$row['partner_payment_id']];
            $rows[$key]['bill_info'] = @$bill_info[$row['bill_id']];
            $rows[$key]['checkout_order_info'] = @$checkout_order_info[$row['checkout_order_id']];
            $rows[$key]['merchant_info'] = @$merchant_info[$row['merchant_id']];
            $rows[$key]['info'] = 'Mã GD ' . $row['id'] . ' - ' . ObjInput::makeCurrency($row['amount']) . $row['currency'] . ' - ' . @$payment_method_info[$row['payment_method_id']]['name'];
            $rows[$key]['operators'] = self::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function encryptPartnerPaymentInfo($partner_payment_info, $bin_code, $card_type, $request_token)
    {
        if (trim($partner_payment_info) != '') {
            $partner_payment_info = json_decode($partner_payment_info, true);
        } else {
            $partner_payment_info = array();
        }
        $partner_payment_info['bin_code'] = $bin_code;
        $partner_payment_info['card_type'] = $card_type;
        $partner_payment_info['request_token'] = $request_token;
        return json_encode($partner_payment_info);
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_NOT_PAYMENT:
                $result['update-paid'] = $operators['update-paid'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
            case self::STATUS_PAYING:
                $result['update-paid'] = $operators['update-paid'];
                $result['update-receipt'] = $operators['update-receipt'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
            case self::STATUS_VERIFY:
                $result['update-paid'] = $operators['update-paid'];
                $result['update-receipt'] = $operators['update-receipt'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
            case self::STATUS_CANCEL:
                break;
            case self::STATUS_PAID:
                $result['refund'] = $operators['refund'];
                $result['update-receipt'] = $operators['update-receipt'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
            case self::STATUS_WAIT_REFUND:
                $result['update-receipt'] = $operators['update-receipt'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
            case self::STATUS_REFUND:
                $result['update-receipt'] = $operators['update-receipt'];
                $result['update-user-created'] = $operators['update-user-created'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function getOperators()
    {
        return array(
            'refund' => array('title' => 'Hoàn tiền', 'confirm' => false),
            'update-paid' => array('title' => 'Cập nhật thanh toán', 'confirm' => false),
            'update-receipt' => array('title' => 'Cập nhật mã tham chiếu', 'confirm' => false),
            'update-user-created' => array('title' => 'Thay đổi người tạo', 'confirm' => false),
        );
    }

    public static function getRefundAmount($payment_transaction_id)
    {
        $payment_transaction_info = Tables::selectOneDataTable("payment_transaction", "id = " . $payment_transaction_id . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_REFUND . ") ");
        if ($payment_transaction_info != false) {
            if ($payment_transaction_info['status'] == PaymentTransaction::STATUS_PAID) {
                return $payment_transaction_info['amount'];
            } else {
                $refund_transaction_info = Tables::selectAllDataTable("payment_transaction", "related_payment_transaction_id = " . $payment_transaction_id . " AND type = " . PaymentTransaction::TYPE_REFUND . " AND status = " . PaymentTransaction::STATUS_PAID . " ", "", "id");
                if ($refund_transaction_info != false) {
                    $refund_amount = 0;
                    foreach ($refund_transaction_info as $row) {
                        $refund_amount += $row['amount'];
                    }
                    return $payment_transaction_info['amount'] - $refund_amount;
                }
            }
        }
        return 0;
    }

    public static function getPaymentTransactionIsRefundBySaleOrderID($sale_order_id)
    {
        $result = array();
        $payment_transaction_info = Tables::selectAllDataTable("payment_transaction", "bill_id = " . intval($sale_order_id) . " AND type = " . PaymentTransaction::TYPE_PAYMENT . " AND status IN (" . PaymentTransaction::STATUS_PAID . "," . PaymentTransaction::STATUS_REFUND . ")", "", "id");
        if ($payment_transaction_info != false) {
            $check_refund_ids = array();
            foreach ($payment_transaction_info as $row) {
                $row['refund_amount'] = 0;
                $result[$row['id']] = $row;
                if ($row['status'] == PaymentTransaction::STATUS_REFUND) {
                    $check_refund_ids[] = $row['id'];
                }
            }
            if (!empty($check_refund_ids)) {
                $refund_transaction_info = Tables::selectAllDataTable("payment_transaction", "bill_id = " . intval($sale_order_id) . " AND related_payment_transaction_id IN (" . implode(',', $check_refund_ids) . ") AND type = " . PaymentTransaction::TYPE_REFUND . " AND status = " . PaymentTransaction::STATUS_PAID . " ", "", "id");
                if ($refund_transaction_info != false) {
                    $refund_amount = array();
                    foreach ($refund_transaction_info as $row) {
                        if (!isset($refund_amount[$row['related_payment_transaction_id']])) {
                            $refund_amount[$row['related_payment_transaction_id']] = $row['amount'];
                        } else {
                            $refund_amount[$row['related_payment_transaction_id']] += $row['amount'];
                        }
                    }
                    foreach ($result as $row) {
                        if ($row['status'] == PaymentTransaction::STATUS_REFUND) {
                            if ($result[$row['id']]['amount'] == $refund_amount[$row['id']]) {
                                unset($result[$row['id']]);
                            } else {
                                $result[$row['id']]['refund_amount'] = $refund_amount[$row['id']];
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

}
