<?php

namespace backend\models\form;

use yii\base\Model;
use common\models\db\ProductArea;
use common\components\utils\Validation;
use common\components\libs\Tables;
use common\components\libs\Weblib;
use Yii;

class ProductAreaAddForm extends ProductArea {

    public $zone_ids = null;
    
    public function rules() {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255, 'message' => '{attribute} không hợp lệ'],
            [['zone_ids'], 'isArrayInteger', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels() {
        return [
            'name' => 'Tên khu vực giao hàng',
            'zone_ids' => 'Tỉnh thành',
        ];
    }  
    
    public function isArrayInteger($attribute, $params) {
        if (!empty($this->$attribute) && !Validation::isArrayInteger($this->$attribute)) {
            $labels = $this->attributeLabels();
            $this->addError($attribute, $labels[$attribute].' không hợp lệ');
        }
    }
    
    public function getZoneNotSelected() {
        $zone_info = Tables::selectAllDataTable("zone", "id NOT IN (SELECT zone_id FROM product_area_zone) AND level = 2 AND status = ".\common\models\db\Zone::STATUS_ACTIVE, "code ASC ");
        if ($zone_info != false) {
            $result = array();
            foreach ($zone_info as $row) {
                $result[$row['id']] = \common\components\utils\Strings::strip($row['name']);
            }
            return $result;
        }
        return false;
    }
    
    public function getProductAreaZones() {
        $result = array();
        if (!empty($this->zone_ids)) {
            $zone_info = Tables::selectAllDataTable("zone", "id IN (".implode(',', $this->zone_ids).") AND level = 2 AND status = ".\common\models\db\Zone::STATUS_ACTIVE, "code ASC ");
            if ($zone_info != false) {
                return Weblib::getArraySelectBoxForData($zone_info, "id", "name");
            }
        }
        return $result;
    }
}
