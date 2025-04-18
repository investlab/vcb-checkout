<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class SupplierProductAddForm extends \common\models\db\SupplierProduct {
    
    
    
    public function beforeSave($insert) {
        $this->supplier_product_code = strtoupper($this->supplier_product_code);
        $this->status = self::STATUS_ACTIVE;
        $this->time_created = time();
        $this->user_created = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
