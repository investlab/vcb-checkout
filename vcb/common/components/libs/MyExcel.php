<?php

namespace common\components\libs;

use Yii;
use PHPExcel;
use PHPExcel_Style_Alignment;
use yii\helpers\FileHelper;
use Faker\Provider\tr_TR\DateTime;
use yii\base\Exception;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style_NumberFormat;

class MyExcel
{

    public $columns = null;
    public $totalRowOnSheet = 5000;
    public $excel = null;
    public $sheets = null;
    protected $cells = null;

    function __construct()
    {
        $this->_setCells();
    }

    protected function _setCells()
    {
        $azRange = range('A', 'Z');
        $index = 0;
        foreach ($azRange as $letter) {
            $this->cells[$index++] = $letter;
        }
        foreach ($azRange as $letter1) {
            foreach ($azRange as $letter2) {
                $this->cells[$index++] = $letter1 . $letter2;
            }
        }
    }

    public function setColumns($columns, $update_cell = false)
    {
        if ($update_cell == true) {
            $index = 0;
            foreach ($columns as $key => $column) {
                $columns[$key]['cell'] = $this->cells[$index];
                $index++;
            }
        }
        $this->columns = $columns;
    }

    protected function _setColumnsByData($data)
    {
        if (!empty($data)) {
            foreach ($data as $row) {
                $index = 0;
                foreach ($row as $key => $value) {
                    $this->columns[$key] = array('title' => $key, 'cell' => $this->cells[$index]);
                    $index++;
                }
                break;
            }
        }
    }

    protected function _setSheets(PHPExcel &$excel, $data, &$total_sheet = 0)
    {
        $total_sheet = ceil(count($data) / $this->totalRowOnSheet);
        if ($total_sheet > 1) {
            for ($i = 1; $i < $total_sheet; $i++) {
                $excel->createSheet();
            }
            for ($i = 0; $i < $total_sheet; $i++) {
                $excel->setActiveSheetIndex($i);
                $excel->getActiveSheet()->setTitle('Sheet ' . ($i + 1));
            }
        }
    }

    protected function _setSheetRowHeader(&$sheet, $colums = null)
    {
        if (empty($colums)) {
            $colums = $this->columns;
        }
        $cell_last = '';
        foreach ($colums as $col) {
            $cell_last = $col['cell'];
            $sheet->SetCellValue($col['cell'] . '1', $col['title']);
        }
        $sheet->getStyle('A1:' . $cell_last . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $cell_last . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

    protected function _setSheetRowData(&$sheet, $row, $index, $columns = null)
    {
        if (empty($columns)) {
            $columns = $this->columns;
        }
        foreach ($columns as $key => $col) {
            $cell_last = $col['cell'];
            if (!is_array($row[$key])) {
                $value = (isset($row[$key]) && !empty($row[$key])) ? strval($row[$key]) : '';
            } else {
                $value = '';
            }
            if (@$col['type'] == 'time') {
                if ($value != '' && $value != 0) {
                    $value = PHPExcel_Shared_Date::PHPToExcel($value, true, 'Asia/Ho_Chi_Minh');
                    $sheet->SetCellValue($col['cell'] . $index, $value);
                    $sheet->getStyle($col['cell'] . $index)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
                } else {
                    $sheet->SetCellValue($col['cell'] . $index, '');
                }
            } elseif (@$col['type'] == 'number') {
                $sheet->SetCellValue($col['cell'] . $index, $value);
                $sheet->getStyle($col['cell'] . $index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            } elseif (@$col['type'] == 'money') {
                $sheet->SetCellValue($col['cell'] . $index, $value);
                $sheet->getStyle($col['cell'] . $index)->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet->SetCellValue($col['cell'] . $index, $value);
                $sheet->getStyle($col['cell'] . $index)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
            foreach(range('A','Q') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            if (@$col['validation'] != '') {
                $cell = $col['cell'] . $index;
                eval($col['validation'] . '($sheet, $cell, $row);');
            }
        }
    }

    protected function _createFileExcel($file_name, $data, $creator, $title, $subject, $description, &$total_sheet = 0)
    {
        $this->_setObjectExcel($file_name, $data, $creator, $title, $subject, $description, $total_sheet);
        $write = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $write->save($file_name);
        $this->excel = null;
    }

    protected function _setObjectExcel($file_name, $data, $creator, $title, $subject, $description, &$total_sheet = 0)
    {
        $this->excel = new PHPExcel();
        $this->excel->getProperties()->setCreator($creator);
        $this->excel->getProperties()->setTitle($title);
        $this->excel->getProperties()->setSubject($subject);
        $this->excel->getProperties()->setDescription($description);
        if (!empty($data)) {
            $this->_setSheets($this->excel, $data, $total_sheet);
        }
    }

    protected function _writeDataFileExcel($file_name, $data, $sheet_index)
    {
        if (!empty($data)) {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $this->excel = $objReader->load($file_name);
            $this->_writeDataObjectExcel($file_name, $data, $sheet_index);
            $write = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
            $write->save($file_name);
            $this->excel = null;
        }
    }

    protected function _writeDataObjectExcel($file_name, $data, $sheet_index)
    {
        if (!empty($data)) {
            if ($this->columns == null) {
                $this->_setColumnsByData($data);
            }
            $this->excel->setActiveSheetIndex($sheet_index);
            $sheet_active = $this->excel->getActiveSheet();
            $this->_setSheetRowHeader($sheet_active);
            //------------
            $first_index = 2;
            $index = 0;
            $start = $sheet_index * $this->totalRowOnSheet;
            $end = ($sheet_index + 1) * $this->totalRowOnSheet;
            foreach ($data as $row) {
                if ($index >= $start && $index < $end) {
                    $this->_setSheetRowData($sheet_active, $row, $first_index++);
                }
                $index++;
            }
        }
    }

    public function writeRowsFile($file_path, $rows, $creator, $title, $subject, $description, $colums = null)
    {
        if (!file_exists($file_path)) {
            $objExcel = new PHPExcel();
            $objExcel->getProperties()->setCreator($creator);
            $objExcel->getProperties()->setTitle($title);
            $objExcel->getProperties()->setSubject($subject);
            $objExcel->getProperties()->setDescription($description);
            if (!empty($rows)) {
                $this->_setSheets($objExcel, $rows, $total_sheet);
            }
            $sheet_index = 0;
            $objExcel->setActiveSheetIndex($sheet_index);
            $sheet_active = $objExcel->getActiveSheet();
            $this->_setSheetRowHeader($sheet_active, $colums);

        } else {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objExcel = $objReader->load($file_path);
            $sheet_index = 0;
            $objExcel->setActiveSheetIndex($sheet_index);
            $sheet_active = $objExcel->getActiveSheet();
        }
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        //------------
        $index = $sheet_active->getHighestRow() + 1;
        foreach ($rows as $row) {
            $this->_setSheetRowData($sheet_active, $row, $index++, $colums);
        }
        $write = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $write->save($file_path);
        $objExcel = null;
    }

    public function createFile($file_name, $data, $creator, $title, $subject, $description)
    {
        ini_set('memory_limit', '2048M');
        $file_temp_name = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'temp' . DS . 'temp.xls';

        $this->_setObjectExcel($file_temp_name, $data, $creator, $title, $subject, $description, $total_sheet);
        for ($i = 0; $i < $total_sheet; $i++) {
            set_time_limit(0);
            $this->_writeDataObjectExcel($file_temp_name, $data, $i);
        }

        if(stristr($_SERVER['HTTP_USER_AGENT'], 'ipad') OR stristr($_SERVER['HTTP_USER_AGENT'],     'iphone') OR stristr($_SERVER['HTTP_USER_AGENT'], 'ipod'))
        {
            header("Content-Type: application/octet-stream");
        }else{
            header('Content-Type: application/vnd.ms-excel');
        }
        header('Content-Disposition: attachment;filename=' . $file_name . ' ');
        header('Cache-Control: max-age=0');

        $write = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $write->save('php://output');

    }

    /* public function createFile($file_name, $data, $creator, $title, $subject, $description) {       
      $this->excel = new PHPExcel();
      $this->excel->getProperties()->setCreator($creator);
      $this->excel->getProperties()->setTitle($title);
      $this->excel->getProperties()->setSubject($subject);
      $this->excel->getProperties()->setDescription($description);
      if (!empty($data)) {
      $this->_setSheets($this->excel, $data);
      if ($this->columns == null) {
      $this->_setColumnsByData($data);
      }
      $index = 0;
      $sheet_index = null;
      $sheet_active = null;
      $first_index = 2;
      foreach ($data as $row) {
      if ($sheet_index !== floor($index / $this->totalRowOnSheet)) {
      $sheet_index = floor($index / $this->totalRowOnSheet);
      $this->excel->setActiveSheetIndex($sheet_index);
      $sheet_active = $this->excel->getActiveSheet()->setTitle('Sheet '.($sheet_index + 1));
      $this->_setSheetRowHeader($sheet_active);
      $first_index = 2;
      }
      //---------
      $this->_setSheetRowData($sheet_active, $row, $first_index++);
      $index++;
      }
      }
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename=' . $file_name . ' ');
      header('Cache-Control: max-age=0');
      $write = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
      $write->save('php://output');
      } */

    protected function _getKeyColumns()
    {
        $result = array();
        foreach ($this->columns as $key => $col) {
            $result[$col['cell']] = $key;
        }
        return $result;
    }

    protected function _getRange($start, $end)
    {
        $result = array();
        $this->_setRange($result, $end);
        return $result;
    }

    protected function _setRange(&$range, $end, $prefix = '', $jump = 1)
    {
        if (strlen($end) >= $jump) {
            $cells = range('A', 'Z');
            foreach ($cells as $cell) {
                $range[] = $prefix . $cell;
                if ($prefix . $cell == $end) {
                    return true;
                }
            }
            foreach ($cells as $cell) {
                if ($this->_setRange($range, $end, $prefix . $cell, $jump + 1)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function readFile($file_path)
    {
        try {
            $file_type = PHPExcel_IOFactory::identify($file_path);
            $objReader = PHPExcel_IOFactory::createReader($file_type);
            $objExcel = $objReader->load($file_path);
            $sheet = $objExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $rowData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, false);
            if ($this->columns != null) {
                $keys = $this->_getKeyColumns();
                if (!empty($rowData)) {
                    $index = 0;
                    $result = array();
                    //$range = range('A', $highestColumn);
                    $range = $this->_getRange('A', $highestColumn);
                    foreach ($rowData as $row) {
                        if (!$this->isRowBlank($row)) {
                            if ($index++ > 0) {
                                $i = 0;
                                foreach ($range as $cell) {
                                    $key = isset($keys[$cell]) ? $keys[$cell] : $i;
                                    $result[$index][$key] = $row[$i];
                                    $i++;
                                }
                            }
                        }
                    }
                    return $result;
                }
            } else {
                return $rowData;
            }
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    public function isRowBlank($row)
    {
        foreach ($row as $cell_value) {
            if (trim($cell_value) != '') {
                return false;
            }
        }
        return true;
    }

    public static function convertDateTimeToTimestamp($date_time)
    {
        return ($date_time - 25569) * 86400 - 7 * 3600;
    }
}
