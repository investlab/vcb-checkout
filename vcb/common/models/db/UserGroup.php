<?php

namespace common\models\db;

use common\components\libs\Tables;
use common\components\utils\Validation;
use Yii;

/**
 * This is the model class for table "user_group".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $parent_id
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property integer $position
 * @property integer $status
 */
class UserGroup extends MyActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'parent_id', 'left', 'right', 'level', 'status', 'position'], 'integer', 'message' => 'Vị trí không hợp lệ.'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'checkString'],
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
            'name' => 'Tên nhóm',
            'code' => 'Mã  nhóm',
            'parent_id' => 'Cấp cha',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
            'position' => 'Vị trí',
            'status' => 'Trạng thái',
        ];
    }

    public static function getStatus()
    {
        return array(
            1 => 'Hoạt động',
            2 => 'Bị khóa'
        );
    }

    public static function getUserGroup($groups)
    {
        $result = Tables::selectAllDataTable("user_group", "status = 1", "`left` ASC ");
        if ($result != false) {
            foreach ($result as $row) {
                $groups[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
        }
        return $groups;
    }

    public static function getSubUserGroupIds($user_group_id)
    {
        $ids = array();
        $user_group_info = Tables::selectOneDataTable("user_group", "id = $user_group_id AND status = " . self::STATUS_ACTIVE);
        if ($user_group_info != false) {
            $data = Tables::selectAllDataTable("user_group", "`left` > " . $user_group_info['left'] . " AND `right` < " . $user_group_info['right'] . " AND status = " . self::STATUS_ACTIVE, "`left` ASC ");
            if ($data != false) {
                foreach ($data as $row) {
                    $ids[$row['id']] = $row['id'];
                }
            }
        }
        return $ids;
    }

    public static function getRightIds($user_group_id)
    {
        $right_ids = array();
        $data = Tables::selectAllDataTable("user_group_right", "user_group_id = $user_group_id ");
        if ($data != false) {
            foreach ($data as $row) {
                $right_ids[$row['right_id']] = $row['right_id'];
            }
        }
        return $right_ids;
    }

    public static function getLevel($parent_id)
    {
        if ($parent_id != 0) {
            $result = Tables::selectOneDataTable("user_group", "id = $parent_id ");
            if ($result != false) {
                return $result['level'] + 1;
            }
        } else {
            return 1;
        }
    }

    public function checkString($attribute, $param)
    {
        switch ($attribute) {
            case "code":
                if (!Validation::checkCode($this->code)) {
                    $this->addError($attribute, 'Mã nhóm quyền không hợp lệ.');
                }
                break;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::_updateIndexCategory($this->tableName());
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
                $result["id_" . $row['id']] = "UPDATE $table SET $table.level = $level, $table.left = $index, ";
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
}
