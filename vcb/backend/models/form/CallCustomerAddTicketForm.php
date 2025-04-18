<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;

class CallCustomerAddTicketForm extends CallCustomerBasicForm {
    
    public $call_history_info = null;
    public $customer_info = null;
    public $title = 'Hỗ trợ';
    public $key = null;
    
    public function rules() {
        return [
            [['reason_cancel_id', 'reason_cancel'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['reason_cancel_id'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['reason_cancel'], 'string', 'message' => '{attribute} không hợp lệ.'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'reason_cancel_id' => 'Lý do hủy đơn hàng',
            'reason_cancel' => 'Nội dung hủy đơn hàng',
        );
    }
}