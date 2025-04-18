<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;
use common\models\db\StockMove;
use common\components\libs\Tables;

class BillUpdateBankReceiptForm extends Bill {
    
    public $payment_transaction_id = null;
    public $bank_receipt = null;
    
    public function rules() {
        return [
            [['payment_transaction_id', 'bank_receipt'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['payment_transaction_id'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['bank_receipt'], 'string', 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'payment_transaction_id' => 'Giao dịch thanh toán',
            'bank_receipt' => 'Mã chuẩn chi',
        );
    }
}
