<?php
namespace common\models\form;

use yii\base\Model;

class PdfDownloadForm extends Model
{
    public $links;
    public $filenames;

    public function rules()
    {
        return [
            [['links', 'filenames'], 'required'],
            [['links', 'filenames'], 'string'],
        ];
    }
}
