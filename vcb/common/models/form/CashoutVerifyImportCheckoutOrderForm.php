<?php

namespace common\models\form;

use common\components\libs\Weblib;
use merchant\models\form\LanguageBasicForm;
use common\components\libs\Tables;
use common\models\db\Method;
use yii\base\Model;
use common\components\utils\Validation;
use common\payments\NganLuongWithdraw;
use common\components\utils\Strings;

class CashoutVerifyImportCheckoutOrderForm extends LanguageBasicForm {

    public $import_id = null;
    public $merchant_id = null;
    public $method_id = null;
    
    private $zones = null;
    private $city_names = null;   

    public function rules() {
        return [
            [['import_id', 'merchant_id', 'method_id'], 'required', 'message' => 'Bạn phải chọn {attribute}.'],
            [['merchant_id', 'method_id'], 'integer'],
            [['import_id'], 'isImportId'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'import_id' => 'import_id',
            'merchant_id' => 'Merchant',
            'method_id' => 'Hình thức rút',
        ];
    }

    public function isImportId($attribute, $value) {
        if (!file_exists(IMPORT_CASHOUT_REQUEST_PATH.$value)) {       
            $this->addError($attribute, 'Không tìm thấy file import');
        }
    }

    public function checkFileImport(&$error_message = '', &$rows = array(), &$validate_rows = array()) {
        if (file_exists(IMPORT_CASHOUT_REQUEST_PATH.$this->import_id)) {
            $rows = $this->_readFileImport(IMPORT_CASHOUT_REQUEST_PATH.$this->import_id);
            if (!empty($rows)) {
                $all = true;
                $method_code = Method::getCodeById($this->method_id);
                foreach ($rows as $key => $row) {
                    $validate_rows[$key] = $this->_validateRow($method_code, $rows[$key]);
                    if (!empty($validate_rows[$key])) {
                        $error_message = 'Dữ liệu file import không hợp lệ';
                        $all = false;
                    }
                }
                return $all;
            } else {
                $error_message = 'Nội dung file import không hợp lệ';
            }
        } else {
            $error_message = 'Không tìm thấy file import';
        }
        return false;
    }
    
    public function getColumns() {
        $columns = array(
            'index' => array('title' => 'No.', 'cell' => 'A'),
            'merchant_id' => array('title' => 'Merchant ID', 'cell' => 'B'),
            'amount' => array('title' => 'Payout (VND)', 'cell' => 'C'),
            'type' => array('title' => 'ATM/Account', 'cell' => 'D'),
            'account_number' => array('title' => 'Beneficiary Account No', 'cell' => 'E'),
            'account_name' => array('title' => 'Beneficiary Account Name', 'cell' => 'F'),
            'bank_code' => array('title' => 'BankCode', 'cell' => 'G'),
            'branch_name' => array('title' => 'Beneficiary Bank Branch', 'cell' => 'H'),
            'city_name' => array('title' => 'City Name', 'cell' => 'I'),
        );
        return $columns;
    }
    
    private function _validateRow($method_code, &$row) {
        $error_message = array();
        if ($row['merchant_id'] != $this->merchant_id) {
            $error_message['merchant_id'] = 'Merchant rút tiền không đúng với merchant đã chọn';
        }
        if ($method_code !== 'WITHDRAW-IB-OFFLINE' || trim($row['type']) != 'Account') {
            $error_message['type'] = 'Hình thức rút không đúng hoặc không được hỗ trợ';
        }       
        if (!Validation::checkBankNumber($row['account_number'])) {
            $error_message['account_number'] = 'Số tài khoản không hợp lệ';
        }
        if (!preg_match('/^[a-zA-Z\s]/', $row['account_name'])) {
            $error_message['account_name'] = 'Tên chủ tài khoản không hợp lệ';
        }
        if (!$this->_checkBankCode($row['bank_code'], $bank_info, $payment_method_info)) {
            $error_message['bank_code'] = 'Mã ngân hàng không hợp lệ';
        } else {
            $row['payment_method_id'] = $payment_method_info['id'];
        }
        if (floatval($row['amount']) < $payment_method_info['min_amount']) {
            $error_message['amount'] = 'Số tiền rút đang nhỏ hơn số tiền tối thiểu để rút';
        }
        if (!$this->_checkCityName($row['city_name'], $row['zone_id'])) {
            $error_message['city_name'] = 'Tỉnh thành không tồn tại';
        }
        return $error_message;
    }
    
    private function _checkBankCode($bank_code, &$bank_info = false, &$payment_method_info = false) {
        $bank_info = Tables::selectOneDataTable("bank", ["code = :code AND status = ".\common\models\db\Bank::STATUS_ACTIVE, "code" => $bank_code]);
        if ($bank_info != false) {
            $payment_method_info = Tables::selectOneDataTable("payment_method", [
                "transaction_type_id = ".\common\models\db\TransactionType::getWithdrawTransactionTypeId()." "
                . "AND bank_id = ".$bank_info['id']. " "
                . "AND id IN (SELECT payment_method_id FROM method_payment_method WHERE method_id = ".$this->method_id.") "
                . "AND status = ".\common\models\db\PaymentMethod::STATUS_ACTIVE." "
            ]);
            if ($payment_method_info != false) {
                return true;
            }
        }
        return false;
    }
    
    private function _checkCityName($city_name, &$city_id = 0) {
        if ($this->city_names === null) {
            $this->_setCityNames();
        }
        $city_name = str_replace('  ', ' ', trim($city_name));
        if (array_key_exists($city_name, $this->city_names)) {
            $city_id = $this->city_names[$city_name]['id'];
            return true;
        }
        return false;
    }
    
    private function _setCityNames() {
        $zones = $this->getZones();
        if (!empty($zones)) {
            foreach ($zones as $id => $zone_name) {
                $city_name = Strings::_convertToSMS(trim($zone_name));
                $city_name = str_replace('  ', ' ', $city_name);
                $this->city_names[$city_name] = array(
                    'name' => $zone_name,
                    'code' => $city_name,
                    'id' => $id,
                );
            }
        } else {
            $this->city_names = array();
        }
    }
    
    public function getZones() {
        if ($this->zones === null) {
            $this->_setZones();
        }
        return $this->zones;
    }
    
    private function _setZones() {
        $result = NganLuongWithdraw::GetZone();
        if ($result['error_code'] == '00') {
            $this->zones = $result['data'];
        } else {
            $this->zones = array();
        }       
    }

    private function _readFileImport($path_file) {
        $columns = $this->getColumns();
        $excel = new \common\components\libs\MyExcel();
        $excel->setColumns($columns);
        $rows = $excel->readFile($path_file);
        return $rows;
    }
}
