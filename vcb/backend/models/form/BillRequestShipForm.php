<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\components\libs\Tables;

class BillRequestShipForm extends Bill {
    
    public $ship_partner_id = null;
    public $ship_fee = null;
    public $ship_code = null;
    
    public function rules() {
        return [
            [['ship_partner_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['ship_fee'], 'number', 'message' => '{attribute} không hợp lệ'],
            [['ship_partner_id'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['ship_code'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'ship_partner_id' => 'Đơn vị vận chuyển',
            'ship_fee' => 'Phí vận chuyển',
            'ship_code' => 'Mã vận đơn'
        );
    }
}
