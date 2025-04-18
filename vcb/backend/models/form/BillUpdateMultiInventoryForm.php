<?php
namespace backend\models\form;
use common\components\utils\Validation;
use yii\base\Model;
use Yii;
use common\models\db\MyActiveRecord;
use common\models\db\Bill;
use common\models\db\SupplierProduct;
use common\models\db\Supplier;
use common\models\db\SupplierProductPrice;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class BillUpdateMultiInventoryForm extends MyActiveRecord {
    
    public $supplier_inventory_id = null;
    public $supplier_id = null;
    public $supplier_order_code = null;
    public $bill_ids = null;
    
    public function rules() {
        return [
            [['supplier_inventory_id', 'supplier_id'], 'isArrayInteger', 'message' => '{attribute} không hợp lệ'],
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
    
    public function isArrayInteger($attribute, $params) {
        if (!empty($this->$attribute) && !Validation::isArrayInteger($this->$attribute)) {
            $labels = $this->attributeLabels();
            $this->addError($attribute, $labels[$attribute].' không hợp lệ');
        }
    }
    
    public function getBills($ids) {
        $bills = Tables::selectAllDataTable("bill", "id IN (" . implode(',', $ids) . ") AND payment_status = " . Bill::PAYMENT_STATUS_PAID . " AND ship_status IN (" . Bill::SHIP_STATUS_NOT_ORDER . "," . Bill::SHIP_STATUS_NOT_DELIVERY . "," . Bill::SHIP_STATUS_CANCEL_DELIVERY . ")", "", "id");
        if ($bills != false) {
            $this->bill_ids = array_keys($bills);
            $bill_item_info = Tables::selectAllDataTable("bill_item", "bill_id IN (" . implode(',', $this->bill_ids). ") ");
            if ($bill_item_info != false) {
                foreach ($bill_item_info as $bill_item) {
                    $product_ids[$bill_item['product_id']] = $bill_item['product_id'];
                    $bills[$bill_item['bill_id']]['items'][] = $bill_item;
                    $bills[$bill_item['bill_id']]['suppliers'] = array();
                    $bills[$bill_item['bill_id']]['supplier_inventories'] = array();
                }
                $supplier_product_info = Tables::selectAllDataTable("supplier_product", "product_id IN (".implode(',', $product_ids).") AND status = ".SupplierProduct::STATUS_ACTIVE." ");
                if ($supplier_product_info != false) {
                    $supplier_ids = array();
                    $supplier_product_map_ids = array();
                    foreach ($supplier_product_info as $row) {
                        $supplier_ids[$row['supplier_id']] = $row['supplier_id'];
                        $supplier_product_map_ids[$row['product_id']][$row['supplier_id']] = $row['supplier_id'];
                    }
                    $supplier_info = Tables::selectAllDataTable("supplier", "id IN (".implode(',', $supplier_ids).") AND status = ".Supplier::STATUS_ACTIVE, "", "id");
                    
                    if ($supplier_info != false) {
                        foreach ($bill_item_info as $bill_item) {
                            if (isset($supplier_product_map_ids[$bill_item['product_id']]) && !empty($supplier_product_map_ids[$bill_item['product_id']])) {
                                foreach ($supplier_product_map_ids[$bill_item['product_id']] as $supplier_id) {
                                    if (isset($supplier_info[$supplier_id]) && !empty($supplier_info[$supplier_id])) {
                                        $bills[$bill_item['bill_id']]['suppliers'][$supplier_id] = $supplier_info[$supplier_id]['name'];
                                    }
                                }
                            }
                        }
                    }                        

                }
            }
        }
        return $bills;
    }
    
    public function getSupplierProductPrices(&$bills) {
        $supplier_product_prices = array();
        $form_supplier_inventory_id = $this->supplier_inventory_id;
        if (!empty($form_supplier_inventory_id) && \common\components\utils\Validation::isArrayInteger($form_supplier_inventory_id)) {
            $supplier_inventory_info = Tables::selectAllDataTable("supplier_inventory", "id IN (".implode(',', $form_supplier_inventory_id).")", "", "id");
            if ($supplier_inventory_info != false) {
                foreach ($form_supplier_inventory_id as $bill_id => $supplier_inventory_id) {
                    $form_supplier_id[$bill_id] = $supplier_inventory_info[$supplier_inventory_id]['supplier_id'];
                }
            }
            $this->supplier_id = $form_supplier_id;
        } else {
            $form_supplier_id = $this->supplier_id;
        }
        if (!empty($form_supplier_id) && \common\components\utils\Validation::isArrayInteger($form_supplier_id)) {
            $supplier_inventory_info = Tables::selectAllDataTable("supplier_inventory", "supplier_id IN (".implode(',', $form_supplier_id).")");
            if ($supplier_inventory_info != false) {
                foreach ($form_supplier_id as $bill_id => $supplier_id) {
                    foreach ($supplier_inventory_info as $row) {
                        if ($row['supplier_id'] == $supplier_id) {
                            $bills[$bill_id]['supplier_inventories'][$row['id']] = $row['inventory_name'];
                        }
                    }
                    //---------
                    if (intval(@$form_supplier_inventory_id[$bill_id]) != 0) {
                        $product_id = $bills[$bill_id]['items'][0]['product_id'];
                        $supplier_product_info = Tables::selectOneDataTable("supplier_product", "supplier_id = $supplier_id AND product_id = $product_id ");
                        if ($supplier_product_info != false) {
                            $price = SupplierProductPrice::getPrice($supplier_product_info['id'], time());
                            $supplier_product_prices[$bill_id][$supplier_id] = ObjInput::makeCurrency($price);
                        }
                    }
                }
            }
        }
        return $supplier_product_prices;
    }
    
    public function getItems() {
        $result = array();
        if (!empty($this->bill_ids)) {
            foreach ($this->bill_ids as $bill_id) {
                $bill_item_info = Tables::selectAllDataTable("bill_item", "bill_id = ".$bill_id);
                if ($bill_item_info != false) {
                    foreach ($bill_item_info as $row) {
                        $result[$bill_id][$row['id']] = array();
                        $result[$bill_id][$row['id']][] = array(
                            'inventory_type' => 1,
                            'inventory_id' => $this->supplier_inventory_id[$bill_id],
                            'product_quantity' => $row['product_quantity'],
                            'supplier_order_code' => '',
                        );
                    }
                }    
            }
        }
        return $result;
    }
}
