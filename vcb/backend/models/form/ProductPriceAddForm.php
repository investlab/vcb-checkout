<?php
namespace backend\models\form;
use common\models\db\ProductPrice;
use common\models\db\ProductArea;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class ProductPriceAddForm extends ProductPrice {
      
    public function rules()
    {
        return [
            [['id','product_id', 'time_end',
                'status','time_created', 'time_updated',
                'time_active','time_deactive', 'time_lock',
                'user_created','user_updated', 'user_active',
                'user_deactive', 'user_lock'], 'integer'],
            [['zone_id'],'required','message' => 'Chưa có Tỉnh/Thành Phố được chọn.'],
//            [['zone_id'],'number','min' => 1,'tooSmall' => 'Bạn phải chọn Tỉnh/Thành Phố'],
            [['currency'], 'string', 'max' => 10],
            [['product_id', 'price', 'discount_percentage'
                , 'discount_amount', 'time_begin'
            ], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['price', 'discount_amount'], 'isAmount'],
            [['product_id'], 'isProductId'],
            [['discount_percentage', 'price_percentage'], 'isDiscountPercentage'],
            [['discount_amount'], 'isDiscountAmount'],
            [['time_begin'],'date','format' => 'dd-mm-yyyy HH:mm','message' => '{attribute} không hợp lệ.']
        ];
    }
    
    public function beforeSave($insert) {
        $this->price = ObjInput::formatCurrencyNumber($this->price);
        $this->discount_amount = ObjInput::formatCurrencyNumber($this->discount_amount);
        $this->time_begin = Yii::$app->formatter->asTimestamp($this->time_begin);
        $this->currency = 'VND';
        $this->time_end = 0;
        $this->status = self::STATUS_ACTIVE;
        $this->time_active = time();
        $this->user_active = Yii::$app->user->getId();
        $this->time_created = time();
        $this->user_created = Yii::$app->user->getId();
        $this->zone_id = $this->zone_id;
        return parent::beforeSave($insert);
    }
    
    public function getProductAreaZone($product_id) {
        $result = array();
        $product_area_info = ProductArea::getProductAreaZoneByProductId($product_id);
        if ($product_area_info != false) {
            foreach ($product_area_info as $row) {
                $zones = array();
                foreach ($row['zones'] as $sub_row) {
                    $zones[$sub_row['zone_id']] = array(
                        'id' => $sub_row['zone_id'],
                        'name' => $sub_row['zone_info']['name'],
                    );
                }
                $result[$row['id']] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'zones' => $zones,
                );
            }
        }
        return $result;
    }
}
