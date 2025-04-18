<?php

namespace common\components\libs;

use Yii;

class MyCsv
{

    public $columns = null;
    public $delimiter = ',';

    function __construct()
    {

    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    function readFile($file_path)
    {
        $file = fopen($file_path, "r");
        if ($file != false) {
            $rows = array();
            $index = 0;
            while (($data = fgetcsv($file, 1000, $this->delimiter)) !== false) {
                foreach ($this->columns as $key => $name) {
                    $rows[$index][$name] = trim(@$data[$key]);
                }
                $index++;
            }
            fclose($file);
            return $rows;
        }
        return false;
    }

    private function _getCellValue($value)
    {
        $value = trim($value);
        if (substr($value, 0, 1) === '0') {
            $value = "'" . $value;
        }
        return $value;
    }

    function writeFile($file_path, $rows)
    {
        $file = fopen($file_path, "a");
        if ($file != false) {
            if (!empty($rows)) {
                $str_row = implode($this->delimiter, array_keys($this->columns));
                fwrite($file, $str_row . " \n");
                foreach ($rows as $row) {
                    $cells = array();
                    foreach ($this->columns as $key => $name) {
                        $cells[$key] = $this->_getCellValue(@$row[$key]);
                    }
                    $str_row = implode($this->delimiter, $cells);
                    fwrite($file, $str_row . " \n");
                }
            }
            fclose($file);
            return true;
        }
        return false;
    }

}
