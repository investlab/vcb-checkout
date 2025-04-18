<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Bill;

class BillConvertInstallmentTransactionForm extends Bill {
    
    public $payment_transaction_id = null;
    public $payment_transaction_amount = null;
    public $payment_method_id = null;
    public $installment_bank_refer_code = null;
    public $installment_bank_id = null;
    public $installment_period = null;
    public $bin_code = null;
    
    public function rules() {
        return [
            [['payment_transaction_id','payment_method_id', 'installment_bank_id', 'installment_period'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['payment_transaction_id','installment_bank_id','installment_period'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['bin_code'], 'string', 'max' => 20, 'message' => '{attribute} không hợp lệ'],
            [['installment_bank_refer_code'], 'string', 'max' => 50, 'message' => '{attribute} không hợp lệ'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'payment_method_id' => 'Hình thức thanh toán',
            'payment_transaction_id' => 'Mã GD thanh toán',
            'bin_code' => 'Mã BIN',
            'installment_option' => 'Lựa chọn trả góp',
            'installment_bank_id' => 'Ngân hàng trả góp',
            'installment_period' => 'Kỳ trả góp',
            'installment_bank_refer_code' => 'Mã chuẩn chi',
            
        );
    }
}
