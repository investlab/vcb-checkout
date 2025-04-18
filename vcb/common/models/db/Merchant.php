<?php

namespace common\models\db;

use common\components\libs\Weblib;
use common\components\utils\Translate;
use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "merchant".
 *
 * @property integer $id
 * @property string $name
 * @property string $merchant_code
 * @property integer $partner_id
 * @property string $password
 * @property string $logo
 * @property string $website
 * @property integer $branch_id
 * @property integer $status
 * @property string $email_notification
 * @property string $mobile_notification
 * @property string $url_notification
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 * @property integer $active3D
 * @property integer $payment_flow
 */
class Merchant extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const ST_ACTIVE = 0;
    const ST_LOCK = 1;
    const CALLBACK_FAILURE_STATUS_ENABLE = 1;
    const CALLBACK_FAILURE_STATUS_DISABLE = 0;
    const MERCHANT_ON_SEAMLESS_ENABLE = 1;
    const MERCHANT_ON_SEAMLESS_DISABLE = 0;

    const MERCHANT_PARTNER_SWITCH_AS_AMOUNT_ON = 1;
    const MERCHANT_PARTNER_SWITCH_AS_AMOUNT_OFF = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'merchant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'partner_id', 'status'], 'required'],
            [['partner_id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated', 'branch_id', 'active3D', 'payment_flow'], 'integer'],
            [['name', 'logo', 'website', 'email_notification', 'url_notification'], 'string', 'max' => 255],
            [['password'], 'string', 'max' => 50],
            [['mobile_notification'], 'string', 'max' => 20],
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
            'partner_id' => 'Partner ID',
            'password' => 'Password',
            'logo' => 'Logo',
            'website' => 'Website',
            'status' => 'Status',
            'email_notification' => 'Email Notification',
            'mobile_notification' => 'Mobile Notification',
            'url_notification' => 'URL Notification',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'active3D' => 'Active 3D',
            'payment_flow' => 'Payment Flow'
        ];
    }


    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Kích hoạt',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getActive3D()
    {
        return array(
            self::ST_ACTIVE => 'Bật',
            self::ST_LOCK => 'Tắt'
        );
    }

    public static function getPaymentFlow()
    {
        return array(
            self::ST_ACTIVE => '3Ds',
            self::ST_LOCK => '3Ds2'
        );
    }


    public static function getOperators()
    {
        return array(
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
            'active' => array('title' => 'Mở khóa merchant', 'confirm' => true),
            'lock' => array('title' => 'Khóa merchant', 'confirm' => true),
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
            'change-password' => array('title' => 'Đổi mật khẩu', 'confirm' => false),
            'view-installment' => array('title' => 'Cấu hình trả góp', 'confirm' => true),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['view-update'] = $operators['view-update'];
                $result['lock'] = $operators['lock'];
                $result['change-password'] = $operators['change-password'];
                $result['view-installment'] = $operators['view-installment'];
                if ($row['active3D'] == 0) {
                    $result['token-secure'] = array('title' => 'Tắt Token 3D-Secure', 'confirm' => true);
                } else {
                    $result['token-secure'] = array('title' => 'Bật Token 3D-Secure', 'confirm' => true);
                }
                if ($row['payment_flow'] == 0) {
                    $result['payment-flow'] = array('title' => 'Chọn luồng thanh toán 3Ds2', 'confirm' => true);
                } else {
                    $result['payment-flow'] = array('title' => 'Chọn luồng thanh toán 3Ds', 'confirm' => true);
                }
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function getApiKey($merchant_id, &$merchant_info = false)
    {
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id AND status = :status ", "id" => $merchant_id, "status" => Merchant::STATUS_ACTIVE]);
        if ($merchant_info != false) {
            return $merchant_info['password'];
        }
        return false;
    }


    public static function setRow(&$row)
    {
        $row['operators'] = self::getOperatorsByStatus($row);
        User::setUsernameForRow($row);
        return $row;
    }

    public static function setRows(&$rows)
    {
        foreach ($rows as $merchant_id => $row) {
            $rows[$merchant_id]['operators'] = Merchant::getOperatorsByStatus($row);
        }
        User::setUsernameForRows($rows);
        return $rows;
    }

    public static function getPartnerIdByMerchantId($merchant_id, &$merchant_info = false)
    {
        $merchant_info = Tables::selectOneDataTable("merchant", ["id = :id", "id" => $merchant_id]);
        if ($merchant_info != false) {
            return $merchant_info['partner_id'];
        }
        return false;
    }

    public static function hasViewFeeFree($merchant_info)
    {
        return false;
    }

    public static function getMerchantByBranchId($branch_id)
    {
        $merchant_arr = [['id' => 0, 'name' => Translate::get('Chọn merchant')]];
        $merchants = Merchant::findAll(['branch_id' => $branch_id, 'status' => self::STATUS_ACTIVE]);

        if (!empty($merchants)) {
            foreach ($merchants as $key => $merchant) {
                $merchant_arr[] = [
                    'id' => $merchant['id'],
                    'name' => $merchant['name']
                ];
            }
        }

        return $merchant_arr;
    }

    public static function getById($merchant_id)
    {
        $merchant_info = Merchant::find()
            ->where(['id' => $merchant_id])
            ->andWhere(['status' => self::STATUS_ACTIVE])
            ->asArray()
            ->one();
        if (!is_null($merchant_info)) {
            return $merchant_info;
        }
        return false;
    }

    public static function getPaymentFlowById($id)
    {
        $merchant_info = Merchant::findOne(['id' => $id]);
        if (!is_null($merchant_info)) {
            return $merchant_info['payment_flow'] == Merchant::ST_LOCK;
        }
    }

    public static function getNameById($id)
    {
        $merchant = Merchant::findOne(['id' => $id]);
        if (!is_null($merchant)) {
            return @$merchant->name;
        }
    }

    public function getAccount(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Account::className(), ['merchant_id' => 'id']);
    }

}
