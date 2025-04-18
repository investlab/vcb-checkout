<?php

use common\models\form\PdfDownloadForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Download PDFs from Links';

/** @var $model  PdfDownloadForm */
?>
<div class="panel panel-default wrapCont">
    <br>

    <div class="row">
        <div class="col-md-2" style="margin-left: 15px;">
            <h4>Tải xuống hóa đơn 3C NL</h4>
        </div>
        <div class="pdf-download-form">

            <?php $form = ActiveForm::begin([
                'action' => ['pdf/download'],
                'method' => 'post',
            ]); ?>

            <div class="col-md-12">
                <?= $form->field($model, 'links')->textarea(['rows' => 6])->label('Links (mỗi link ngăn cách nhau bởi dấu | )') ?>
                <?= $form->field($model, 'filenames')->textarea(['rows' => 6])->label('Tên file PDF tương ứng (mỗi tên ngăn cách nhau bởi dấu | )') ?>
                <div class="form-group text-center">
                    <?= Html::submitButton('Download PDFs', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>


