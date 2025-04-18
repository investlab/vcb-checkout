<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class RightUpdatePositionForm extends \common\models\db\Right{
    
    public function updatePosition($ids, $positions) {
        if ($this->_isArrayInterger($ids) && $this->_isArrayInterger($positions)) {
            $connection = Yii::$app->getDb();
            $now = time();
            foreach ($ids as $key=>$id) {
                $sql = "UPDATE `".$this->tableName()."` SET position = ".intval($positions[$key]).", time_updated = $now, user_updated = ".Yii::$app->user->getId()."  WHERE id = ".$id;
                $command = $connection->createCommand($sql);
                $result = $command->execute();
            }
            $this->_updateIndexCategory($this->tableName());
            return true;
        }
        return false;
    }
    
    private function _isArrayInterger($values) {
        if (is_array($values) && !empty($values)) {
            foreach ($values as $value) {
                if (!is_numeric($value)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
