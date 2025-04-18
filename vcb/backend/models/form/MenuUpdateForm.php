<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class MenuUpdateForm extends \common\models\db\Menu{
    public function beforeSave($insert) {
        $this->time_updated = time();
        $this->user_updated = Yii::$app->user->getId();
        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        $this->_updateIndexCategory($this->tableName());
    }
}
