<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

echo Yii::$app->view->renderFile('@app/views/'.Yii::$app->controller->id.'/includes/request/basic/basic-atm-card.php', array('model' => $model, 'checkout_order' => $checkout_order));
//echo Yii::$app->view->renderFile('@app/views/'.Yii::$app->controller->id.'/includes/request/basic/basic-atm-card-2.php', array('model' => $model, 'checkout_order' => $checkout_order));