<?php
namespace backend\models\form;
use common\models\db\ProductArea;
use common\models\db\ProductAreaProduct;
use common\models\db\Zone;
use yii\base\Model;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\User;
use common\models\output\DataPage;
use yii\data\Pagination;
use common\models\db\ProductPrice;
use common\models\db\ProductImage;
use common\models\db\ProductCategory;
use common\components\utils\Strings;
use Yii;

class ProductSearchForm extends ProductListForm{
    
    public function getConditions() {

        $conditions = array();
        if ($this->product_category_id != 0) {
            $product_category_ids = ProductCategory::getSubIdsByParentId($this->product_category_id);
            if (!empty($product_category_ids)) {
                $conditions[] = "product_category_id IN (".implode(',', $product_category_ids).") ";
            }
        }
        if ($this->keyword != '') {
            $conditions[] = "(name LIKE '%".$this->keyword."%' OR code LIKE '%".$this->keyword."%')";
        }
        if ($this->status != 0) {
            $conditions[] = "status = ".$this->status;
        }        
        if ($this->publish != 0) {
            $conditions[] = "publish = ".$this->publish;
        }

        if($this->zone_id > 0) {
            ProductArea::setSessionProductZone($this->zone_id, Zone::getName($this->zone_id));
        }


        if (!empty($conditions)) {
            return implode(' AND ', $conditions);
        }

        return "1";
    }
}
