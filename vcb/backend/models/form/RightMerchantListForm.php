<?php
namespace backend\models\form;
use common\models\db\Right;

class RightMerchantListForm extends RightListForm {

    public function getConditions() {
        $conditions = [
            'type='. Right::TYPE_MERCHANT
        ];
        if ($this->keyword != '') {
            $conditions[] = "(name LIKE '%".$this->keyword."%' OR code LIKE '%".$this->keyword."%') ";
        }
        if ($this->status != 0) {
            $conditions[] = "status = ".$this->status;
        }
        if (!empty($conditions)) {
            return implode(' AND ', $conditions);
        }
        return "1";
    }
    
    public function getOperatorsByStatus($row) {
        $result = array();
        $operators = self::getOperators();
        switch ($row['status']) {
            case self::STATUS_ACTIVE:
                $result['update'] = $operators['update'];
                $result['lock'] = $operators['lock'];
                $result['delete'] = $operators['delete'];
                break;
            case self::STATUS_LOCK:
                $result['active'] = $operators['active'];
                break;
        }
        return $result;
    }

}
