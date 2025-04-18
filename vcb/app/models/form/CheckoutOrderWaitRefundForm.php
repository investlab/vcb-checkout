<?php


namespace  app\models\form;


use common\models\db\CheckoutOrder;
use merchant\models\form\LanguageBasicForm;
use common\components\libs\Tables;
use common\components\utils\ObjInput;
use yii\base\Model;

class CheckoutOrderWaitRefundForm extends LanguageBasicForm
{
    public $order_id;
    public $payment_method_id;
    public $partner_payment_id;
    public $refund_type;
    public $refund_amount;
    public $partner_payment_method_refer_code;
    public $refund_reason;

    public function rules()
    {
        return [
//            [['payment_method_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'],
//            [['id', 'partner_payment_id'], 'integer'],
//            [['partner_payment_method_refer_code'], 'string']
            [['refund_type', 'refund_amount'], 'validateRefund'],
            [['refund_reason'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'order_id' => 'Mã đơn hàng',
            'payment_method_id' => 'Phương thức hoàn tiền',
            'partner_payment_id' => 'Kênh hoàn tiền',
            'partner_payment_method_refer_code' => 'Mã tham chiếu kênh hoàn tiền',
            'refund_type' => 'Loại hoàn tiền',
            'refund_amount' => 'Số tiền hoàn lại'
        ];
    }

    public function getRefundType() {
        return [
            0 => 'Chọn loại hoàn tiền',
            $GLOBALS['REFUND_TYPE']['TOTAL'] => 'Hoàn tiền toàn bộ',
            $GLOBALS['REFUND_TYPE']['PARTIAL'] => 'Hoàn tiền một phần',
        ];
    }
    
    public function validateRefund($attribute, $params) {
        if ($attribute == 'refund_type') {
            if ($this->refund_type == 0) {
                $this->addError($attribute, 'Bạn phải chọn ' . $this->getAttributeLabel($attribute));
            } elseif ($this->refund_type == $GLOBALS['REFUND_TYPE']['PARTIAL']) {
                if (empty($this->refund_amount)) {
                    $this->addError('refund_amount', 'Bạn phải nhập ' . $this->getAttributeLabel('refund_amount'));
                } else {
                    $checkout_order_info = Tables::selectOneDataTable('checkout_order', ['id = :id', "id" => $this->order_id]);
                    if (!empty($checkout_order_info)) {
                        $checkout_order = CheckoutOrder::setRow($checkout_order_info);
                        if ($this->refund_amount >= $checkout_order['amount']) {
                            $this->addError($this->refund_amount, 'Số tiền hoàn một phần phải nhỏ hơn '
                                . ObjInput::makeCurrency($checkout_order['amount'])
                                . ' ' . $checkout_order_info['currency']
                            );
                        }
                    }
                }
            }
        }
    }


    
} 