<?php


namespace backend\models\form;


use common\components\libs\Tables;
use common\components\utils\Strings;
use common\models\db\ProductAreaProduct;
use common\models\db\ProductImage;
use common\models\db\ProductPrice;
use Yii;
use yii\db\Query;

class ProductExportForm extends ProductListForm {

    public function search() {
        $conditions = $this->getConditions();
        $query = (new Query())->select('*')
            ->from($this->tableName())
            ->where($conditions)
            ->orderBy("id DESC ");
        $data = $query->all();
        if ($data != false) {
            $this->setRows($data);
        }
        return $data;
    }

    public static function setRows(&$data) {
        $product_category_ids = array();
        $producer_ids = array();
        $product_ids = array();
        foreach ($data as $key=>$row) {
            $product_category_ids[$row['product_category_id']] = $row['product_category_id'];
            $producer_ids[$row['producer_id']] = $row['producer_id'];
            $product_ids[$row['id']] = $row['id'];
        }
        $product_category_info = Tables::selectAllDataTable("product_category", "id IN (".implode(',', $product_category_ids).")", "", "id");


        $parent_level2 = array();
        foreach($product_category_info as $keyPCI => $dataPCI){
            $parent_level2[$dataPCI['id']] = Tables::selectAllDataTable("product_category","`left` <= ". $dataPCI['left']." AND `right` >= ".$dataPCI['right']. " AND `level` = 2");
        }
        $producer_info = Tables::selectAllDataTable("producer", "id IN (".implode(',', $producer_ids).")", "", "id");
        $product_price_info = ProductPrice::getPriceInfoByProductIds($product_ids, time());
        $product_image_info = ProductImage::getImageInfoByProductIds($product_ids);
        $index = 0;
        $status_array = self::getStatus();
        $publish_array = self::getPublishName();

        // khu vực
        $product_zones = array();
        $product_areas = array();
        $product_area_product_info = Tables::selectAllDataTable("product_area_product", "product_id IN (".implode(',', $product_ids).") AND status = ".ProductAreaProduct::STATUS_ACTIVE);
        if ($product_area_product_info != false) {
            $product_area_product_info = ProductAreaProduct::setRows($product_area_product_info);
            foreach ($product_area_product_info as $item) {
                $product_areas[$item['product_id']][$item['product_area_id']] = $item;
                foreach ($item['product_area_info']['zones'] as $sub_item) {
                    $sub_item['quantity'] = $item['quantity'];
                    $product_zones[$item['product_id']][$sub_item['zone_id']] = $sub_item;
                }
            }
        }

        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['name'] = Strings::strip($row['name']);
            $data[$key]['description'] = Strings::strip($row['description']);
            $data[$key]['status_name'] = $status_array[$row['status']];
            $data[$key]['status_class'] = self::_getStatusClass($row['status']);
            $data[$key]['publish_name'] = $publish_array[$row['publish']];
            $data[$key]['publish_class'] = self::_getPublishClass($row['publish']);
            $data[$key]['operators'] = self::getOperatorsByStatus($row);
            $data[$key]['product_category_name'] = Strings::strip(@$product_category_info[$row['product_category_id']]['name']);
            $data[$key]['product_category_parent_name_lv2'] = @$parent_level2[$row['product_category_id']][0]['name'];
            $data[$key]['product_category_info'] = @$product_category_info[$row['product_category_id']];
            $data[$key]['producer_name'] = Strings::strip(@$producer_info[$row['producer_id']]['name']);
            $data[$key]['producer_info'] = @$producer_info[$row['producer_id']];
            $data[$key]['buy_price'] = intval(@$product_price_info[$row['id']]['buy_price']);
            $data[$key]['price_info'] = @$product_price_info[$row['id']];
            $data[$key]['image'] = @$product_image_info[$row['id']]['image_default']['image'];
            $data[$key]['image_small'] = @$product_image_info[$row['id']]['image_default']['image_small'];
            $data[$key]['images'] = @$product_image_info[$row['id']]['images'];
            $data[$key]['link'] = Yii::$app->urlManager->createAbsoluteUrl(['product/detail', 'id' => $row['id'], 'name' => Strings::convertNameForUrl($row['name'])]);
            $data[$key]['product_zones'] = isset($product_zones[$row['id']]) ? $product_zones[$row['id']] : array();
            $data[$key]['product_areas'] = isset($product_areas[$row['id']]) ? $product_areas[$row['id']] : array();
        }
        return $data;
    }
} 