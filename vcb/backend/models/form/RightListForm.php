<?php
namespace backend\models\form;
use common\models\db\Right;
use yii\base\Model;
use common\components\libs\Tables;
use common\components\utils\FormatDateTime;
use common\models\db\User;
use common\components\utils\Translate;

class RightListForm extends Right{
    public $time_created_from = null;
    public $time_created_to = null;
    public $keyword = null;
    public $status = null;
    
    public function rules() {
        return [
            [['keyword'], 'string'],
            [['status'], 'integer'],
        ];
    }
    
    public function search() {
        $data = Tables::selectAllDataTable(self::tableName(), $this->getConditions(), "`left` ASC ");
        if ($data != false) {
            $this->_setStatus($data);
            User::setUsernameForRows($data);
        }
        return $data;
    }
    
    protected function _setStatus(&$data) {
        $index = 0;
        $status_array = self::getStatus();
        foreach ($data as $key=>$row) {
            $data[$key]['index'] = ++$index;
            $data[$key]['name'] = str_repeat('--', $row['level'] - 1) . ' ' .Translate::get($row['name']);
            $data[$key]['status_name'] = $status_array[$row['status']];
            $data[$key]['status_class'] = $this->_getStatusClass($row['status']);
            $data[$key]['operators'] = $this->getOperatorsByStatus($row);
        }
    }
    
    protected function _getStatusClass($status) {
        if ($status == self::STATUS_ACTIVE) {
            return 'label label-success';
        }
        return 'label label-danger';
    }
    
    public function getConditions() {
        $conditions = [
            'type='. Right::TYPE_BACKEND
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
                $result['update'] = $operators['update'];
                $result['active'] = $operators['active'];
                $result['delete'] = $operators['delete'];
                break;
        }
        return $result;
    }
    
    public static function getOperators() {
        return array(
            'detail' => array('title' => 'Xem chi tiết', 'confirm' => false),
            'update' => array('title' => 'Cập nhật', 'confirm' => false),
            'lock' => array('title' => 'Khóa', 'confirm' => true),
            'active' => array('title' => 'Mở khóa', 'confirm' => true),
            'delete' => array('title' => 'Xóa', 'confirm' => true),
        );
    }
}
