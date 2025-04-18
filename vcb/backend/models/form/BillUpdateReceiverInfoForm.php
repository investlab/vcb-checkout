<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillUpdateReceiverInfoForm extends Bill {
    
    public $zone_id = null;
    public $district_id = null;
    public $city_id = null;
    public $address = null;
    
    public function rules() {
        return [
            [['zone_id', 'city_id','district_id', 'address'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['zone_id', 'city_id','district_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['address'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'city_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'zone_id' => 'Phường/Xã',
            'address' => 'Địa chỉ nhận hàng',
        );
    }
    
    public function setValueDefault() {
        $this->address = $this->buyer_address;
        $this->zone_id = $this->buyer_zone_id;
    }
}
