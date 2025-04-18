<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "merchant_installment_fee_online_v2".
 *
 * @property int $id
 * @property string $fee_code
 * @property int $merchant_id
 * @property string|null $merchant_name
 * @property string $bank_code
 * @property string $method
 * @property int $period
 * @property string $fee_bearer
 * @property int|null $card_owner_fixed_fee
 * @property float|null $card_owner_percent_fee
 * @property int|null $merchant_fixed_fee
 * @property float|null $merchant_percent_fee
 * @property string|null $card_owner_fee_display
 * @property string|null $merchant_fee_display
 * @property int $status 0: active; 1:lock; 2:not yet applied; 3: expired
 * @property string|null $import_from_excel
 * @property string|null $applied_from
 * @property string|null $expired_at
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $locked_at
 */
class MerchantInstallmentFeeOnlineV2 extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 0;
    const STATUS_LOCK = 1;
    const STATUS_NOT_YET_APPLIED = 2;
    const STATUS_EXPIRED = 3;

    const STATUS_LABEL = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_LOCK => 'Locked',
        self::STATUS_NOT_YET_APPLIED => 'Not Yet Applied',
        self::STATUS_EXPIRED => 'Expired',
    ];

    const FEE_BEARER_CARD_OWNER = 'CARD_OWNER';
    const FEE_BEARER_MERCHANT = 'MERCHANT';
    const FEE_BEARER_BOTH = 'BOTH';
    const FEE_BEARER = [
        self::FEE_BEARER_BOTH=>'ĐVCNT và Chủ thẻ',
        self::FEE_BEARER_CARD_OWNER=>'Chủ thẻ',
        self::FEE_BEARER_MERCHANT=>'ĐVCNT',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'merchant_installment_fee_online_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'fee_code',
                    'merchant_id',
                    'bank_code',
                    'method',
                    'period',
                    'fee_bearer',
                    'status',
                    'merchant_fixed_fee',
                    'merchant_percent_fee',
                    'card_owner_percent_fee',
                    'card_owner_fixed_fee',
                ],
                'required'
            ],
            [
                [
                    'merchant_id',
                    'period',
                    'card_owner_fixed_fee',
                    'merchant_fixed_fee',
                    'status'
                ],
                'integer'
            ],
            [
                [
                    'card_owner_percent_fee',
                    'merchant_percent_fee',

                ],
                'double'
            ],
            [['applied_from', 'expired_at', 'created_at', 'updated_at', 'deleted_at', 'locked_at'], 'safe'],
            [['fee_code'], 'string', 'max' => 15],
            [['merchant_name', 'card_owner_fee_display', 'merchant_fee_display'], 'string', 'max' => 128],
            [['bank_code'], 'string', 'max' => 12],
            [['method', 'fee_bearer'], 'string', 'max' => 24],
            [['import_from_excel'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fee_code' => 'Mã phí',
            'merchant_id' => 'ID Merchant',
            'merchant_name' => 'Tên Merchant',
            'bank_code' => 'Mã ngân hàng',
            'method' => 'Loại thẻ',
            'period' => 'Kỳ hạn',
            'fee_bearer' => 'Người chịu phí',
            'card_owner_fixed_fee' => 'Phí cố định chủ thẻ(vnđ)',
            'card_owner_percent_fee' => 'Phí % chủ thẻ',
            'merchant_fixed_fee' => 'Phí cố định Merchant',
            'merchant_percent_fee' => 'Phí % Merchant',
            'card_owner_fee_display' => 'Hiển thị phí chủ thẻ',
            'merchant_fee_display' => 'Hiển thị phí Merchant',
            'status' => 'Trạng thái',
            'import_from_excel' => 'Nhập từ Excel',
            'applied_from' => 'Áp dụng từ ngày',
            'expired_at' => 'Ngày hết hạn',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'deleted_at' => 'Ngày xóa',
            'locked_at' => 'Ngày khóa',
        ];
    }

    public function status()
    {
        $data = self::findOne($this->id);
        switch ($data->status) {
            case 1:
                return '<span class="label" style="background-color: #ff0000;">Đang khóa</span>';
            case 0:
                return '<span class="label" style="background-color: #006600;">Đang hoạt động</span>';
            case 2:
                return '<span class="label" style="background-color: #ff9900;">Chưa áp dụng</span>';
            case 3:
                return '<span class="label label-dark">Đã hết hạn</span>';
            default:
                return '<span class="label label-warning">Unknown</span>';
        }
    }

    public function statusForExport($statusRaw = false)
    {
        if (!$statusRaw) {
            $data = self::findOne($this->id);
            $statusRaw = $data->status;
        }
        switch ($statusRaw) {
            case 1:
                return 'Đang khóa';
            case 0:
                return 'Đang hoạt động';
            case 2:
                return 'Chưa áp dụng';
            case 3:
                return 'Hết hạn';
            default:
                return 'Unknown';
        }
    }

    public function feeBearer($fee_bearer_raw = false)
    {
        if (!$fee_bearer_raw) {
            $data = self::findOne($this->id);
            $fee_bearer_raw = $data->status;
        }
        switch ($fee_bearer_raw) {
            case 'MERCHANT':
                return 'Đơn vị chấp nhận thẻ';
            case 'CARD_OWNER':
                return 'Chủ thẻ';
            case 'BOTH':
                return 'ĐVCNT và Chủ thẻ';
            default:
                return 'Unknown';
        }
    }

    public function time()
    {
        $data = self::findOne($this->id);
        $created_at = $data->created_at;
        $updated_at = $data->updated_at;
        $locked_at = $data->locked_at;
        return [$created_at, $updated_at, $locked_at];
    }

    public function merchantDisplay()
    {
        $data = self::findOne($this->id);

        $html = "Merchant ID: $data->merchant_id<br>";
        $html .= "Tên: $data->merchant_name";
        return $html;
    }


}
