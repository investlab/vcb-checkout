<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "news_category".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property integer $position
 * @property integer $parent_id
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 * @property integer $user_created
 * @property integer $user_updated
 */
class NewsCategory extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'position', 'status'], 'required'],
            [['description'], 'string'],
            [['position', 'parent_id', 'left', 'right', 'level', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'position' => 'Position',
            'parent_id' => 'Parent ID',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
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
