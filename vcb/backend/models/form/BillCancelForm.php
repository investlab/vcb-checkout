<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\components\libs\Tables;

class BillCancelForm extends Bill {
    
    public $reason_cancel_id = null;
    public $reason_cancel = null;
    
    public function rules() {
        return [
            [['reason_cancel_id'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['reason_cancel_id'], 'integer', 'message' => '{attribute} không hợp lệ.']
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