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
use common\models\business\CustomerInterestBusiness;

class CallCustomerAddCustomerInterestForm extends CallCustomerBasicForm {

    public $product_category_id = null;
    public $product_id = null;
    public $max_price = null;
    public $min_price = null;
    public $note = null;
    public $title = 'Nhu cầu';   

    public function rules() {
        return [
            [['product_category_id'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['product_category_id', 'product_id', 'max_price', 'min_price'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['note'], 'string'],
        ];
    }

    public function attributeLabels() {
        return array(
            'product_category_id' => 'Nhóm sản phẩm',
            'product_id' => 'Sản phẩm',
            'min_price' => 'Mức giá từ',
            'max_price' => 'Mức giá đến',
            'note' => 'Ghi chú',
        );
    }

    public function getProductCategories() {
        $product_category_info = Tables::selectAllDataTable("product_category", "status = " . ProductCategory::STATUS_ACTIVE, "`left` ASC ");
        if ($product_category_info != false) {
            $result = array('' => '-- Chọn nhóm sản phẩm --');
            foreach ($product_category_info as $row) {
                $result[$row['id']] = str_repeat('--', $row['level'] - 1) . ' ' . $row['name'];
            }
            return $result;
        }
        return array();
    }

    public function getProducts($product_category_id) {
        if (intval($product_category_id != 0)) {
            $product_category_info = Tables::selectOneDataTable("product_category", "id = $product_category_id ");
            if ($product_category_info != false) {
                $product_info = Tables::selectAllDataTable("product", "product_category_id IN (SELECT id FROM product_category WHERE `left` >= " . $product_category_info['left'] . " AND `right` <= " . $product_category_info['right'] . " AND status = " . ProductCategory::STATUS_ACTIVE . " ) AND status = " . Product::STATUS_ACTIVE);
                if ($product_info != false) {
                    return Weblib::getArraySelectBoxForData($product_info, 'id', array('name', 'code'), array('' => ''));
                }
            }
        }
        return array();
    }

    public function getListPrice($min_price = 0) {
        $result = array(0 => '-- Chọn mức giá --');
        $list_price = \common\models\db\CustomerInterest::getListPrice();
        foreach ($list_price as $price) {
            if ($price > $min_price) {
                $result[$price] = \common\components\utils\ObjInput::makeCurrency($price);
            }
        }
        return $result;
    }
    
    function submit() {
        if ($this->call_history_info != false && $this->call_history_info['status'] == CallHistory::STATUS_CALLED) {
            $inputs = array(
                'call_history_id' => $this->call_history_info['id'], 
                'product_category_id' => $this->product_category_id, 
                'product_id' => $this->product_id, 
                'min_price' => $this->min_price, 
                'max_price' => $this->max_price, 
                'note' => $this->note, 
                'user_id' => Yii::$app->user->getId(),
            );
            $result = CustomerInterestBusiness::addByCallHistory($inputs);
        } else {
            $inputs = array(
                'customer_id' => $this->customer_info['id'], 
                'product_category_id' => $this->product_category_id, 
                'product_id' => $this->product_id, 
                'min_price' => $this->min_price, 
                'max_price' => $this->max_price, 
                'note' => $this->note, 
                'user_id' => Yii::$app->user->getId(),
            );
            $result = CustomerInterestBusiness::addByCustomer($inputs);
        }
        if ($result['error_message'] == '') {
            $this->addMessage('Ghi nhận nhu cầu thành công');
            $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index','option'=>$this->key, 'call_history_id'=>@$this->call_history_info['id'], 'customer_id' => $this->customer_info['id']]);
            header('Location:'.$url);
            die();
        } else {
            $this->error = $result['error_message'];
        }
    }
}
