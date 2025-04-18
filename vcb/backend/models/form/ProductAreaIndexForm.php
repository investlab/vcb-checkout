<?php
namespace backend\models\form;

use common\components\libs\Tables;
use yii\base\Model;
use common\components\utils\Validation;
use common\components\utils\FormatDateTime;
use common\models\db\ProductArea;
use yii\data\Pagination;
use Yii;

class ProductAreaIndexForm extends Model {

    public $time_created_from;
    public $time_created_to;
    public $name;
    public $status;

    //tìm vùng
    public $city;

    public $pageSize = 10;
    public $page = 1;

    public function rules()
    {
        return [
            [['pageSize', 'page', 'status','city'], 'integer'],
            [['name'], 'string'],
            [['time_created_from', 'time_created_to'], 'date'],
        ];
    }

    public function search(&$pagging) {
        $conditions = array();
        if ($this->city > 0) {
            $conditions[] = "id IN (SELECT product_area_id FROM product_area_zone WHERE zone_id = ".$this->city.") ";
        }
        if ($this->time_created_from != null && trim($this->time_created_from) != "") {
            if (!Validation::isDate($this->time_created_from)) {
                $errors[] = 'Ngày tạo từ không đúng định dạng';
            } else {
                $fromdate = FormatDateTime::toTimeBegin($this->time_created_from);
                $conditions[] = "time_created >= $fromdate ";
            }
        }
        if ($this->time_created_to != null && trim($this->time_created_to) != "") {
            if (!Validation::isDate($this->time_created_to)) {
                $errors[] = 'Ngày tạo đến không đúng định dạng';
            } else {
                $todate = FormatDateTime::toTimeEnd($this->time_created_to);
                $conditions[] = "time_created <= $todate ";
            }
        }
        if ($this->name != null && trim($this->name) != "") {
            $conditions[] = "name LIKE '%".trim($this->name)."%' ";
        }
        if ($this->status > 0) {
            $conditions[] = "status = ".$this->status." ";
        }
        if (!empty($conditions)) {
            $conditions = implode(' AND ',$conditions);
        } else {
            $conditions = "1";
        }
        //-----
        $pagging = new Pagination(['totalCount' => Tables::selectCountDataTable("product_area", $conditions)]);
        $pagging->setPageSize($this->pageSize);
        $pagging->page = $this->page;
        //--------
        $offset = ($this->page - 1) * $this->pageSize;
        $product_area_info = Tables::selectAllDataTable("product_area", $conditions, "time_updated DESC ", "id", $this->pageSize, $offset);
        if ($product_area_info != false) {
            $product_area_info = ProductArea::setRows($product_area_info);
        }        
        return $product_area_info;
    }
} 