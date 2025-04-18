<?php



namespace common\models\form;

use common\components\libs\Tables;
use common\components\utils\ObjInput;
use common\payments\NganLuongWithdraw;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use common\models\db\PaymentMethod;
use common\models\db\Method;

class CashoutForm extends LanguageBasicForm
{

    public $id;
    public $payment_method_id;
    public $merchant_id;
    public $amount;
    public $bank_account_code;
    public $bank_account_name;
    public $bank_account_branch;
    public $bank_card_month;
    public $bank_card_year;
    public $zone_id;
    // Thêm yêu cầu rút tiền thẻ cào
    public $time_begin;
    public $time_end;

    public function rules()
    {
        return [
            [['amount'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['merchant_id', 'payment_method_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}.'],
            [['id', 'bank_card_month', 'bank_card_year','zone_id'], 'integer', 'message' => '{attribute} không hợp lệ.'],
            [['bank_account_code', 'bank_account_name', 'bank_account_branch'], 'string'],
            [['time_end', 'payment_method_id','amount'], 'checkValidate'],
            [['time_begin', 'time_end'], 'safe'],
            [['time_begin', 'time_end'], 'date', 'format' => 'dd-mm-yyyy HH:mm', 'message' => '{attribute} không hợp lệ . dd-mm-yyyy h:m'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_method_id' => 'Phương thức rút tiền',
            'merchant_id' => 'Merchant',
            'amount' => 'Số tiền muốn rút',
            'bank_account_code' => 'Số thẻ/Tài khoản/Email ví điện tử',
            'bank_account_name' => 'Tên chủ thẻ/Tài khoản',
            'bank_account_branch' => 'Tên chi nhánh',
            'bank_card_month' => 'Tháng trên thẻ',
            'bank_card_year' => 'Năm trên thẻ',
            'zone_id' => 'Tỉnh/Thành phố',
            // Thêm yêu cầu rút tiền thẻ cào
            'time_begin' => 'Thời gian bắt đầu',
            'time_end' => 'Thời gian kết thúc',
        ];
    }

    public function getMethodCode()
    {
        $method_id = PaymentMethod::getMethodIdByPaymentMethodId($this->payment_method_id);
        if ($method_id != false) {
            return Method::getCodeById($method_id);
        }
        return false;
    }

    public function getCardMonths()
    {
        return array(
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
        );
    }
    
    public function getZones() {
        $zones = array();
        $result = NganLuongWithdraw::GetZone();
        if ($result['error_code'] == '00') {
            $zones = $result['data'];
        }
        return $zones;
    }

    public function getCardYears()
    {
        $result = array();
        $year = date('Y');
        for ($i = $year - 10; $i <= $year + 10; $i++) {
            $result[$year] = $year;
        }
        return $result;
    }

    public function checkValidate($attribute, $param)
    {
        switch ($attribute) {
            case "time_end":
                if (strtotime($this->time_end) <= strtotime($this->time_begin)) {
                    $this->addError('time_end', 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu');
                }
                break;
            case "amount":
                if (ObjInput::formatCurrencyNumber($this->amount) < 0 ) {
                    $this->addError('amount', 'Số tiền rút không hợp lệ');
                }
                break;
            case "payment_method_id":
                if ($this->payment_method_id > 0) {                    
                    $method_code = $this->getMethodCode();
                    if (Method::isWithdrawIBOffline($method_code)) {
                        if ($this->bank_account_name == null) {
                            $this->addError('bank_account_name', 'Bạn phải nhập Tên chủ tài khoản');
                        }
                        if ($this->bank_account_code == null) {
                            $this->addError('bank_account_code', 'Bạn phải nhập Số tài khoản');
                        }
                        if ($this->bank_account_branch == null) {
                            $this->addError('bank_account_branch', 'Bạn phải nhập chi nhánh');
                        }
                        if(intval($this->zone_id) == 0){
                            $this->addError('zone_id', 'Bạn phải chọn Tỉnh/Thành phố');
                        }
                    } elseif (Method::isWithdrawATMCard($method_code)) {
                        if ($this->bank_account_name == null) {
                            $this->addError('bank_account_name', 'Bạn phải nhập Tên chủ thẻ');
                        }
                        if ($this->bank_account_code == null) {
                            $this->addError('bank_account_code', 'Bạn phải nhập Số thẻ');
                        }
                        if ($this->bank_card_month == null) {
                            $this->addError('bank_card_month', 'Bạn phải nhập Số tháng trên thẻ');
                        }
                        if ($this->bank_card_year == null) {
                            $this->addError('bank_card_year', 'Bạn phải nhập Số năm trên thẻ');
                        }
                    } elseif (Method::isWithdrawWallet($method_code)) {
                        if ($this->bank_account_code == null) {
                            $this->addError('bank_account_code', 'Bạn phải nhập Email/SĐT ví');
                        }
                    }
                    break;
                }

        }
    }
}