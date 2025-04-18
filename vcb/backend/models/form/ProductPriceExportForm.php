<?php
namespace backend\models\form;
use common\models\db\ProductAreaZone;
use yii\base\Model;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\User;
use common\models\output\DataPage;
use yii\data\Pagination;
use common\models\db\ProductPrice;
use common\components\utils\Strings;
use Yii;
use common\components\utils\ObjInput;
use common\models\db\Product;
use common\models\db\ProductCategory;

class ProductPriceExportForm extends ProductPriceListForm {
    
    public function search() {
        $conditions = $this->getConditions();
        $query = (new \yii\db\Query())->select('*')
                ->from($this->tableName())
                ->where($conditions)
                ->orderBy("id DESC ");
        $data = $query->all();
        if ($data != false) {
            $this->_setStatus($data);
            User::setUsernameForRows($data);
        }
        return $data;
    }
    
    protected function _setStatus(&$data) {
        $product_ids = array();
        $user_updated_prices = array();
        $zone_prices = array();
        foreach ($data as $key=>$row) {
            $product_ids[$row['product_id']] = $row['product_id'];
            $user_updated_prices[$row['user_created']] = $row['user_created'];
            $zone_prices[$row['zone_id']] = $row['zone_id'];
        }
        $product = Tables::selectAllDataTable("product", "id IN (".implode(',', $product_ids).")", "", "id");
        $product_info = Product::setRows($product);
        $user_updated_prices_info = Tables::selectAllDataTable("user", "id IN (".implode(',', $user_updated_prices).")", "", "id");
        $zone_prices_info = Tables::selectAllDataTable("zone", "id IN (".implode(',', $zone_prices).")", "", "id");
        $product_area_zone = Tables::selectAllDataTable("product_area_zone", "zone_id IN (".implode(',', $zone_prices).")", "", "zone_id");
        $product_area_zone_info = ProductAreaZone::setRows($product_area_zone);

        $product_category_ids = array();
        $user_created_products = array();
        foreach ($product_info as $key=>$row) {
            $product_category_ids[$row['product_category_id']] = $row['product_category_id'];
            $user_created_products[$row['user_created']] = $row['user_created'];
        }
        $product_category_info = Tables::selectAllDataTable("product_category", "id IN (".implode(',', $product_category_ids).")", "", "id");
        $user_created_products_info = Tables::selectAllDataTable("user", "id IN (".implode(',', $user_created_products).")", "", "id");

        $index = 0;
        $status_array = self::getStatus();

        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['status_name'] = $status_array[$row['status']];
            $data[$key]['status_class'] = $this->_getStatusClass($row['status']);
            $data[$key]['operators'] = $this->getOperatorsByStatus($row);
            $data[$key]['product_category_name'] = Strings::strip(@$product_category_info[@$product_info[$row['product_id']]['product_category_id']]['name']);
            $data[$key]['product_name'] = Strings::strip(@$product_info[$row['product_id']]['name']);
            $data[$key]['product_code'] = Strings::strip(@$product_info[$row['product_id']]['code']);
            $data[$key]['product_category_parent_name'] = Strings::strip(@$product_info[$row['product_id']]['product_category_parent_name']);
            $data[$key]['product_category_parent_name_lv2'] = Strings::strip(@$product_info[$row['product_id']]['product_category_parent_name_lv2']);
            $data[$key]['zone_name'] = Strings::strip(@$product_area_zone_info[$row['zone_id']]['zone_info']['product_area_name']);
            $data[$key]['product_info'] = $product_info[$row['product_id']];
            $data[$key]['buy_price'] = self::getBuyPrice($row);
            $data[$key]['time_begin'] = date('d-m-Y H:i', $row['time_begin']);
            $data[$key]['user_created_product'] = $user_created_products_info[@$product_info[$row['product_id']]['user_created']]['username'];
            $data[$key]['user_updated_price'] = $user_updated_prices_info[$row['user_created']]['username'];
            $data[$key]['zone_price'] = $zone_prices_info[$row['zone_id']]['name'];
        }
    }
}
