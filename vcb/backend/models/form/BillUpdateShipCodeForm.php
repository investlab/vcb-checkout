<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillUpdateShipCodeForm extends Bill {
    
    public $ship_code = null;
    
    public function rules() {
        return [
            [['ship_code'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['ship_code'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'ship_code' => 'Mã vận đơn',
        );
    }
    
    public function getShipOrderId() {
        $bill_item_info = Tables::selectOneDataTable("bill_item", "bill_id = ".$this->id);
        if ($bill_item_info != false) {
            $stock_move_info = Tables::selectOneDataTable("stock_move", "sale_order_line_id = ".$bill_item_info['id']." AND status NOT IN (".StockMove::STATUS_NOT_SHIP.",".StockMove::STATUS_CANCEL.") ");
            if ($stock_move_info != false) {
                $this->ship_code = $stock_move_info['ship_code'];
                return $stock_move_info['ship_order_id'];
            }
        }
        return false;
    }
}
