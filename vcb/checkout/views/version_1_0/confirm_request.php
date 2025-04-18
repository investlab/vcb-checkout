<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container checkout"> 
    <div class="row">
        <div class="col-xs-12 col-xs-offset-0 col-sm-12 col-sm-offset-0 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a type="button" class="close" href="<?=Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id.'/cancel', 'token_code' => $checkout_order['code']], HTTP_CODE)?>" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                            <h2 class="panel-title"><i class="glyphicon glyphicon-credit-card text-primary"></i> Thanh toán đơn hàng</h2>                            
                        </div>
                        <div class="panel-body">
                            <?php echo Yii::$app->view->renderFile('@app/views/'.Yii::$app->controller->id.'/includes/confirm-request/'.strtolower($model->partner_payment_code).'/'.strtolower($model->payment_method_code).'.php', array('model' => $model)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>