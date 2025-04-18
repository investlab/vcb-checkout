<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use \common\models\db\CheckoutOrder;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$payer_fee = $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$payment_amount = $transaction['amount'] + $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice">
        <!--begin hoa don-->
        <!--header-->
        <?php require_once('includes/header.php') ?>
        <!--main-->
        <!--begin left Colm-->
        <div class="col-span-8 mfleft brdRight success">
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
            <div class="col-xs-10 col-sm-10 col-md-8 brdRightIner">
                <?php if ($checkout_order['status'] == CheckoutOrder::STATUS_REVERT) {?>
                    <div class="row clearfix" id="info-success">
                        <div class="col-xs-12 col-sm-12">
                            <h4 class="payopt-title"><span class="blueFont"> <?= Translate::get('Thanh toán hoàn hủy') ?></span></h4>
                            <p><?= Translate::get('Nếu tài khoản của quý khách bị trừ tiền, quý khách sẽ được hoàn lại về tài khoản trong thời gian sớm nhất') ?>
                            </p>
                        </div>
                    </div>
                <?php } ?>

            </div>
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->

    </div>
</div>

