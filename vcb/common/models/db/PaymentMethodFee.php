<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\models\db\PartnerPaymentMethod;

/**
 * This is the model class for table "payment_method_fee".
 *
 * @property integer $id
 * @property integer $payment_method_id
 * @property string $percentage_fee
 * @property double $flat_fee
 * @property double $payer_flat_fee
 * @property string $payer_percentage_fee
 * @property integer $time_begin
 * @property integer $time_end
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $time_active
 * @property integer $time_reject
 * @property integer $time_lock
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $user_active
 * @property integer $user_reject
 * @property integer $user_lock
 * @property integer $time_request
 * @property integer $user_request
 */
class PaymentMethodFee extends MyActiveRecord
{
    const STATUS_NOT_REQUEST = 1;
    const STATUS_REQUEST = 2;
    const STATUS_REJECT = 3;
    const STATUS_ACTIVE = 4;
    const STATUS_LOCK = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_method_fee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_method_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['payment_method_id', 'time_begin', 'time_end',
                'status', 'time_created', 'time_updated',
                'time_active', 'time_reject', 'time_lock',
                'user_created', 'user_updated', 'user_active',
                'user_reject', 'user_lock', 'time_request', 'user_request'], 'integer'],
            [['percentage_fee', 'flat_fee', 'payer_flat_fee', 'payer_percentage_fee'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {

        return [
            'id' => 'ID',
            'payment_method_id' => 'Payment Method ID',
            'percentage_fee' => 'Phí phần trăm',
            'flat_fee' => 'Phí cố định',
            'payer_flat_fee' => 'payer_flat_fee',
            'payer_percentage_fee' => 'payer_percentage_fee',
            'time_begin' => 'Thời gian áp dụng',
            'time_end' => 'Thời gian kết thúc',
            'status' => 'Trạng thái',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'time_active' => 'Time Active',
            'time_reject' => 'Time Reject',
            'time_lock' => 'Time Lock',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_active' => 'User Active',
            'user_reject' => 'User Reject',
            'user_lock' => 'User Lock',
            'time_request' => 'Time Request',
            'user_request' => 'User Request',
        ];
    }

    public static function getFee($payment_method_id, $amount, $time_payment, &$payment_method_fee_info = null, &$partner_payment_id = null)
    {
        $payment_method_fee_info = Tables::selectOneDataTable("payment_method_fee", "payment_method_id = $payment_method_id AND time_begin <= $time_payment AND (time_end = 0 OR time_end IS NULL OR time_end > $time_payment) AND status = " . self::STATUS_ACTIVE, "time_begin DESC, id DESC ");
        if ($payment_method_fee_info != false) {
            $partner_payment_method_info = Tables::selectOneDataTable("partner_payment_method", "payment_method_id = $payment_method_id AND status = " . PartnerPaymentMethod::STATUS_ACTIVE, "position ASC, id DESC ");
            if ($partner_payment_method_info != false) {
                $partner_payment_id = $partner_payment_method_info['partner_payment_id'];
                return ceil($amount * $payment_method_fee_info['payer_percentage_fee'] / 100 + $payment_method_fee_info['payer_flat_fee']);
            }
        }
        return false;
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NOT_REQUEST => 'Chưa gửi yêu cầu',
            self::STATUS_REQUEST => 'Gửi yêu cầu',
            self::STATUS_REJECT => 'Từ chối',
            self::STATUS_ACTIVE => 'Kích hoạt',
            self::STATUS_LOCK => 'Khóa'
        );
    }
}
