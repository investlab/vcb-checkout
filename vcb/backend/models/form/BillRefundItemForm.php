<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\components\libs\Tables;

class BillRefundItemForm extends Bill {
    
    public $bill_id;
    public $payment_transactions;
    public $reason_refund_id;
    public $reason_refund;
//    public $refund_amount;
//    public $receipt;
//    public $time_refund;
//    public $items;
    public $total_amount;
    public $bill_items;

    public function rules() {
        return [
//            [['payment_transaction_id','time_refund'], 'required', 'message' => 'Bạn phải chọn {attribute}'],
            ['reason_refund_id', 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'],
            [['reason_refund'], 'string', 'message' => '{attribute} không hợp lệ.'],
            [['bill_id','total_amount'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['bill_items','payment_transactions'], 'safe'],
            [['payment_transactions'], 'checkPt'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
//            'payment_transaction_id' => 'GD Thanh toán',
            'reason_refund_id' => 'Lý do hoàn tiền',
            'reason_refund' => 'Nội dung hoàn tiền',
            'total_amount' => 'Tổng tiền có thể hoàn',
//            'refund_amount' => 'Tiền muốn hoàn',
//            'receipt' => 'Mã tham chiếu',
//            'time_refund' => 'Thời gian hoàn',
//            'items' => 'Sản phẩm hoàn của yêu cầu',
            'bill_items' => 'Sản phẩm hoàn của đơn hàng',
        );
    }

    public function checkPt($attribute, $params)
    {
        //var_dump($this->payment_transactions);die;
        foreach($this->payment_transactions as $key => $v){
            if(intval($v['payment_transaction_id']) > 0){
                if($v['refund_amount'] == 0 || trim($v['refund_amount']) == ''){
                    $this->addError('amount_'.$v['payment_transaction_id'], 'Bạn phải nhập số tiền muốn hoàn');
                }
                if(trim($v['receipt']) == ''){
                    
                    $this->addError('receipt_'.$v['payment_transaction_id'], 'Bạn phải nhập mã tham chiếu');
                }
                if(trim($v['time_refund']) == ''){
                    $this->addError('time_'.$v['payment_transaction_id'], 'Bạn phải chọn thời gian hoàn');
                }
            }
        }
    }
}