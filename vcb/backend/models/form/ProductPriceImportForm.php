<?php

namespace backend\models\form;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use PHPExcel;
use PHPExcel_Style_Alignment;
use yii\helpers\FileHelper;
use Faker\Provider\tr_TR\DateTime;
use yii\base\Exception;
use PHPExcel_IOFactory;

class ProductPriceImportForm extends Model {

    public $excelFile;

    public function rules() {
        return [
            [['excelFile'], 'isFileExcel'],
        ];
    }

    public function attributeLabels() {
        return [
            'excelFile' => 'File excel',
        ];
    }

    public function isFileExcel($attribute, $params) {
        $name = $this->$attribute->name;
        $extension = substr($name, strrpos($name, '.') + 1);
        if (!in_array($extension, array('xlsx', 'xls'))) {
            $this->addError($attribute, 'File upload không hợp lệ');
        }
    }

    public function upload() {
        if ($this->validate()) {
            $file_path = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'product_price' . DS . $this->excelFile->baseName . '.' . $this->excelFile->extension;
            $this->excelFile->saveAs($file_path);

            return $file_path;
        }
        return false;
    }

}
