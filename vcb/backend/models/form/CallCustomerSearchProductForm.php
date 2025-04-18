<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use common\models\db\Product;
use common\models\db\ProductCategory;
use common\models\db\CallHistory;
use common\models\db\Producer;
use common\models\business\CustomerInterestBusiness;

class CallCustomerSearchProductForm extends CallCustomerBasicForm {

    public $product_category_id = null;
    public $producer_id = null;
    public $max_price = null;
    public $min_price = null;
    public $keyword = null;
    public $title = 'Tìm sản phẩm';

    public function rules() {
        return [
            [['product_category_id', 'producer_id', 'max_price', 'min_price'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels() {
        return array(
            'product_category_id' => 'Nhóm sản phẩm',
            'producer_id' => 'Nhà sản xuất',
            'min_price' => 'Mức giá từ',
            'max_price' => 'Mức giá đến',
            'keyword' => 'Tên/Mã sản phẩm',
        );
    }

    public function getProductCategories() {
        $product_category_info = Tables::selectAllDataTable("product_category", "status = " . ProductCategory::STATUS_ACTIVE, "`left` ASC ");
        if ($product_category_info != false) {
            $result = array('' => '-- Nhóm sản phẩm --');
            foreach ($product_category_info as $row) {
                $result[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
            return $result;
        }
        return array();
    }

    public function getListProduct() {
        $conditions = array();
        $now = time();
        if (intval($this->min_price) > 0 && intval($this->max_price) > 0) {
            $conditions[] = "id IN (SELECT product_id FROM product_price WHERE price >= " . $this->min_price . " AND price <= " . $this->max_price . " AND time_begin <= $now AND (time_end >= $now OR time_end = 0))";
        } elseif (intval($this->min_price) > 0) {
            $conditions[] = "id IN (SELECT product_id FROM product_price WHERE price >= " . $this->min_price . " AND time_begin <= $now AND (time_end >= $now OR time_end = 0))";
        } elseif (intval($this->max_price) > 0) {
            $conditions[] = "id IN (SELECT product_id FROM product_price WHERE price <= " . $this->max_price . " AND time_begin <= $now AND (time_end >= $now OR time_end = 0))";
        }
        if (intval($this->product_category_id) != 0) {
            $product_category_info = Tables::selectOneDataTable("product_category", "id = " . $this->product_category_id . " ");
            if ($product_category_info != false) {
                $conditions[] = "product_category_id IN (SELECT id FROM product_category WHERE `left` >= " . $product_category_info['left'] . " AND `right` <= " . $product_category_info['right'] . " AND status = " . ProductCategory::STATUS_ACTIVE . " ) ";
            } else {
                return false;
            }
        }
        if (intval($this->producer_id) != 0) {
            $conditions[] = "producer_id = " . $this->producer_id;
        }
        if (trim($this->keyword) != '') {
            $conditions[] = "(name LIKE '%" . trim($this->keyword) . "%' OR code LIKE '%" . trim($this->keyword) . "%') ";
        }
        if (!empty($conditions)) {
            $conditions = implode(' AND ', $conditions) . " AND status = " . Product::STATUS_ACTIVE . " AND publish = " . Product::PUBLISH;
            $product_info = Tables::selectAllDataTable("product", $conditions, "position ASC ", "id");
            if ($product_info != false) {
                return Product::setRows($product_info);
            }
        }
        return false;
    }

    public function getProducers() {
        $producer_info = Tables::selectAllDataTable("producer", "status = " . Producer::STATUS_ACTIVE);
        if ($producer_info != false) {
            return Weblib::getArraySelectBoxForData($producer_info, 'id', 'name', array('' => '-- Nhà sản xuất --'));
        }
        return array();
    }

    public function getListPrice($min_price = 0) {
        $result = array('0' => '-- Chọn mức giá --');
        for ($i = 1000000; $i < 50000000; $i = $i + 1000000) {
            if ($i > $min_price) {
                $result[$i] = \common\components\utils\ObjInput::makeCurrency($i);
            }
        }
        return $result;
    }

}
