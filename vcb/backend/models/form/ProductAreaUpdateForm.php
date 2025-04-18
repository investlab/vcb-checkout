<?php

namespace backend\models\form;

use yii\base\Model;
use common\components\libs\Tables;
use Yii;

class ProductAreaUpdateForm extends ProductAreaAddForm {
    
    public function setZoneIds() {
        $product_area_zone_info = Tables::selectAllDataTable("product_area_zone", "product_area_id = ".$this->id);
        if ($product_area_zone_info != false) {
            foreach($product_area_zone_info as $row) {
                $this->zone_ids[] = $row['zone_id'];
            }
        }
    }
    
}
