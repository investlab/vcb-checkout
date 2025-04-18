<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$payer_fee = $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$payment_amount = $transaction['amount'] + $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$tokenCode = Yii::$app->request->get('token_code');
?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice"> 
        <!--begin hoa don-->
        <!--header-->
        <?php require_once('includes/header.php') ?>
        <!--main-->
        <!--begin left Colm-->
        <div class="col-span-8 mfleft brdRight">
            <div class="col-sm-2"></div>
            <div class="col-sm-8 brdRightIner">
                <div class="row">
                    <div class="form-horizontal mform2 pdtop">
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <div class="alert alert-warning" style="margin-top: 30px;">
                                    <i class="fas fa-exclamation-triangle text-danger pull-left" style="font-size: 40px; margin-right: 12px;"></i>
                                    <p class="text-justify padding-15px"><?= Translate::get($error_message) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row display-flex-center">
                    <a class="col-sm-4 col-md-3 col-xs-5 btn btn-danger" id="cancel-btn-page-warning" href="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/cancel", "token_code" => $tokenCode], HTTP_CODE) ?>">
                        <span><?= Translate::get('QUAY LẠI') ?></span>
                    </a>
                </div>
            </div>
            <div class="col-sm-2"></div>
        </div>
        <!--footer-->

    </div>
</div>