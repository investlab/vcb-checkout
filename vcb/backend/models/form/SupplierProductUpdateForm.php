<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class SupplierProductUpdateForm extends \common\models\db\SupplierProduct {
        
    public function beforeSave($insert) {
        $this->supplier_product_code = strtoupper($this->supplier_product_code);
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
