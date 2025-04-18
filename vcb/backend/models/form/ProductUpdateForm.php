<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use backend\models\form\ProductAddForm;
use common\components\libs\Tables;
use common\models\db\Product;

class ProductUpdateForm extends ProductAddForm {
    
    public function beforeSave($insert) {
       // $this->new = 0;
        $this->promotion = 0;
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
    
    public function getProductAreaProducts() {
        $product_area_products = parent::getProductAreaProducts();
        if (empty($product_area_products)) {
            $product_area_products = $this->_getProductAreaProducts();
            foreach ($product_area_products as $key => $row) {
                $this->product_area_quantities[$key] = $row['quantity'];
                $this->product_area_ids[$key] = $row['product_area_id'];
                $this->product_area_status[$key] = $row['status'];
            }
        }
        return $product_area_products;
    }
    
    public function getListKeywords() {
        return Product::getKeywordsByTags($this->tags);
    }
}
