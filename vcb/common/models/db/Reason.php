<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;

/**
 * This is the model class for table "reason".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class Reason extends MyActiveRecord {

    /**
     * @inheritdoc
     */
    const CANCEL_BILL = 1;
    const REFUN = 2;
    const CANCEL_SHIP = 3;
    const REASON_LOAN = 4;
    const CANCEL_PROFILE = 5;
    const STOCK_TRANSFER_REQUEST = 6;
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    public static function tableName() {
        return 'reason';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type', 'name'], 'required'],
            [['id', 'type', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Loại lý do',
            'name' => 'Tên lý do',
            'description' => 'Mô tả',
            'status' => 'Trạng thái',
            'time_created' => 'Thời gian tạo',
            'time_updated' => 'Thời gian cập nhật',
            'user_created' => 'Người tạo',
            'user_updated' => 'Người cập nhật',
        ];
    }

    public static function getStatus() {
        return array(
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_LOCK => 'Bị khóa'
        );
    }

    public static function getType() {
        return array(
            self::CANCEL_BILL => 'Hủy đơn hàng',
            self::REFUN => 'Hoàn tiền',
            self::CANCEL_SHIP => 'Hủy giao hàng',
            self::REASON_LOAN => 'Rút tiền',
        );
    }

    public static function getDataForSelectBox($type, $result = array()) {
        $reason_info = Tables::selectAllDataTable("reason", "type = $type AND status = " . self::STATUS_ACTIVE);
        if ($reason_info != false) {
            foreach ($reason_info as $row) {
                $result[$row['id']] = $row['name'];
            }
        }
        return $result;
    }

}
