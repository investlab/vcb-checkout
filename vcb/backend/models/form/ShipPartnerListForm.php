<?php
namespace backend\models\form;
use yii\base\Model;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\User;

class ShipPartnerListForm extends \common\models\db\ShipPartner{
    public $time_created_from = null;
    public $time_created_to = null;
    public $keyword = null;
    public $status = null;
    
    public function rules() {
        return [
            [['keyword'], 'string'],
            [['status'], 'integer'],
            [['time_created_from', 'time_created_to'], 'safe'],
            [['time_created_from', 'time_created_to'], 'date', 'format' => 'dd-mm-yyyy', 'message' => 'Thời gian không hợp lệ'],
        ];
    }
    
    public function search() {
        $data = Tables::selectAllDataTable(self::tableName(), $this->getConditions(), "id DESC ");
        if ($data != false) {
            $this->_setStatus($data);
            $this->_setListSupplier($data);
            User::setUsernameForRows($data);
        }
        return $data;
    }
    
    protected function _setListSupplier(&$data) {
        $ship_partner_ids = array();
        foreach ($data as $key=>$row) {
            $ship_partner_ids[$row['id']] = $row['id'];
        }
        $ship_partner_suppliers = array();
        $ship_partner_supplier_info = Tables::selectAllDataTable("ship_partner_supplier", "ship_partner_id IN (".implode(',', $ship_partner_ids).") ");
        if ($ship_partner_supplier_info != false) {
            $supplier_ids = array();
            foreach ($ship_partner_supplier_info as $row) {
                $supplier_ids[$row['supplier_id']] = $row['supplier_id'];
                $ship_partner_suppliers[$row['ship_partner_id']] = array();
            }
            $suppliers = array();
            $supplier_info = Tables::selectAllDataTable("supplier", "id IN (".implode(',', $supplier_ids).") ");
            if ($supplier_info != false) {
                foreach ($supplier_info as $row) {
                    $suppliers[$row['id']] = $row;
                }
            }
            foreach ($ship_partner_supplier_info as $row) {
                if (array_key_exists($row['supplier_id'], $suppliers)) {
                    $ship_partner_suppliers[$row['ship_partner_id']][$row['supplier_id']] = $suppliers[$row['supplier_id']]['name'];
                }
            }
        }
        foreach ($data as $key=>$row) {
            if (isset($ship_partner_suppliers[$row['id']])) {
                $data[$key]['list_supplier'] = implode(',<br/>', $ship_partner_suppliers[$row['id']]);
            } else {
                $data[$key]['list_supplier'] = '<font color="#F60">Tất cả NCC</font>';
            }
        }
    }
    
    protected function _setStatus(&$data) {
        $index = 0;
        $status_array = self::getStatus();
        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['status_name'] = $status_array[$row['status']];
            $data[$key]['status_class'] = $this->_getStatusClass($row['status']);
            $data[$key]['operators'] = $this->getOperatorsByStatus($row);
        }
    }
    
    protected function _getStatusClass($status) {
        if ($status == self::STATUS_ACTIVE) {
            return 'label label-success';
        }
        return 'label label-danger';
    }
    
    public function getConditions() {
        $conditions = array();
        if ($this->time_created_from != '') {
            $fromdate = FormatDateTime::toTimeBegin($this->time_created_from);
            $conditions[] = "time_created >= $fromdate ";
        }
        if ($this->time_created_to != '') {
            $todate = FormatDateTime::toTimeEnd($this->time_created_to);
            $conditions[] = "time_created <= $todate ";
        }
        if ($this->keyword != '') {
            $conditions[] = "name LIKE '%".$this->keyword."%' OR code LIKE '%".$this->keyword."%'";
        }
        if ($this->status != 0) {
            $conditions[] = "status = ".$this->status;
        }        
        if (!empty($conditions)) {
            return implode(' AND ', $conditions);
        }
        return "1";
    }
    
    public function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                //$result['detail'] = $operators['detail'];
                $result['update'] = $operators['update'];
                $result['lock'] = $operators['lock'];
                $result['update-product-category'] = $operators['update-product-category'];
                $result['update-supplier'] = $operators['update-supplier'];
                break;
            case self::STATUS_LOCK:
                //$result['detail'] = $operators['detail'];
                $result['update'] = $operators['update'];
                $result['active'] = $operators['active'];
                $result['update-product-category'] = $operators['update-product-category'];
                $result['update-supplier'] = $operators['update-supplier'];
                break;
        }
        return $result;
    }
    
    public static function getOperators() {
        return array(
            'detail' => array('title' => 'Xem chi tiết', 'confirm' => false),
            'update' => array('title' => 'Cập nhật', 'confirm' => false),
            'lock' => array('title' => 'Khóa', 'confirm' => true),
            'active' => array('title' => 'Mở khóa', 'confirm' => true),
            'update-product-category' => array('title' => 'Danh mục SP hỗ trợ', 'confirm' => false),
            'update-supplier' => array('title' => 'Danh sách NCC hỗ trợ', 'confirm' => false),
        );
    }
}
