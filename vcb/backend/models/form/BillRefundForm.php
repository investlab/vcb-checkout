<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillRefundForm extends Bill {
    
    public $payment_transaction_id = null;
    public $reason_refund_id = null;
    public $reason_refund = null;
    public $receipt = null;
    
    public function rules() {
        return [
            [['payment_transaction_id', 'reason_refund_id', 'reason_refund', 'receipt'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['payment_transaction_id', 'reason_refund_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['reason_refund', 'receipt'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'payment_transaction_id' => 'Giao dịch thanh toán',
            'reason_refund_id' => 'Lý do hoàn tiền',
            'reason_refund' => 'Nội dung hoàn tiền',
            'receipt' => 'Mã giao dịch hoàn tiền',
        );
    }
    
    public function getRefundAmount() {
        return \common\models\db\PaymentTransaction::getRefundAmount($this->payment_transaction_id);
    }
}
