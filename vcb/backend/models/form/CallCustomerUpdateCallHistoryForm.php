<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;
use common\models\db\CallResult;
use common\models\db\CallResultGroup;
use common\models\business\CallHistoryBusiness;
use common\models\db\CallHistory;

class CallCustomerUpdateCallHistoryForm extends CallCustomerAddCallHistoryForm {

    public $parent_id = null;
    public $call_result_group_id = null;
    public $call_result_id = null;
    public $description = null;

    public function rules() {
        return [
            [['parent_id', 'call_result_group_id', 'call_result_id'], 'required', 'message' => 'Bạn phải chọn {attribute}'],
            [['parent_id', 'call_result_group_id', 'call_result_id'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['description'], 'string', 'message' => '{attribute} không hợp lệ.'],
        ];
    }

    public function attributeLabels() {
        return array(
            'parent_id' => 'Phân loại kết quả cuộc gọi',
            'call_result_group_id' => 'Phân loại chi tiết kết quả cuộc gọi',
            'call_result_id' => 'Kết quả cuộc gọi',
            'description' => 'Hướng xử lý',
        );
    }

    protected function _afterLoadRequest() {
        if ($this->call_history_info['status'] == CallHistory::STATUS_CALLED) {
            $this->title = 'Kết quả gọi';
        }
        if (intval($this->call_result_id) !== 0) {
            $call_result_info = Tables::selectOneDataTable("call_result", "id = " . $this->call_result_id . " AND status = " . CallResult::STATUS_ACTIVE);
            if ($call_result_info != false) {
                $this->call_result_group_id = $call_result_info['call_result_group_id'];
            }
        }
        if (intval($this->call_result_group_id) !== 0) {
            $call_result_group_info = Tables::selectOneDataTable("call_result_group", "id = " . $this->call_result_group_id . " AND status = " . CallResultGroup::STATUS_ACTIVE);
            if ($call_result_group_info != false) {
                $this->parent_id = $call_result_group_info['parent_id'];
            }
        }
    }

    public function getCallResults($call_result_group_id) {
        return Tables::selectAllDataTable("call_result", "call_result_group_id = $call_result_group_id AND status = " . CallResult::STATUS_ACTIVE, "position ASC, id ASC ", "id");
    }

    public function getCallResultGroup($parent_id) {
        if ($parent_id !== null && $parent_id !== '') {
            if ($parent_id == 0) {
                $data = Tables::selectAllDataTable("call_result_group", "parent_id = $parent_id AND status = " . CallResultGroup::STATUS_ACTIVE, "`left` ASC ");
                return $this->_getCallResultGroup($data, $parent_id);
            } else {
                $parent_info = Tables::selectOneDataTable("call_result_group", "id = $parent_id AND status = " . CallResultGroup::STATUS_ACTIVE);
                if ($parent_info != false) {
                    $data = Tables::selectAllDataTable("call_result_group", "`left` > " . $parent_info['left'] . " AND `right` < " . $parent_info['right'] . " AND status = " . CallResult::STATUS_ACTIVE, "`left` ASC ");
                    return $this->_getCallResultGroup($data, $parent_id);
                }
            }
        }
        return array();
    }

    private function _getCallResultGroup($call_results, $parent_id) {
        $result = array();
        if (!empty($call_results)) {
            foreach ($call_results as $row) {
                if ($parent_id == $row['parent_id']) {
                    $childs = $this->_getCallResultGroup($call_results, $row['id']);
                    if (empty($childs)) {
                        $result[$row['id']] = $row['name'];
                    } else {
                        $result[$row['name']] = $childs;
                    }
                }
            }
        }
        return $result;
    }
    
    public function submit() {
        $inputs = array(
            'id' => $this->call_history_info['id'], 
            'time_call' => time() - $this->call_history_info['time_created'], 
            'call_result_id' => $this->call_result_id, 
            'call_result_note' => $this->description, 
            'customer_note' => $this->description, 
            'time_appointment' => 0, 
            'user_id' => Yii::$app->user->getId(), 
        );
        $result = CallHistoryBusiness::updateResult($inputs);
        if ($result['error_message'] == '') {
            $this->message = 'Cập nhật kết quả cuộc gọi thành công';
            $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index','option'=>'form_update_call_history', 'call_history_id'=>$this->call_history_info['id']]);
            header('Location:'.$url);
            die();
        } else {
            $this->error = $result['error_message'];
        }
    }

}
