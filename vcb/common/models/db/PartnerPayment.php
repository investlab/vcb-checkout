<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "partner_payment".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerPayment extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'status'], 'required'],
            [['description'], 'string'],
            [['status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'status' => 'Status',
            'token_key' => 'Token key',
            'checksum_key' => 'Checksum key',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getIdByCode($code)
    {
        $partner_payment_info = Tables::selectOneDataTable("partner_payment", ["code = :code", "code" => $code]);
        if ($partner_payment_info != false) {
            return $partner_payment_info['id'];
        }
        return false;
    }
    
    public static function getIdActiveByCode($code)
    {
        $partner_payment_info = Tables::selectOneDataTable("partner_payment", [
            "code = :code and status = :status", 
            "code" => $code,
            "status" => self::STATUS_ACTIVE
        ]);
        if ($partner_payment_info != false) {
            return $partner_payment_info['id'];
        }
        return false;
    }
    
    public static function getById($id) {
        $partner_payment = PartnerPayment::find()
                ->where(['id' => $id])
                ->andWhere(['status' => self::STATUS_ACTIVE])
                ->asArray()
                ->one();
        if (!is_null($partner_payment)) {
            return $partner_payment;
        }
        return false;
    }

    public static function getProviderBankQr($partner_payment_code)
    {
        if (in_array($partner_payment_code, ['ZALO-PAY'])) {
            return 'ZALO';
        } elseif (in_array($partner_payment_code, ['VCB-VA', 'BIDV-VA	', 'VCCB-VA', 'MSB-VA'])) {
            return 'VIETQR';
        } elseif (in_array($partner_payment_code, ['NGANLUONG-SEAMLESS', 'VCB'])) {
            return 'VNPAY';
        } else{
            return 'UNKNOWN_BANK_QR';
        }
    }
}
