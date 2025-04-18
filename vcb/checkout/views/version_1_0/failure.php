<?php

use common\components\utils\ObjInput;
use common\models\db\CheckoutOrder;
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
        <div class="col-span-8 mfleft brdRight success failure">
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
            <div class="col-xs-10 col-sm-10 col-md-8 brdRightIner">
                <div class="row clearfix" id="info-success">
                    <div class="col-xs-12 col-sm-12">
                        <h4 class="payopt-title">
                            <span class="redFont"> <?= Translate::get('Đơn hàng thanh toán thất bại') ?></span></h4>
                        <span class="redFont"><i><?php echo $transaction['reason_id'] . ": " . Translate::get($transaction['reason']); ?></i></span></h4>

                    </div>
                </div>
                <?php if ($redirect) { ?>
                    <div class="row boxreport clearfix <?= ($checkout_order['status'] != CheckoutOrder::STATUS_FAILURE) ? "review-color" : ""; ?>">
                        <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">
                            <img src="<?= ROOT_URL ?>/checkout/web/images/loading.gif">
                        </div>
                        <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">
                            <p><?= Translate::get('Hóa đơn của bạn đang được gửi về Website mua hàng') ?> !</p>
                            <p><?= Translate::get('Xin vui lòng') ?>
                                <strong><?= Translate::get('KHÔNG ĐÓNG TRÌNH DUYỆT') ?></strong></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->

    </div>
</div>

<script type="text/javascript">
    setTimeout('returnUrl();', 5000);

    function returnUrl() {
        document.location.href = '<?= $checkout_order['cancel_url'] ?>';
    }
</script>
