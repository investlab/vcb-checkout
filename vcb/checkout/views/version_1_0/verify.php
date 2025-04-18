<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice"> 
        <!--begin hoa don-->
        <!--header-->
        <?php require_once('includes/header.php') ?>
        <!--main-->
        <!--begin left Colm-->
        <div class="col-span-8 ">
            <div class="col-xs-12 col-sm-1 col-md-1 col-lg-2"></div>
            <div class="col-xs-12 col-sm-10 col-lg-8 brdRightIner vcb">
                <?php echo Yii::$app->view->renderFile('@app/views/' . Yii::$app->controller->id . '/includes/verify/' . strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code) . '.php', array('model' => $model, 'checkout_order' => $checkout_order)); ?>
                    <?php // ["version_1_0/index", "token_code" => $checkout_order['token_code']?>
            </div>
            <div class="col-xs-12 col-sm-1 col-md-1 col-lg-2"></div>
        </div>
        <!--footer-->

    </div>
</div>