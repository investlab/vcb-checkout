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
                <?php if ($checkout_order['status'] == CheckoutOrder::STATUS_PAID) {?>
                    <div class="row clearfix" id="info-success">
                        <img src="<?= ROOT_URL ?>/checkout/web/images/check.png">
                        <div class="col-xs-12 col-sm-12">
                            <h4 class="payopt-title"><span class="greenFont"> <?= Translate::get('Thanh toán thành công') ?></span></h4>
                            <p><?= Translate::get('Chúc mừng, bạn vừa thanh toán thành công số tiền') ?>
                                <strong class="greenFont"><?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?></strong>
                                <?= Translate::get('Cho người bán') ?>
                                <strong class="greenFont"><?= $checkout_order['merchant_info']['name'] ?></strong>
                            </p>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="row clearfix"  id="info-warning">
                        <div class="col-xs-12 col-sm-12">
                            <h4 class="payopt-title text-danger">
                                <?= Translate::get('Thẻ của bạn đang bị review. Vui lòng liên hệ quản trị để hoàn tất thanh toán')?>
                            </h4>
                        </div>
                    </div>
                <?php } ?>
                <div class="row boxreport clearfix <?= ($checkout_order['status'] != CheckoutOrder::STATUS_PAID)? "review-color": ""; ?>">
                    <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">
                        <img src="<?= ROOT_URL ?>/checkout/web/images/loading.gif">
                    </div>
                    <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">
                        <p><?= Translate::get('Hóa đơn của bạn đang được gửi về Website mua hàng') ?> !</p>
                        <p><?= Translate::get('Xin vui lòng') ?> <strong><?= Translate::get('KHÔNG ĐÓNG TRÌNH DUYỆT') ?></strong></p>
                    </div>
                </div>
            </div>
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->

    </div>
</div>

<?php if ($checkout_order['status'] == CheckoutOrder::STATUS_PAID) { ?>
    <script type="text/javascript">
        setTimeout('returnUrl();', 5000);
        function returnUrl() {
            document.location.href = '<?= $checkout_order['return_url'] ?>';
        }
    </script>
<?php } else { ?>
    <script type="text/javascript">
        setInterval(function(){ location.reload(); }, 6000);
    </script>
<?php }?>
