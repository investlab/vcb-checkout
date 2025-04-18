<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

echo $model->error_message;
$form = ActiveForm::begin(['options' => ['class' => 'active']]);
echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
ActiveForm::end();


