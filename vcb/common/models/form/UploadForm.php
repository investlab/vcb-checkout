<?php
/**
 * Created by PhpStorm.
 * User: THU
 * Date: 5/19/2016
 * Time: 4:13 PM
 */

namespace common\models\form;


use yii\base\Model;

class UploadForm extends Model
{
    public $excelFile;

    public function rules()
    {
        return [
            [['excelFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['xlsx', 'xls']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'excelFile' => 'File excel',
        ];
    }
} 