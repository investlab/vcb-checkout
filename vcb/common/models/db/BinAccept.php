<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "bank".
 *
 * @property integer $id
 * @property string $bin_code
 * @property string $card_type
 * @property string $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class BinAccept extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bin_accept';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'bin_code', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['card_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bin_code' => 'Bin Code',
            'card_type' => 'Card Type',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getOperators()
    {
        return array(
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
            'active' => array('title' => 'Mở khóa', 'confirm' => true),
            'lock' => array('title' => 'Khóa', 'confirm' => true),
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
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
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }

    public static function getCardType() {
        $card_type = [
            "" => 'Chọn loại thẻ',
            'VISA' => 'VISA',
            'MASTERCARD' => 'MASTERCARD',
            'JCB' => 'JCB',
            'AMEX' => 'AMEX',
        ];

        return $card_type;
    }

    public static function getStatus() {
        $status = [
            "" => 'Trạng thái',
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đã khóa',
        ];

        return $status;
    }

    public function checkBinAccept($card_number) {
        $flag = false;
        $bin_accept = self::findAll(['status' => self::STATUS_ACTIVE]);

        if (!empty($bin_accept)) {
            foreach ($bin_accept as $key => $bin) {
                $cut_number = substr($card_number, 0, strlen($bin['bin_code']));

                if ($cut_number == $bin['bin_code']) {
                    $flag = true;
                    break;
                }
            }
        }

        return $flag;
    }
}
