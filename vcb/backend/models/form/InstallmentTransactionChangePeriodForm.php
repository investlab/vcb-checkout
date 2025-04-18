<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\InstallmentTransaction;
use common\components\libs\Tables;

class InstallmentTransactionChangePeriodForm extends InstallmentTransaction {
        
    public function rules() {
        return [
            [['period'], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['period'], 'required', 'message' => 'Bạn phải chọn {attribute}'],
        ];
    }
    
    public function attributeLabels()
    {
        return array(
            'period' => 'Kỳ trả góp',
        );
    }
    
    public function getPeriods($result = array()) {
        $data = Tables::selectAllDataTable("installment_bank_period", "installment_bank_id = ".$this->installment_bank_id," period ASC ");
        if ($data != false) {
            foreach ($data as $row) {
                if ($row['period'] != 0) {
                    $result[$row['period']] = $row['period'].' tháng';
                }
            }
        }
        return $result;
    }
    
    public function getInstalllmentBankName() {
        $installment_bank_info = Tables::selectOneDataTable("installment_bank", "id = ".$this->installment_bank_id);
        if ($installment_bank_info != false) {
            $bank_info = Tables::selectOneDataTable("bank", "id = ".$installment_bank_info['bank_id']);
            if ($bank_info != false) {
                return \common\components\utils\Strings::strip($bank_info['name']);
            }
        }
        return '';
    }
}
