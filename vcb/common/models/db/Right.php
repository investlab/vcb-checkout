<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\components\utils\Validation;

/**
 * This is the model class for table "right".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property string $title
 * @property string $link
 * @property string $params
 * @property integer $parent_id
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property integer $position
 * @property integer $status
 * @property integer $type
 */
class Right extends MyActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const TYPE_BACKEND = 1;
    const TYPE_MERCHANT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'right';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'parent_id', 'status', 'position'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['description', 'params'], 'string'],
            [['parent_id', 'left', 'right', 'level', 'position', 'status', 'type', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['name', 'title', 'link'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 100],
            [['code'], 'isCode'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên quyền',
            'code' => 'Mã quyền',
            'description' => 'Mô tả',
            'title' => 'Tiêu đề',
            'link' => 'Liên kết',
            'params' => 'Tham số',
            'parent_id' => 'Quyền cấp cha',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
            'position' => 'Vị trí',
            'status' => 'Trạng thái',
        ];
    }

    public function isCode($attribute, $params)
    {
        $data = Tables::selectOneDataTable("`right`", "code = '" . $this->$attribute . "' ");
        if ($data != false && $data['id'] != $this->id) {
            $this->addError($attribute, 'Mã quyền đã tồn tại');
        }
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_LOCK => 'Đang khóa',
        );
    }

    public static function getCategories($categories)
    {
        $result = Right::find()->where(['type' => Right::TYPE_BACKEND])->addOrderBy('left')->all();
        if ($result != false) {
            foreach ($result as $row) {
                $categories[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
        }
        return $categories;
    }

    public static function getCategoryMerchants($categories)
    {
        $result = Right::find()->where(['type' => Right::TYPE_MERCHANT])->addOrderBy('left')->all();
        if ($result != false) {
            foreach ($result as $row) {
                $categories[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
        }
        return $categories;
    }

    public static function getSubIdsByParentId($parent_id)
    {
        $result = array($parent_id);
        $data_info = Tables::selectOneDataTable("`right`", "id = $parent_id ");
        if ($data_info != false) {
            $data = Tables::selectAllDataTable("`right`", "right.left > " . $data_info['left'] . " AND right.right < " . $data_info['right'] . " ");
            if ($data != false) {
                foreach ($data as $row) {
                    $result[$row['id']] = $row['id'];
                }
            }
        }
        return $result;
    }

    protected function _updateIndexCategory($table, $id = false)
    {
        $level = 1;
        $index = 0;
        $parent_id = 0;
        if ($id !== false && is_numeric($id)) {
            $category = Tables::selectOneDataTable($table, "$table.id = $id ");
            if ($category != false) {
                $level = $category['level'] - 1 == 0 ? $level : $category['level'] - 1;
                $index = $category['left'] - 1 < 0 ? $index : $category['left'];
                $parent_id = $category['parent_id'];
            }
        }
        $queries = $this->_getQueryUpdateIndexCategory($table, $parent_id, $index, $level);
        if (!empty($queries)) {
            $connection = Yii::$app->getDb();
            foreach ($queries as $sql) {
                $command = $connection->createCommand($sql);
                $result = $command->execute();
            }
        }
        return true;
    }

    protected function _getQueryUpdateIndexCategory($table, $parent_id = 0, &$index = 0, $level = 1)
    {
        $result = array();
        $category = Tables::selectAllDataTable($table, "$table.parent_id = $parent_id ", "$table.position ASC, $table.id ASC ");
        if ($category != false) {
            foreach ($category as $row) {
                $index++;
                $result["id_" . $row['id']] = "UPDATE `$table` SET $table.level = $level, $table.left = $index, ";
                $temp = $this->_getQueryUpdateIndexCategory($table, $row['id'], $index, $level + 1);
                $index++;
                $result["id_" . $row['id']] .= "$table.right = $index WHERE $table.id = " . $row['id'] . " ;";
                if (!empty($temp)) {
                    $result = array_merge($result, $temp);
                }
            }
        }
        return $result;
    }

    public static function isCodeExists($code)
    {
        if (preg_match('/^[A-Z0-9\-_]+(\:\:[A-Z0-9\-_]+)+$/', $code)) {
            $right_info = Tables::selectOneDataTable(self::tableName(), "code = '$code' ");
            if ($right_info != false) {
                return true;
            }
        }
        return false;
    }

}
