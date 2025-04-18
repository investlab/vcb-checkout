<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillUpdateShipCompletedForm extends Bill {
    
    public $ship_code = null;
    public $time_delivered = null;
    public $ship_order_id = null;

    public function rules() {
        return [
            [['ship_code', 'time_delivered'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['ship_code'], 'string', 'message' => '{attribute} không hợp lệ'],
            [['time_delivered'], 'isDateTime', 'message' => '{attribute} không hợp lệ.'],
            ['ship_order_id','integer']
        ];
    }
    
    public function attributeLabels() {
        return array(
            'ship_code' => 'Mã vận đơn',
            'time_delivered' => 'Thời gian giao hàng',
            'ship_order_id' => 'ID vận đơn',
        );
    }
    
    public function isDateTime($attribute, $params) {
        if (!preg_match('/^\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}:\d{1,2}$/', $this->$attribute)) {
            $this->addError($attribute, 'Thời gian giao hàng không hợp lệ');
        }
    }
    
    public function getShipOrderId() {
        return \common\models\business\BillBusiness::getShipOrderIdByBillId($this->id);
    }
}
