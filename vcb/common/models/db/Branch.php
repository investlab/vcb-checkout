<?php


namespace common\models\db;

use common\components\utils\Translate;
use Yii;

/**
 * This is the model class for table "bank".
 *
 * @property integer $id
 * @property string $name
 * @property string $city
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */

class Branch extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','city'], 'required'],
            [['name','city'], 'string'],
            [['id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'city'], 'string', 'max' => 255],
            [['name'], 'unique']
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
            'city' => 'City',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
        ];
    }

    public static function getStatus() {
        return [
            self::STATUS_ACTIVE => Translate::get('Kích hoạt'),
            self::STATUS_LOCK => Translate::get('Đang khóa'),
        ];
    }

    public static function getOperators()
    {
        return array(
            'detail' => array('title' => 'Chi tiết', 'confirm' => false),
            'view-update' => array('title' => 'Cập nhật', 'confirm' => false),
            'active' => array('title' => 'Mở khóa chi nhánh', 'confirm' => true),
            'lock' => array('title' => 'Khóa chi nhánh', 'confirm' => true),
            'add' => array('title' => 'Thêm', 'confirm' => false, 'check-all' => true),
        );
    }

    public static function getOperatorsByStatus($row)
    {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['detail'] = $operators['detail'];
                $result['view-update'] = $operators['view-update'];
                $result['lock'] = $operators['lock'];
                break;
            case self::STATUS_LOCK:
                $result['detail'] = $operators['detail'];
                $result['active'] = $operators['active'];
                break;
        }
        $result = self::getOperatorsForUser($row, $result);
        return $result;
    }
}