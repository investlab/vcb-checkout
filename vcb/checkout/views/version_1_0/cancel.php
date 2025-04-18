<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$payer_fee = $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$payment_amount = $transaction['amount'] + $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$tokenCode =  Yii::$app->request->get('token_code');
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
                                <div class="alert alert-danger"><?=Translate::get('Bạn đã hủy thanh toán đơn hàng thành công')?>!</div>
                                <div style="text-align: center">
                                    <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/index", "token_code" => $tokenCode], HTTP_CODE)?>">
                                        <button class="btn btn-success"><?=Translate::get('Chọn phương thức thanh toán')?></button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
            <div class="col-sm-2"></div>
        </div>
        <!--footer-->

    </div>
</div>