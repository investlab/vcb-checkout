<?php

namespace common\models\db;

use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "partner_payment_method".
 *
 * @property integer $id
 * @property integer $partner_payment_id
 * @property string $partner_payment_code
 * @property integer $payment_method_id
 * @property string $enviroment
 * @property integer $position
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class PartnerPaymentMethod extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_payment_method';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enviroment', 'position'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['partner_payment_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['position'], 'number', 'min' => 1, 'tooSmall' => '{attribute} không hợp lệ.'],
            [['partner_payment_id', 'payment_method_id', 'position', 'status',
                'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['partner_payment_code'], 'string', 'max' => 50],
            [['enviroment'], 'string', 'max' => 255],
            [['enviroment'], 'validateForm']
//            [['partner_payment_id', 'payment_method_id', 'enviroment'], 'unique',
//                'targetAttribute' => ['partner_payment_id', 'payment_method_id', 'enviroment'], 'message' => 'Đã tồn tại kênh thanh toán hỗ trợ.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_payment_id' => 'Kênh thanh toán hỗ trợ',
            'partner_payment_code' => 'Mã kênh thanh toán hỗ trợ',
            'payment_method_id' => 'Phương thức thanh toán',
            'enviroment' => 'Môi trường sử dụng',
            'position' => 'Vị trí',
            'status' => 'Trạng thái',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public function validateForm($attribute, $param)
    {
        switch ($attribute) {
            case "enviroment":
                if (intval($this->id) == 0) {
                    $partner_payment_method = Tables::selectOneDataTable("partner_payment_method", "partner_payment_id =" . $this->partner_payment_id . " AND payment_method_id =" . $this->payment_method_id . " AND enviroment = '" . $this->enviroment . "' ");
                    if ($partner_payment_method) {
                        $this->addError($attribute, 'Môi trường sử dụng đã tồn tại ở kênh thanh toán này.');
                    }
                } else {
                    $partner_payment_method = Tables::selectOneDataTable("partner_payment_method", "id != " . $this->id . " AND partner_payment_id =" . $this->partner_payment_id . " AND payment_method_id =" . $this->payment_method_id . " AND enviroment = '" . $this->enviroment . "' ");
                    if ($partner_payment_method) {
                        $this->addError($attribute, 'Môi trường sử dụng đã tồn tại ở kênh thanh toán này.');
                    }
                }
                break;
        }
    }
    
    public static function getByPaymentMethodId($payment_method_id) {
        $partner_payment_method = PartnerPaymentMethod::find()
                ->where(['payment_method_id' => $payment_method_id])
                ->andWhere(['status' => self::STATUS_ACTIVE])
                ->asArray()
                ->one();
        if (!is_null($partner_payment_method)) {
            return $partner_payment_method;
        }
        return false;
    }
}
