<?php
namespace backend\models\form;
use common\models\db\Zone;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;

class ZoneDeleteForm extends Zone{
    
    public function beforeDelete() {
        if (parent::beforeDelete()) {
            if ($this->_checkRelatedId()) {
                return true;
            } else {
                $this->addError('delete', 'Không xóa được do có rằng buộc về dữ liệu');
            }
        }
        return false;
    }
    
    protected function _checkRelatedId() {
        $data = Tables::selectOneDataTable($this->tableName(), "parent_id = ".$this->id);
        if ($data == false) {
            return true;
        }
        return false;
    }


    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        $this->_updateIndexCategory($this->tableName());
    }
}
