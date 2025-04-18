<?php

namespace common\components\libs;

use common\components\libs\MyExcel;
use common\models\business\UserLoginBusiness;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use common\components\utils\ObjInput;

class ExportDataV2
{

    public $get_page_number = 'page';
    public $get_rows_on_page = 'per-page';
    public $get_temp_file_name = 'temp_file_name';
    public $get_option = 'option';
    private $page_number = 1;
    private $rows_on_page = 300;
    private $temp_file_name = '';
    public $option = '';
    public $totalRowOnSheet = 5000;

    public function getOption(): string
    {
        return $this->option;
    }

    function __construct($rows_on_page = 300)
    {
        $this->rows_on_page = $rows_on_page;
    }

    public function init($file_name, $columns, $user_created = 'mtq')
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        //---------------
        $this->page_number = ObjInput::get($this->get_page_number, 'int', $this->page_number);
        $this->rows_on_page = ObjInput::get($this->get_rows_on_page, 'int', $this->rows_on_page);
        $this->temp_file_name = ObjInput::get($this->get_temp_file_name, 'str', $this->temp_file_name);
        $this->option = ObjInput::get($this->get_option, 'str', '');
        if ($this->temp_file_name == '') {
            $this->temp_file_name = uniqid() . '.txt';
        }
        if ($this->option == 'export_data') {
//            var_dump(123);die();
            $this->_export($file_name, $columns, $user_created);
            return false;
        } elseif ($this->option == 'clear_temp') {
            @unlink($this->getTempFilePath());
            return false;
        } elseif ($this->option == 'export_report') {
            return true;


        }
        return true;
    }

    public function getTempFilePath()
    {
        $dir_path = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'export_data_temp' . DS;
        return $dir_path . $this->temp_file_name;
    }

    private function _writeData($data)
    {
        $file = fopen($this->getTempFilePath(), 'a+');
        if ($file) {
            foreach ($data as $row) {
                fwrite($file, json_encode($row) . "\n");
            }
            fclose($file);
        }
    }

    public function _readData(): array
    {
        $data = array();
        if (file_exists($this->getTempFilePath())) {
            $file = fopen($this->getTempFilePath(), "r");
            if ($file) {
                while (!feof($file)) {
                    $line = fgets($file);
                    if ($line != '') {
                        $data[] = json_decode($line, true);
                    }
                }
                fclose($file);
            }
        }
        return $data;
    }

    private function _export($file_name, $columns, $user_created)
    {
        $data = $this->_readData();
        if (ob_get_contents()) ob_end_clean();
        //-------------
        $obj = new MyExcel();

        $obj->totalRowOnSheet = $this->totalRowOnSheet;

        $obj->setColumns($columns, true);


        $obj->createFile($file_name, $data, $user_created, "Office 2007", "Office 2007", "MTQ");

    }

    public function process($data, $type_export = 'export_data')
    {
        $error = 'Lỗi không xác định';
        $next_url = $this->getNextDataUrl();
        $type_url = 'get_data';
        $row_processed = 0;
        if (!empty($data)) {
            $error = '';
            $row_processed = $this->getOffset() + count($data);
            $this->_writeData($data);
        } else {
            if ($this->page_number > 1) {
                $error = '';
                $type_url = $type_export;
                $next_url = $this->getExportUrl($type_export);
            } else {
                $error = 'Không có dữ liệu để trích xuất';
            }
        }
        return array('error' => $error, 'next_url' => $next_url, 'type_url' => $type_url, 'row_processed' => $row_processed);
    }

    public function getNextDataUrl()
    {
        $gets = Yii::$app->request->get();
        $gets[$this->get_page_number] = $this->page_number + 1;
        $gets[$this->get_rows_on_page] = $this->rows_on_page;
        $gets[$this->get_temp_file_name] = $this->temp_file_name;
        $gets[$this->get_option] = 'get_data';
        $params = array(
            Yii::$app->controller->id . '/' . Yii::$app->controller->action->id
        );
        $params = array_merge($params, $gets);
        return Yii::$app->urlManager->createUrl($params);
    }

    public function getExportUrl($type_export)
    {
        $gets = Yii::$app->request->get();
        $gets[$this->get_temp_file_name] = $this->temp_file_name;
        $gets[$this->get_option] = $type_export;
        $params = array(
            Yii::$app->controller->id . '/' . Yii::$app->controller->action->id
        );
        $params = array_merge($params, $gets);
        return Yii::$app->urlManager->createUrl($params);
    }

    public function getPageNumber()
    {
        return $this->page_number;
    }

    public function getOffset()
    {
        return ($this->page_number - 1) * $this->rows_on_page;
    }

    public function getLimit()
    {
        return $this->rows_on_page;
    }
}
