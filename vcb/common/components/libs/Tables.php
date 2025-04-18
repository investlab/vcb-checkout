<?php
/*
 * Class lấy dữ liệu theo table
 * 
 * Đếm số bản ghi theo điều kiện
 * Lấy tất cả bản ghi theo điều kiện
 * Lấy một bản ghi theo điều kiện
 * Lấy giới hạn số bản ghi theo điều kiện
 * 
 * Lấy tất cả bản ghi theo câu lệnh SQL
 * Lấy một bản ghi theo câu lệnh SQL
 */
namespace common\components\libs;

use yii\db\Connection;
use Yii;

class Tables
{
    /*
     * Đếm số bản ghi
     * @input: table, where
     * 
     * 
     * @return number rows
     */
    public static function selectCountDataTable($table, $where = 1)
    {
        $params = self::getParamsForConditions($where, $str_condition);
        $rows = (new \yii\db\Query())
            ->select('*')
            ->from($table)
            ->where($str_condition, $params)
            ->count();

//        var_dump($rows);die();

        return $rows;
    }

    public static function selectCountDataTableV2($table, $column = '*', $where = 1)
    {
        $params = self::getParamsForConditions($where, $str_condition);
        self::getColumnForCount($column, $str_column);

        $sql = "SELECT " . $str_column . " FROM " . $table . " WHERE " . $str_condition;
        return Yii::$app->getDb()->createCommand($sql)->queryAll();

    }

    protected static function getColumnForCount($columns, &$str_column = '')
    {
        if (is_array($columns) && !empty($columns)) {
            foreach ($columns as $key => $item) {
                if ($key === 0) {
                    $str_column = "SUM(IF(" . $item[0] . "=" . $item[1] . ",1,0)) AS " . $item[2];
                } else {
                    $str_column .= ", " . "SUM(IF(" . $item[0] . "=" . $item[1] . ",1,0)) AS " . $item[2];
                }
            }
        }
    }


    public static function selectSumDataTable($table, $where = 1,$key)
    {
        $params = self::getParamsForConditions($where, $str_condition);
        $rows = (new \yii\db\Query())
            ->select(['COUNT(*) as counts','SUM('.$key.') as totals' ])
            ->from($table)
            ->where($str_condition, $params)
            ->one();
        
        return $rows;
    }
    /*
     * Lay 1 ban ghi trong 1 table voi dieu kien
     * @input: table, where(dieu kien)
     * 
     * @return array
     */
    public static function selectOneDataTable($table, $where = 1, $order_by = 'id desc', $fields = "*")
    {
        $params = self::getParamsForConditions($where, $str_condition);
        $rows = (new \yii\db\Query())
            ->select($fields)
            ->from($table)
            ->where($str_condition, $params)
            ->orderBy($order_by)
            ->one();
        return $rows;
    }

    /*
     * Lay All ban ghi trong 1 table voi dieu kien
     * @input: table, where(dieu kien)
     * 
     * @return array
     */

    public static function selectAllDataTable($table, $where = 1, $order_by = 'id desc', $primary_key = false, $limit = false, $offset = 0, $fields = '*')
    {
        $params = self::getParamsForConditions($where, $str_condition);
        if ($limit == false) {
            $rows = (new \yii\db\Query())
                ->select($fields)
                ->from($table)
                ->where($str_condition, $params)
                ->orderBy($order_by)
                ->all();
        } else {
            $rows = (new \yii\db\Query())
                ->select($fields)
                ->from($table)
                ->where($str_condition, $params)
                ->orderBy($order_by)
                ->limit($limit)
                ->offset($offset)
                ->all();
        }
        if ($rows != false && $primary_key != false) {
            $result = array();
            foreach ($rows as $row) {
                $result[$row[$primary_key]] = $row;
            }
            return $result;
        }
        Yii::$app->db->createCommand();
        return $rows;
    }

    /*
     * Lay Giaoi han so ban ghi trong 1 table voi dieu kien
     * @input: table, where(dieu kien), limit (So ban ghi can lay)
     * 
     * @return array
     */

    public static function selectLimitDataTable($table, $where = 1, $limit = 25)
    {
        $params = self::getParamsForConditions($where, $str_condition);
        $rows = (new \yii\db\Query())
            ->select('*')
            ->from($table)
            ->where($str_condition, $params)
            ->limit($limit)
            ->all();
        return $rows;
    }

    /*
     * Lấy toàn bộ dữ liệu theo câu lệnh sql
     * 
     */
    public static function getAllDataForQuery($sqlQuery = '')
    {
        $result = Yii::$app->db->createCommand($sqlQuery)->queryAll();

        return $result;
    }

    /*
     * Lấy 1 dòng khi query
     */
    public static function getOneRowDataForQuery($sqlQuery = '')
    {
        $result = Yii::$app->db->createCommand($sqlQuery)->queryOne();

        return $result;
    }

    public static function selectAllBySql($sql, $primary_key = false)
    {
        $connection = Yii::$app->getDb();
        if (SHOW_SQL == true) {
            $GLOBALS['DEBUG']['SQL'][] = $sql;
        }
        $command = $connection->createCommand($sql);
        $rows = $command->queryAll();
        if (!empty($rows) && $primary_key != false) {
            $result = array();
            foreach ($rows as $row) {
                $result[$row[$primary_key]] = $row;
            }
            return $result;
        }
        return $rows;
    }

    public static function selectOneBySql($sql)
    {
        $connection = Yii::$app->getDb();
        if (SHOW_SQL == true) {
            $GLOBALS['DEBUG']['SQL'][] = $sql;
        }
        $command = $connection->createCommand($sql);
        return $command->queryOne();
    }

    public static function selectExpressionBySql($sql)
    {
        $connection = Yii::$app->getDb();
        if (SHOW_SQL == true) {
            $GLOBALS['DEBUG']['SQL'][] = $sql;
        }
        $command = $connection->createCommand($sql);
        return $command->queryScalar();
    }

    final public static function setParamsAndConditions(&$str_condition, &$params)
    {
        $params = self::getParamsForConditions(array_merge(array($str_condition), $params), $str_condition);
    }

    final public static function getParamsForConditions($conditions, &$str_condition = '')
    {
        $params = array();
        if (is_array($conditions) && !empty($conditions)) {
            $replates = array();
            foreach ($conditions as $key => $value) {
                if ($key === 0) {
                    $str_condition = $value;
                } else {
                    if (is_array($value)) {
                        self::_setParams($key, $value, $params, $replates);
                    } elseif (preg_match('/^(\d+)(,(\d+))+$/', str_replace(' ', '', $value))) {
                        $value = str_replace(' ', '', $value);
                        $value = explode(',', $value);
                        self::_setParams($key, $value, $params, $replates);
                    } else {
                        $params[$key] = $value;
                    }
                }
            }
            if (!empty($replates)) {
                foreach ($replates as $key => $value) {
                    $str_condition = str_replace($key, $value, $str_condition);
                }
            }
        } else {
            $str_condition = $conditions;
        }
        return $params;
    }

    final protected static function _setParams($key, $value, &$params = array(), &$replates = array())
    {
        $index = 0;
        $temp = array();
        foreach ($value as $sub_value) {
            $params[$key . '_' . $index] = intval($sub_value);
            $temp[$index] = ':' . $key . '_' . $index;
            $index++;
        }
        $replates[':' . $key] = implode(',', $temp);
    }

    public static function selectAllByIds($table, $ids, $fields = "*", $order_by = false, $primary_key = "id", $limit = false, $offset = 0)
    {
        $query = new \yii\db\Query();
        $query->select($fields)->from($table)->where("id IN (" . implode(',', $ids) . ") ");
        if ($order_by != false) {
            $query->orderBy($order_by);
        }
        if ($limit != false) {
            $query->limit($limit)->offset($offset);
        }
        $rows = $query->all();
        if ($rows != false && $primary_key != false) {
            $result = array();
            foreach ($rows as $row) {
                $result[$row[$primary_key]] = $row;
            }
            return $result;
        }
        return $rows;
    }
}
