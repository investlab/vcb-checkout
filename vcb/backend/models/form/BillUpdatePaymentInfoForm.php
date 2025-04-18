<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillUpdatePaymentInfoForm extends Bill {
    
    public $payment_transaction_id = null;
    public $card_number = null;
    public $card_type = null;
    
    public function rules() {
        return [
            [['payment_transaction_id', 'card_number', 'card_type'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['payment_transaction_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['card_number', 'card_type'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'payment_transaction_id' => 'Giao dịch thanh toán',
            'card_number' => 'Số thẻ',
            'card_type' => 'Loại thẻ',
        );
    }
    
    public function getPaymentInfo() {
        $payment_info = array(
            'card_number' => $this->card_number,
            'card_type' => $this->card_type,
        );
        return json_encode($payment_info);
    }
}
