<?php
namespace backend\models\form;
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
use yii\db\Query;

class ProductPriceListForm extends \common\models\db\ProductPrice{
    public $product_code = null;
    public $product_name = null;
    public $product_category_id = null;
    public $status = null;
    public $filter = null;
    public $zone_id = null;
    public $pageSize = 20;
    
    public function rules() {
        return [
            [['product_code', 'product_name'], 'string'],
            [['status','product_category_id','filter','zone_id'], 'integer'],
        ];
    }
    
    public function search() {
        
        $page = intval(Yii::$app->request->get('page'));
        $paging = new Pagination();
        $paging->setPageSize($this->pageSize);
        $paging->setPage($page <= 0 ? 0 : ($page - 1));
        //---------
        $conditions = $this->getConditions();
        $query = (new Query())->select('*')->from($this->tableName())->where($conditions)->orderBy("id DESC ");
        $paging->totalCount = $query->count();
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());
        $dataPage = new DataPage();
        $dataPage->data = $query->all();
        $dataPage->pagination = $paging;
        if ($dataPage->data != false) {
            $this->_setStatus($dataPage->data);
            User::setUsernameForRows($dataPage->data);
        }
        return $dataPage;
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

        $product_category_ids = array();
        foreach ($product_info as $key=>$row) {
            $product_category_ids[$row['product_category_id']] = $row['product_category_id'];       
        }
        $product_category_info = Tables::selectAllDataTable("product_category", "id IN (".implode(',', $product_category_ids).")", "", "id");        
        $index = 0;
        $status_array = self::getStatus();
        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['status_name'] = $status_array[$row['status']];
            $data[$key]['status_class'] = $this->_getStatusClass($row['status']);
            $data[$key]['operators'] = $this->getOperatorsByStatus($row);
            $data[$key]['product_category_name'] = Strings::strip(@$product_category_info[@$product_info[$row['product_id']]['product_category_id']]['name']);
//            $data[$key]['product_name'] = String::strip(@$product_info[$row['product_id']]['name']);
//            $data[$key]['product_code'] = String::strip(@$product_info[$row['product_id']]['code']);
            $data[$key]['product_info'] = @$product_info[$row['product_id']];
            $data[$key]['buy_price'] = self::getBuyPrice($row);
            $data[$key]['user_updated_price'] = $user_updated_prices_info[$row['user_created']]['username'];
            $data[$key]['zone_price'] = Strings::strip(@$zone_prices_info[$row['zone_id']]['name']);
        }
//        var_dump($data);die;
    }

    protected function _getStatusClass($status) {
        switch ($status) {
            case self::STATUS_NOT_REQUEST:
                return 'label label-default';
                break;
            case self::STATUS_REQUEST:
                return 'label label-warning';
                break;
            case self::STATUS_REJECT:
                return 'label label-danger';
                break;
            case self::STATUS_ACTIVE:
                return 'label label-success';
                break;
            case self::STATUS_LOCK:
                return 'label label-danger';
                break;
        }
    }
    
    public function getConditions() {
        $conditions = array();
        $product_conditions = array();
        if ($this->product_category_id != 0) {
            $product_category_ids = ProductCategory::getSubIdsByParentId($this->product_category_id);
            if (!empty($product_category_ids)) {
                $product_conditions[] = "product_category_id IN (".implode(',', $product_category_ids).") ";
            }
        }
        if ($this->product_name != '') {
            $product_conditions[] = "name LIKE '%".$this->product_name."%'";
        }
        if ($this->product_code != '') {
            $product_conditions[] = "code LIKE '%".$this->product_code."%'";
        }
        if (!empty($product_conditions)) {
            $product_ids = Product::getProductIds(implode(' AND ', $product_conditions));
            if (!empty($product_ids)) {
                $conditions[] = "product_id IN (".implode(',', $product_ids).") ";
            } else {
                return 0;
            }
        }
        if ($this->status != 0) {
            $conditions[] = "status = ".$this->status;
        }
        if($this->filter > 0){
            $conditions[] = " time_begin <= ".time()." AND (time_end = 0 OR time_end >".time().")";
        }

        if ($this->zone_id != 0) {
            $conditions[] = "zone_id = ".$this->zone_id;
        }
        
        if (!empty($conditions)) {
            $str_conditions = implode(' AND ', $conditions);
        } else {
            $str_conditions = "1";
        }
        return $str_conditions;
    }
}
