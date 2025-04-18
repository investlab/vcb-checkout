<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;
use common\components\utils\Validation;

class BillCancelShipForm extends Bill {
    
    public $time_delivery = null;
    
    public function rules() {
        return [
            [['reason_cancel_id', 'reason_cancel'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['reason_cancel_id'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['reason_cancel'], 'string', 'message' => '{attribute} không hợp lệ.'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'reason_cancel_id' => 'Lý do hủy giao hàng',
            'reason_cancel' => 'Nội dung hủy giao hàng',
        );
    }
    
    public function getShipOrderId() {
        $bill_item_info = Tables::selectOneDataTable("bill_item", "bill_id = ".$this->id);        
        if ($bill_item_info != false) {
            $stock_move_info = Tables::selectOneDataTable("stock_move", "sale_order_line_id = ".$bill_item_info['id']." AND status NOT IN (".StockMove::STATUS_CANCEL.") ");
            if ($stock_move_info != false) {
                return $stock_move_info['ship_order_id'];
            }
        }
        return false;
    }
}