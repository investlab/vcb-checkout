<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use backend\models\form\ProductAddForm;

class ProductChangeStatusForm extends \common\models\db\Product {
    
    public function beforeSave($insert) {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
}
