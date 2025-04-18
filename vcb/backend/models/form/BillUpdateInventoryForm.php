<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\components\libs\Tables;

class BillUpdateInventoryForm extends Bill {
    
    public $supplier_inventory_id = null;
    public $supplier_id = null;
    public $supplier_order_code = null;
    
    public function rules() {
        return [
            [['supplier_inventory_id', 'supplier_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['supplier_inventory_id', 'supplier_id'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'supplier_inventory_id' => 'Kho hàng',
            'supplier_id' => 'Nhà cung cấp',
            'product_quantity' => 'Số lượng sản phẩm',
            'supplier_order_code' => 'Mã đơn hàng NCC',
        );
    }
    
    public function getItems() {
        $result = array();
        $bill_item_info = Tables::selectAllDataTable("bill_item", "bill_id = ".$this->id);
        if ($bill_item_info != false) {
            foreach ($bill_item_info as $row) {
                $result[$row['id']] = array();
                $result[$row['id']][] = array(
                    'inventory_type' => 1,
                    'inventory_id' => $this->supplier_inventory_id,
                    'product_quantity' => $row['product_quantity'],
                    'supplier_order_code' => strval($this->supplier_order_code),
                );
            }
        }        
        return $result;
    }
}
