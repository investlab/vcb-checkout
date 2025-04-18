<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;
use common\components\utils\ObjInput;

class ProductPriceUpdateForm extends \common\models\db\ProductPrice {
       
    public function beforeSave($insert) {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
