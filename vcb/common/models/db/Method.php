<?php

namespace common\models\db;

use common\components\utils\Strings;
use Yii;
use common\components\libs\Tables;
use common\models\business\PaymentMethodBusiness;

/**
 * This is the model class for table "method".
 *
 * @property integer $id
 * @property integer $transaction_type_id
 * @property Strings $name
 * @property Strings $code
 * @property Strings $description
 * @property integer $position
 * @property integer $min_amount
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class Method extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'method';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['transaction_type_id', 'status', 'position', 'time_created', 'time_updated', 'user_created', 'user_updated', 'min_amount'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transaction_type_id' => 'transaction_type_id',
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'position' => 'position',
            'status' => 'Status',
            'min_amount' => 'min_amount',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getMethods($method_code = '')
    {
        $data = Tables::selectAllDataTable("method", "status = " . self::STATUS_ACTIVE, "position ASC, id DESC ");
        if ($data != false) {
            foreach ($data as $key => $row) {
                if ($method_code == '') {
                    $method_code = strtolower($row['code']);
                }
                if (strtolower($row['code']) == $method_code) {
                    $data[$key]['active'] = true;
                } else {
                    $data[$key]['active'] = false;
                }
                $data[$key]['name'] = \common\components\utils\Strings::strip($row['name']);
                $data[$key]['description'] = \common\components\utils\Strings::strip($row['description']);
            }
        }
        return $data;
    }

    public static function getPaymentMethods($method_code, $payment_amount, $time_paid)
    {
        $result = array();
        $data = Tables::selectAllDataTable("method", "(transaction_type_id = 1 OR transaction_type_id = 5) AND status = " . self::STATUS_ACTIVE . " AND min_amount <= $payment_amount ", "position ASC, id DESC ");
        if ($data != false) {
            foreach ($data as $key => $row) {
                if ($method_code == '') {
                    $method_code = strtolower($row['code']);
                }
                if (strtolower($row['code']) == $method_code) {
                    $data[$key]['active'] = true;
                } else {
                    $data[$key]['active'] = false;
                }
                $data[$key]['name'] = Strings::strip($row['name']);
                $data[$key]['description'] = Strings::strip($row['description']);
                $result[$key] = $data[$key];
            }
        }
        return $result;
    }

    public static function getModelFormName($method_code)
    {
        $method_code = trim(strtolower($method_code));
        $temp = explode('-', $method_code);
        $result = 'common\methods\Method';
        foreach ($temp as $item) {
            $result .= ucfirst($item);
        }
        $result .= 'Form';
        return $result;
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function isWithdrawIBOffline($code)
    {
        return ($code == 'WITHDRAW-IB-OFFLINE');
    }

    public static function isWithdrawWallet($code)
    {
        return ($code == 'WITHDRAW-WALLET');
    }

    public static function isWithdrawATMCard($code)
    {
        return ($code == 'WITHDRAW-ATM-CARD');
    }
    
    public static function getCodeById($id) {
        $method_info = Tables::selectOneDataTable("method", ["id = :id ", 'id' => $id]);
        if ($method_info != false) {
            return $method_info['code'];
        }
        return false;
    }
}
