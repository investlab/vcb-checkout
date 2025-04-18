<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class ZoneUpdatePositionForm extends \common\models\db\Zone{
    
    public function updatePosition($ids, $positions) {
        if ($this->_isArrayInterger($ids) && $this->_isArrayInterger($positions)) {
            $connection = Yii::$app->getDb();
            $now = time();
            foreach ($ids as $key=>$id) {
                set_time_limit(60);
                $sql = "UPDATE ".$this->tableName()." SET position = ".intval($positions[$key])."  WHERE id = ".$id;
                $command = $connection->createCommand($sql);
                $result = $command->execute();
            }
            self::_updateIndexCategory($this->tableName());
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
