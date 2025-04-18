<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class MenuUpdatePositionForm extends \common\models\db\Menu{
    
    public function updatePosition($ids, $positions) {
        $connection = Yii::$app->getDb();
        $now = time();
        foreach ($ids as $key=>$id) {
            $sql = "UPDATE `".$this->tableName()."` SET position = ".$positions[$key].", time_updated = $now, user_updated = ".Yii::$app->user->getId()."  WHERE id = ".$id;
            $command = $connection->createCommand($sql);
            $result = $command->execute();
        }
        $this->_updateIndexCategory($this->tableName());
        return true;
    }
}
