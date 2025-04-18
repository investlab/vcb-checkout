<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "otp_transaction".
 *
 * @property integer $id
 * @property integer $transaction_id
 * @property integer $customer_id
 * @property string $code
 * @property string $mobile
 * @property integer $time_limit
 * @property integer $number
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class OtpTransaction extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'otp_transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_id', 'customer_id', 'code', 'mobile', 'time_limit', 'number', 'status'], 'required'],
            [['transaction_id', 'customer_id', 'time_limit', 'number', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['code'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
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
            'transaction_id' => 'Transaction ID',
            'customer_id' => 'Customer ID',
            'code' => 'Code',
            'mobile' => 'Mobile',
            'time_limit' => 'Time Limit',
            'number' => 'Number',
            'status' => 'Status',
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

    public static function encryptOTP($otp)
    {
        return $otp;
        //return md5($otp);
    }

    public static function checkOTP($otp, $transaction_id, $time_verify, &$otp_transaction_info = false)
    {
        $otp_encrypt = self::encryptOTP($otp);
        $otp_transaction_info = Tables::selectOneDataTable("otp_transaction", "transaction_id = $transaction_id AND code = '$otp_encrypt' AND status = " . OtpTransaction::STATUS_ACTIVE . " ");
        if ($otp_transaction_info != false && $otp_transaction_info['time_limit'] > $time_verify) {
            return true;
        }
        return false;
    }
}
