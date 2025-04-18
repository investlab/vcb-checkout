<?php


namespace common\models\db;

use Yii;
use common\components\libs\Tables;

class InstallmentConfig extends MyActiveRecord
{
    public static function tableName()
    {
        return 'installment_config';
    }

    public function rules()
    {
        return [
            [['merchant_id', 'card_accept', 'cycle_accept'], 'required'],
            ['merchant_id', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant id',
            'card_accept' => 'Card Accept',
            'cycle_accept' => 'Cycle Accept',
            'status' => 'status'
        ];
    }

    public static function getOperators($status)
    {
        if ($status == ACTIVE_STATUS) {
            return array(
                'view-installment' => array('title' => 'Cấu hình trả góp Merchant', 'confirm' => true),
                'lock-installment' => array('title' => 'Khoá cấu hình trả góp', 'confirm' => true),
            );
        } else {
            return array(
                'active-installment' => array('title' => 'Mở khoá cấu hình trả góp', 'confirm' => true),
            );
        }
    }
}