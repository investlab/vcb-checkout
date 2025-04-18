<?php

namespace common\models\form;

use common\components\libs\Weblib;
use merchant\models\form\LanguageBasicForm;
use yii\web\UploadedFile;
use yii\base\Model;
use common\models\db\Merchant;
use common\models\db\Method;
use common\models\db\TransactionType;

class CashoutImportCheckoutOrderForm extends LanguageBasicForm {

    public $merchant_id;
    public $method_id;
    public $file_import;
    private $import_id = null;

    public function rules() {
        return [
            [['merchant_id', 'method_id'], 'required', 'message' => 'Bạn phải chọn {attribute}.'],
            [['merchant_id', 'method_id'], 'integer'],            
            [['file_import'], 'file', 'extensions' => 'xls,xlsx'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'merchant_id' => 'Merchant',
            'method_id' => 'Hình thức rút tiền',
            'file_import' => 'File',
        ];
    }

    public function getMerchants() {
        $merchant_arr = Weblib::getArraySelectBoxForTable('merchant', 'id', 'name', 'status = ' . Merchant::STATUS_ACTIVE, 'id ASC', array('' => 'Chọn merchant'));
        return $merchant_arr;
    }

    public function getWithdrawMethods() {
        $merchant_arr = Weblib::getArraySelectBoxForTable('method', 'id', 'name', "transaction_type_id = " . TransactionType::getWithdrawTransactionTypeId() . " AND code IN ('WITHDRAW-IB-OFFLINE') AND status = " . Method::STATUS_ACTIVE, 'id ASC', array('' => 'Chọn hình thức rút'));
        return $merchant_arr;
    }

    public function getImportId($extension) {
        if ($this->import_id == null) {
            $this->import_id = 'f' . uniqid() . '.' . $extension;
        }
        return $this->import_id;
    }

    public function importFile() {
        $this->file_import = UploadedFile::getInstance($this, 'file_import');
        if ($this->file_import != null) {
            $import_id = $this->getImportId($this->file_import->extension);
            if ($this->file_import->saveAs(IMPORT_CASHOUT_REQUEST_PATH . $import_id)) {
                return $import_id;
            } else {
                $this->addError('file_import', 'Không save được file');
            }
        } else {
            $this->addError('file_import', 'Bạn phải chọn file upload');
        }
        return false;
    }

}
