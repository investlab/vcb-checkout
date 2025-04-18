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
$merchant_fubon_id = 1387; //LIVE FUBON ID
$merchant_emart_id = [193, 194, 1434]; // PHẦN ĐIỀU CHỈNH TIME RELOAD TRANG SUCCESS của EMART
$merchant_hungvuong_id = [33]; // PHẦN ĐIỀU CHỈNH TIME RELOAD TRANG SUCCESS của HUNG VUONG
$time_reload = 6000;
if ($checkout_order['merchant_id'] == $merchant_fubon_id) {
    $time_reload = 30000;
}
if (in_array($checkout_order['merchant_id'], $merchant_emart_id)) {
    $time_reload = 2000;
}
if (in_array($checkout_order['merchant_id'], $merchant_hungvuong_id)) {
    $time_reload = 2000;
}
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
                <?php if ($checkout_order['status'] == CheckoutOrder::STATUS_PAID || $checkout_order['status'] == CheckoutOrder::STATUS_INSTALLMENT_WAIT) { ?>
                    <div class="row clearfix" id="info-success">
                        <img src="<?= ROOT_URL ?>/checkout/web/images/check.png">
                        <div class="col-xs-12 col-sm-12">
                            <?php if ($checkout_order['merchant_id'] == 19): ?>
                                <div class="col-xs-12 col-sm-12" id="wording-fwd-success">
                                    <!--                            <h4 class="payopt-title"><span class="greenFont"> -->
                                    <?php //= Translate::get('Thanh toán thành công') ?><!--</span></h4>-->
                                    <p style="margin-top: 20px">
                                        Thông tin đóng phí của Quý khách đang được chuyển đến <span
                                                style="color: #aa5500">Cổng Thanh toán phí bảo hiểm trực tuyến của FWD Việt Nam</span>
                                    </p>
                                    <p>Quý khách vui lòng KHÔNG ĐÓNG TRÌNH DUYỆT</p>
                                </div>
                            <?php elseif ($checkout_order['merchant_id'] == $merchant_fubon_id): ?>
                                <h4 class="payopt-title">
                                    <span class="greenFont"> <?= Translate::get('XÁC NHẬN THANH TOÁN THÀNH CÔNG') ?></span>
                                </h4>

                                <p style="font-size: larger"><?= Translate::get('Cảm ơn quý khách đã tin tưởng và mua hàng tại ') ?>
                                    <strong class="greenFont"><?= $checkout_order['merchant_info']['name'] ?></strong>
                                </p>
                                <hr>
                                <div class="row" style="padding: 10px;">
                                    <div class="col-sm-3">
                                        <strong>Ngày, giờ giao dich </strong><br><i>Trans,Date,Time</i>
                                    </div>
                                    <div class="col-sm-9">
                                        <strong>: <?= date('d/m/Y H:i', $checkout_order['time_paid']) ?></strong>
                                    </div>
                                </div>
                                <div class="row" style="padding: 10px;">
                                    <div class="col-sm-3">
                                        <strong>Mã đơn hàng </strong><br><i>Order No</i>
                                    </div>
                                    <div class="col-sm-9">
                                        <strong>: <?= $checkout_order['order_code'] ?></strong>
                                    </div>
                                </div>
                                <div class="row" style="padding: 10px;">
                                    <div class="col-sm-3">
                                        <strong>Số tiền </strong><br><i>Amount</i>
                                    </div>
                                    <div class="col-sm-9">
                                        <strong>: <?= number_format($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?></strong>
                                    </div>
                                </div>
                                <div class="row" style="padding: 10px;">
                                    <div class="col-sm-3">
                                        <strong>Mô tả </strong><br><i>Description</i>
                                    </div>
                                    <div class="col-sm-9">
                                        <strong>: <?= $checkout_order['order_description'] ?></strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <h4 class="payopt-title"><span
                                            class="greenFont"> <?= Translate::get('Thanh toán thành công') ?></span>
                                </h4>
                                <p><?= Translate::get('Chúc mừng, bạn vừa thanh toán thành công số tiền') ?>
                                    <strong class="greenFont"><?= ObjInput::makeCurrency($checkout_order['cashin_amount']) ?> <?= $checkout_order['currency'] ?></strong>
                                    <?= Translate::get('Cho người bán') ?>
                                    <strong class="greenFont"><?= $checkout_order['merchant_info']['name'] ?></strong>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="row clearfix" id="info-warning">
                        <div class="col-xs-12 col-sm-12">
                            <h4 class="payopt-title text-danger">
                                <?= Translate::get('Thẻ của bạn đang bị review. Vui lòng liên hệ quản trị để hoàn tất thanh toán') ?>
                            </h4>
                        </div>
                    </div>
                <?php } ?>
                <div class="row boxreport clearfix <?= ($checkout_order['status'] != CheckoutOrder::STATUS_PAID && $checkout_order['status'] != CheckoutOrder::STATUS_INSTALLMENT_WAIT) ? "review-color" : ""; ?>">
                    <?php if ($checkout_order['merchant_id'] == 19): ?>
                        <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading"></div>
                        <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning"></div>
                    <?php else: ?>
                        <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">
                            <img src="<?= ROOT_URL ?>/checkout/web/images/loading.gif">
                        </div>
                        <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">
                            <p><?= Translate::get('Hóa đơn của bạn đang được gửi về Website mua hàng') ?> !</p>
                            <p><?= Translate::get('Xin vui lòng') ?>
                                <strong><?= Translate::get('KHÔNG ĐÓNG TRÌNH DUYỆT') ?></strong></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xs-1 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->

    </div>
</div>

<style>
    @media only screen and (min-width: 576px) {
        #wording-fwd-success {
            font-size: 15px !important;
        }
    }
</style>

<?php if ($checkout_order['status'] == CheckoutOrder::STATUS_PAID || $checkout_order['status'] == CheckoutOrder::STATUS_INSTALLMENT_WAIT) { ?>
    <script type="text/javascript">
        setTimeout('returnUrl();', <?=$time_reload?>);

        function returnUrl() {
            document.location.href = '<?= $checkout_order['return_url'] ?>';
        }
    </script>
<?php } else { ?>
    <script type="text/javascript">
        setInterval(function () {
            location.reload();
        }, 6000);
    </script>
<?php } ?>
