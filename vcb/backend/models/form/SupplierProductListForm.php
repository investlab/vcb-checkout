<?php
namespace backend\models\form;
use yii\base\Model;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\User;
use common\models\db\SupplierProduct;
use Yii;

class SupplierProductListForm extends \common\models\db\SupplierProduct {
    
    public function rules() {
        return [
            
        ];
    }
    
    public function search($product_id) {
        $data = Tables::selectAllDataTable(self::tableName(), $this->getConditions($product_id), "id ASC ");
        if ($data != false) {
            $this->_setStatus($data);
            User::setUsernameForRows($data);
        }
        return $data;
    }
    
    protected function _setStatus(&$data) {
        $index = 0;
        $supplier_ids = array();
        $product_ids = array();
        foreach ($data as $row) {
            $supplier_ids[$row['supplier_id']] = $row['supplier_id'];
            $product_ids[$row['product_id']] = $row['product_id'];
        }
        $supplier_info = Tables::selectAllDataTable("supplier", "id IN (".implode(',', $supplier_ids).")", "id ASC ", "id");
        $product_info = Tables::selectAllDataTable("product", "id IN (".implode(',', $product_ids).")", "id ASC ", "id");
        $status_array = self::getStatus();
        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['supplier_code'] = $supplier_info[$row['supplier_id']]['code'];
            $data[$key]['supplier_name'] = $supplier_info[$row['supplier_id']]['name'];
            $data[$key]['product_code'] = $product_info[$row['product_id']]['code'];
            $data[$key]['product_name'] = $product_info[$row['product_id']]['name'];
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
    
    public function getConditions($product_id) {
        return "product_id = ".$product_id;
    }
    
    public function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['update'] = $operators['update'];
                $result['lock'] = $operators['lock'];
                break;
            case self::STATUS_LOCK:
                $result['update'] = $operators['update'];
                $result['active'] = $operators['active'];
                break;
        }
        return $result;
    }
    
    public static function getOperators() {
        return array(
            'update' => array('title' => 'Cập nhật', 'confirm' => false),
            'lock' => array('title' => 'Khóa', 'confirm' => true),
            'active' => array('title' => 'Mở khóa', 'confirm' => true),
        );
    }
}
