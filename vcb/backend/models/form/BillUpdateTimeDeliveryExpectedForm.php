<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;
use common\components\utils\Validation;

class BillUpdateTimeDeliveryExpectedForm extends Bill {
    
    public $time_delivery = null;
    
    public function rules() {
        return [
            [['time_delivery'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['time_delivery'], 'isDateTime', 'message' => '{attribute} không hợp lệ.'],
        ];
    }
    
    public function isDateTime($attribute, $params) {
        if (!preg_match('/^\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}:\d{1,2}$/', $this->$attribute)) {
            $this->addError($attribute, 'TG dự kiến giao hàng không hợp lệ');
        }
    }
    
    public function attributeLabels()
    {
        return array(
            'time_delivery' => 'TG dự kiến giao hàng',
        );
    }
    
    public function getShipOrderId() {
        $bill_item_info = Tables::selectOneDataTable("bill_item", "bill_id = ".$this->id);
        if ($bill_item_info != false) {
            $stock_move_info = Tables::selectOneDataTable("stock_move", "sale_order_line_id = ".$bill_item_info['id']." AND status NOT IN (".StockMove::STATUS_NOT_SHIP.",".StockMove::STATUS_CANCEL.") ");
            if ($stock_move_info != false) {
                return $stock_move_info['ship_order_id'];
            }
        }
        return false;
    }
}