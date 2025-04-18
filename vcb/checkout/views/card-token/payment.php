<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$payment_method_code = ObjInput::get('payment_method_code', 'str', '');
$method = substr($payment_method_code, -9);
?>
<p class="demo" style="    height: 100%;
    width: 100%;
    position: absolute;
    z-index: 999;
    background: #00000024;
    margin: auto;
    display: flex;
    text-align: center;
    justify-content: center;
    align-items: center;">
    <span class=" text text-warning"><img src="<?= ROOT_URL ?>checkout\web\images\loading_2.gif" alt=""></span>
</p>
<div class="panel panel-default wrapCont">
    <div class="modal fade" id="modal-notify" tabindex=-1 role=dialog aria-hidden=true>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;
                    </button>
                    <h4 class="modal-title"><?= Translate::get('Thông báo') ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal" role="form">
                        <div class="alert alert-warning fade in" align="center">
                            <span id="error_message"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mdevice">
        <!--begin hoa don-->
        <!--header-->
<!--        --><?php //require_once('includes/header.php') ?>
        <!--main-->
        <div class="col-span-12 brdRight">
            <?php
            $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $action, 'options' => ['class' => 'active']]);
            echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
            echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
            ?>
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">
                <h4 class=""><!--<?= Translate::get('Chọn phương thức thanh toán') ?>--></h4>
                <div class="panel-group methods row" id="accordion">
                    <?php echo Yii::$app->view->renderFile('@app/views/'.Yii::$app->controller->id.'/includes/request/'.strtolower($model->partner_payment_code).'/'.strtolower($model->payment_method_code).'.php', array('model' => $model, 'checkout_order' => $checkout_order, 'data_cyber' => $data_cyber, 'action' => $action)); ?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <?php ActiveForm::end();?>
        </div>
        <!--footer-->

    </div>
</div>
