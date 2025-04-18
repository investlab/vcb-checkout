<?php

namespace common\models\db;

use common\components\libs\Tables;
use common\components\utils\Translate;
use yii\db\ActiveRecord;

/**
 * @property mixed|null $merchant_id
 * @property mixed|null $card_number
 * @property int|mixed|null $time_created
 * @property int|mixed|null $status
 * @property mixed|null $balance
 * @property mixed|null $user_created
 * @property false|int|mixed|null $time_expired
 * @property string|null $import_from_excel
 * @property mixed|null $id
 * @property mixed|null $user_active
 * @property int|mixed|null $time_active
 * @property int $time_updated
 * @property array|mixed|string|string[]|null $balance_freezing
 */
class CardVoucher extends MyActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_LOCK = 3;
    const STATUS_EXPIRED = 4;

    public $import_from_excel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card_voucher';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'card_number', 'balance', 'time_expired', 'status'], 'required'],
            [['merchant_id', 'status', 'user_created', 'user_updated', 'balance', 'time_created', 'time_updated', 'time_active', 'time_expired'], 'integer'],
            [['card_number'], 'unique'],
            [['import_from_excel'], 'safe'],
            [['import_from_excel'], 'file', 'extensions' => 'xlsx,xls',
                'mimeTypes' => [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ],
                'wrongMimeType' => \Yii::t('app', 'Chỉ chấp nhận file .xlsx .xls'),
                'checkExtensionByMimeType' => false,
                'skipOnEmpty' => true]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant',
            'card_number' => 'Card Number',
            'balance' => 'Số dư',
            'expired' => 'Expired',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'import_from_excel' => 'Mời chọn file tải lên',
        ];
    }

    public static function getListStatus()
    {
        return array(
            self::STATUS_NEW => Translate::get('Mới tạo'),
            self::STATUS_ACTIVE => Translate::get('Đã kích hoạt'),
            self::STATUS_LOCK => Translate::get('Khoá'),
            self::STATUS_EXPIRED => Translate::get('Hết hạn'),
        );
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->time_created = time();
        } else {
            $this->time_updated = time();
        }
        return parent::beforeSave($insert);
    }

    public static function getStatus($status)
    {
        $list_status = self::getListStatus();
        if (array_key_exists($status, $list_status)) {
            return $list_status[$status];
        } else {
            return Translate::get("Không rõ");
        }
    }

    public static function getOperators()
    {
        return array(
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
            'active' => array('title' => 'Kích hoạt thẻ', 'confirm' => true),
            'top-up' => array('title' => 'Nạp tiền vào thẻ', 'confirm' => false),
            'lock' => array('title' => 'Khóa thẻ', 'confirm' => true),
            'add' => array('title' => 'Thêm mới thủ công', 'confirm' => false, 'check-all' => true),
            'import' => array('title' => 'Import', 'confirm' => false, 'check-all' => true),
            'import-top-up' => array('title' => 'Import tiền thẻ', 'confirm' => false, 'check-all' => true),
            'withdraw' => array('title' => 'Rút tiền về merchant', 'confirm' => false),
        );
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['merchant_id' => 'id'])->via("merchant");
    }

    public function getMerchant()
    {
        return $this->hasOne(Merchant::className(), ['id' => 'merchant_id']);
    }

    public function getRequirement()
    {
        return $this->hasMany(CardVoucherRequirement::className(), ['card_voucher_id' => 'id'])
            ->where(['status' => CardVoucherRequirement::STATUS_NEW])
            ->orderBy('id');
    }

    public function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_NEW:
                $result['active'] = $operators['active'];
                $result['top-up'] = $operators['top-up'];
                break;
            case self::STATUS_ACTIVE:
                $result['lock'] = $operators['lock'];
                $result['top-up'] = $operators['top-up'];
                $result['withdraw'] = $operators['withdraw'];
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                break;
            case self::STATUS_EXPIRED:
                $result['withdraw'] = $operators['withdraw'];
                break;
        }
        return self::getOperatorsForUser($row, $result);
    }


    public function getActionByStatus()
    {
        return self::getOperatorsByStatus($this);
    }

}