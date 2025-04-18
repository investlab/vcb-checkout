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
                    <div class="row clearfix"  id="info-warning">
                        <div class="col-xs-12 col-sm-12">
                            <h4 class="payopt-title text-danger text-center" style="line-height: 30px;">
<!--                                --><?//= Translate::get('Thẻ của bạn đang bị review. Vui lòng liên hệ quản trị để hoàn tất thanh toán')?>
                                <?= Translate::get('Thẻ của quý khách có thể đã bị trừ tiền, do một số cảnh báo từ cổng quốc tế, Vietcombank sẽ kiểm tra thông tin trước khi hoàn tất giao dịch hoặc hoàn tiền về chủ thẻ')?>
                            </h4>
                        </div>
                    </div>
<!--                <div class="row boxreport clearfix review-color">-->
<!--                    <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">-->
<!--                    </div>-->
<!--                    <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">-->
<!--                    </div>-->
<!--                </div>-->
            </div>
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->
    </div>
</div>