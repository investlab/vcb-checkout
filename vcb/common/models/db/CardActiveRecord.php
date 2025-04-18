<?php

namespace common\models\db;

use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class CardActiveRecord extends MyActiveRecord
{

    public static function search($table, $fields, $where, $page_no = 1, $page_size = 20, &$search_params = array())
    {
        $rows = false;
        $page_offset = ObjInput::get('page_offset', 'str', '');
        $has_next_page = false;
        $has_previous_page = false;
        $next_offset = false;
        $previous_offset = false;
        if ($page_offset == '') {
            $offset = ($page_no - 1) * $page_size;
            $rows = self::_getSearchRows($table, $fields, $where, $offset, $page_size + 1, $count_row_not_backup);
            if ($count_row_not_backup < $page_size) {
                $next_offset = $page_size - $count_row_not_backup;
                $previous_offset = false;
            }
        } elseif (self::_isPageOffset($page_offset, $offset)) {
            $rows = self::_getSearchRows($table . '_backup', $fields, $where, $offset, $page_size + 1);
            $next_offset = $offset + $page_size;
            $previous_offset = ($offset - $page_size) >= 0 ? ($offset - $page_size) : false;
        }
        if ($rows != false && count($rows) > $page_size) {
            $has_next_page = true;
        }
        if ($page_no > 1) {
            $has_previous_page = true;
        }
        $result = self::_getSearchResult($rows, $page_size);
        $search_params = self::_getSearchParams($next_offset, $previous_offset, $has_next_page, $has_previous_page, $page_no);
        return $result;
    }

    protected static function _getSearchRows($table, $fields, $where, $offset, $page_size, &$count_row_not_backup = 0)
    {
        $rows = false;
        $data = Tables::selectAllDataTable($table, $where, "id DESC ", "id", $page_size, $offset, $fields);
        if ($data != false) {
            $rows = $data;
            if (!self::_isTableBackup($table)) {
                $count_row_not_backup = count($rows);
                if ($count_row_not_backup < $page_size) {
                    $rows_backup = self::_getSearchRows($table . '_backup', $fields, $where, 0, $page_size - $count_row_not_backup);
                    if ($rows_backup != false) {
                        $rows = array_merge($rows, $rows_backup);
                    }
                }
            }
        } else {
            if (!self::_isTableBackup($table)) {
                $rows_backup = self::_getSearchRows($table . '_backup', $fields, $where, $offset, $page_size);
                if ($rows_backup != false) {
                    $rows = $rows_backup;
                }
            }
        }
        return $rows;
    }

    protected static function _getSearchParams($next_offset, $previous_offset, $has_next_page, $has_previous_page, $page_no)
    {
        $result = array();
        if ($has_next_page == true) {
            $result['next'] = array('page_no' => $page_no + 1);
            if ($next_offset !== false) {
                $result['next']['page_offset'] = self::_getPageOffset($next_offset);
            }
        }
        if ($has_previous_page == true) {
            $result['previous'] = array('page_no' => $page_no - 1);
            if ($previous_offset !== false) {
                $result['previous']['page_offset'] = self::_getPageOffset($previous_offset);
            }
        }
        return $result;
    }

    protected static function _getSearchResult($rows, $page_size)
    {
        $result = array();
        $index = 0;
        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                $result[$key] = $row;
                $index++;
                if ($index >= $page_size) {
                    break;
                }
            }
        }
        return $result;
    }

    protected static function _getPageOffset($offset)
    {
        return $offset . '-' . substr(md5($offset . 'filter'), -6);
    }

    protected static function _isPageOffset($page_offset, &$offset = 0)
    {
        if (preg_match('/(\d+)-(.*)/', $page_offset, $part)) {
            $offset = $part[1];
            if (self::_getPageOffset($offset) == $page_offset) {
                return true;
            }
        }
        return false;
    }

    protected static function _isTableBackup($table)
    {
        if (substr($table, -7) == '_backup') {
            return true;
        }
        return false;
    }
}
