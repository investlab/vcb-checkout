<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class ProductCategoryAddForm extends \common\models\db\ProductCategory{
    public function beforeSave($insert) {
        $this->left = 1;
        $this->right = 1;
        $this->level = $this->_getLevel($this->parent_id);
        $this->time_created = time();
        $this->user_created = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        self::_updateIndexCategory($this->tableName());
    }
    
    protected function _getLevel($parent_id) {
        if ($parent_id != 0) {
            $result = Tables::selectOneDataTable("product_category", "id = $parent_id ");
            if ($result != false) {
                return $result['level'] + 1;
            }
        } else {
            return 1;
        }
    }
}
