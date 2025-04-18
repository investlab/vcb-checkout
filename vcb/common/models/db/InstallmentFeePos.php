<?php

namespace common\models\db;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "merchant_installment_fee_v2".
 *
 * @property int $id
 * @property string $fee_code
 * @property int $merchant_id
 * @property int $merchant_name
 * @property string $bank_code
 * @property string $method
 * @property int $period
 * @property string $fee_bearer
 * @property int $card_owner_fixed_fee
 * @property int $card_owner_percent_fee
 * @property string $card_owner_fee_display
 * @property int $merchant_fixed_fee
 * @property int $merchant_percent_fee
 * @property string $merchant_fee_display
 * @property int $status
 * @property string|null $applied_from
 * @property string|null $expired_at
 * @property string $created_at
 * @property string $locked_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $import_from_excel
 * @property int $user_updated
 */
class InstallmentFeePos extends \yii\db\ActiveRecord
{

    const STATUS_LOCK = 1;
    const STATUS_PENDING = 2;
    const STATUS_ACTIVE = 0;
    const STATUS_EXPIRED = 3;

    const STATUS = [
        self::STATUS_LOCK => 'Đang khóa',
        self::STATUS_PENDING => 'Chưa áp dụng',
        self::STATUS_ACTIVE => 'Đang hoạt động',
        self::STATUS_EXPIRED => 'Hết hạn',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'installment_fee_pos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fee_code', 'merchant_id', 'status', 'bank_code', 'method', 'period', 'fee_bearer', 'expired_at'], 'required'],
            [['card_owner_percent_fee', 'merchant_percent_fee', 'card_owner_fixed_fee', 'merchant_fixed_fee'], 'required'],
            [['period', 'status', 'card_owner_fixed_fee', 'merchant_fixed_fee'], 'integer'],
            [['card_owner_percent_fee', 'merchant_percent_fee', 'card_owner_fixed_fee', 'merchant_fixed_fee'], 'number', 'numberPattern' => '/^\s*[+]?\d+(?:[.,]\d{1,6})?\s*$/'],
            [['card_owner_percent_fee', 'merchant_percent_fee'], 'number', 'min' => 0, 'max' => 100],
            [['import_from_excel', 'card_owner_fee_display', 'merchant_name', 'merchant_fee_display', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            ['fee_code', 'unique'],
            [['bank_code', 'method', 'fee_bearer'], 'string', 'max' => 12],
            [['import_from_excel'], 'file', 'extensions' => 'xlsx,xls',
                'mimeTypes' => [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'wrongMimeType' => \Yii::t('app', 'Chỉ chấp nhận file .xlsx .xls'),
                'checkExtensionByMimeType' => false,
                'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fee_code' => 'Mã phí',
            'merchant_name' => 'Tên Merchant',
            'merchant_id' => 'Merchant',
            'bank_code' => 'Ngân hàng',
            'method' => 'Loại thẻ',
            'period' => 'Kỳ hạn(tháng)',
            'fee_bearer' => 'Đối tượng chịu phí',
            'card_owner_fixed_fee' => 'Phí cố định chủ thẻ(vnđ)',
            'card_owner_percent_fee' => 'Phí % chủ thẻ(%)',
            'merchant_fixed_fee' => 'Phí cố định Merchant(vnđ)',
            'merchant_percent_fee' => 'Phí % Merchant',
            'card_owner_fee_display' => 'Phí Chủ thẻ chịu',
            'merchant_fee_display' => 'Phí Merchant chịu',
            'status' => 'Trạng thái',
            'import_from_excel' => 'Mời chọn file tải lên',
            'applied_from' => 'Ngày áp dụng',
            'expired_at' => 'Ngày hết hạn',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'deleted_at' => 'Ngày xóa',
            'user_updated' => 'Người cập nhật'
        ];
    }


    /**
     * @return string
     */
    public function merchantDisplay()
    {
        $data = InstallmentFeePos::findOne($this->id);

        $html = "Merchant ID: $data->merchant_id<br>";
        $html .= "Tên: $data->merchant_name";
        return $html;
    }

    /**
     * @return string
     */
    public function feeBearer()
    {
        $data = InstallmentFeePos::findOne($this->id);
        switch ($data->fee_bearer) {
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

    public function status()
    {
        $data = InstallmentFeePos::findOne($this->id);
        switch ($data->status) {
            case self::STATUS_LOCK:
                return '<span class="label" style="background-color: #ff0000;">Đang khóa</span>';
            case self::STATUS_ACTIVE:
                return '<span class="label" style="background-color: #006600;">Đang hoạt động</span>';
            case self::STATUS_PENDING:
                return '<span class="label" style="background-color: #ff9900;">Chưa áp dụng</span>';
            case self::STATUS_EXPIRED:
                return '<span class="label label-dark">Đã hết hạn</span>';
            default:
                return '<span class="label label-warning">Unknown</span>';
        }
    }

    public function time()
    {
        $data = InstallmentFeePos::findOne($this->id);
        $created_at = $data->created_at;
        $updated_at = $data->updated_at;
        $locked_at = $data->locked_at;
        return [$created_at, $updated_at, $locked_at];
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Cấu hình phí trả góp POS';
    }


}
