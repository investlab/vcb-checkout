<?php
namespace backend\models\form;
use common\models\db\ProductAreaProduct;
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
use yii\db\Query;

class ProductListForm extends \common\models\db\Product{
    public $time_created_from = null;
    public $time_created_to = null;
    public $keyword = null;
    public $status = null;
    public $publish = null;
    public $producer_id = null;
    public $hot = null;
    public $quantity = -1;
    public $zone_id = null;
    
    public $pageSize = 20;
    public $page;
    
    public function rules() {
        return [
            [['keyword'], 'string'],
            [['status','product_category_id','publish','hot','page','quantity','producer_id','zone_id'], 'integer'],
            [['time_created_from', 'time_created_to'], 'safe'],
            [['time_created_from', 'time_created_to'], 'date', 'format' => 'dd-mm-yyyy', 'message' => 'Thời gian không hợp lệ'],
        ];
    }
    
    public function search() {
        $conditions = $this->getConditions();
        $page = intval(Yii::$app->request->get('page'));
        $query = (new Query())->select('*')
                ->from($this->tableName())
                ->where($conditions)
                ->orderBy("id DESC ");
        $paging = new Pagination(['totalCount' => $query->count()]);
        $paging->setPageSize($this->pageSize);
        $paging->setPage($page <= 0 ? 0 : ($page - 1));
        $query->limit($paging->getLimit());
        $query->offset($paging->getOffset());
        $dataPage = new DataPage();
        $dataPage->data = $query->all();
        $dataPage->pagination = $paging;
        if ($dataPage->data != false) {
            self::setRows($dataPage->data);
            User::setUsernameForRows($dataPage->data);
        }
        return $dataPage;
    }
    
    public function getConditions() {
        $conditions = array();
        if ($this->product_category_id != 0) {
            $product_category_ids = ProductCategory::getSubIdsByParentId($this->product_category_id);
            if (!empty($product_category_ids)) {
                $conditions[] = "product_category_id IN (".implode(',', $product_category_ids).") ";
            }
        }
        if ($this->time_created_from != '') {
            $fromdate = FormatDateTime::toTimeBegin($this->time_created_from);
            $conditions[] = "time_created >= $fromdate ";
        }
        if ($this->time_created_to != '') {
            $todate = FormatDateTime::toTimeEnd($this->time_created_to);
            $conditions[] = "time_created <= $todate ";
        }
        if ($this->keyword != '') {
            $conditions[] = "(name LIKE '%".$this->keyword."%' OR code = '".$this->keyword."')";
        }
        if ($this->status != 0) {
            $conditions[] = "status = ".$this->status;
        }        
        if ($this->publish != 0) {
            $conditions[] = "publish = ".$this->publish;
        }

        if($this->zone_id > 0) {
//            var_dump($this->zone_id);die;
            $conditions[] =  "id IN (SELECT product_id FROM product_area_product WHERE product_area_id IN (SELECT product_area_id FROM product_area_zone WHERE zone_id = ". $this->zone_id .") AND quantity > 0 AND status = ".ProductAreaProduct::STATUS_ACTIVE." )" ;
            $conditions[] = "id IN (SELECT DISTINCT product_id FROM product_price WHERE zone_id = ".$this->zone_id." AND status = ".ProductPrice::STATUS_ACTIVE." ) " ;
        }

        if (intval($this->producer_id) > 0) {
            $conditions[] = "producer_id = ".$this->producer_id;
        }
        if (intval($this->quantity) === 0) {
            $conditions[] = "total_quantity <= ".intval($this->quantity);
        } elseif (intval($this->quantity) === 5) {
            $conditions[] = "(total_quantity <= ".intval($this->quantity)." AND total_quantity > 0)";
        }
        if ($this->hot == 1) {
            $conditions[] = "hot = 1 ";
        } elseif ($this->hot == 2) {
            $conditions[] = "hot != 1 ";
        }

        if (!empty($conditions)) {
            return implode(' AND ', $conditions);
        }

        return "1";
    }
}
