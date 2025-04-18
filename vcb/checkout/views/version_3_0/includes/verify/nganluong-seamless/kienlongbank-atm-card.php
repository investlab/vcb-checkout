<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

echo Yii::$app->view->renderFile('@app/views/'.Yii::$app->controller->id.'/includes/verify/nganluong-seamless/basic.php', array('model' => $model, 'checkout_order' => $checkout_order));
