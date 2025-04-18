<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class ProductCategoryUpdateForm extends \common\models\db\ProductCategory{
    public function beforeSave($insert) {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        self::_updateIndexCategory($this->tableName());
    }
}
