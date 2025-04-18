<?php

use common\components\utils\ObjInput;
use common\models\db\PaymentMethod;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use \common\models\db\CheckoutOrder;

$this->title                   = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$payer_fee                     = $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$payment_amount                = $transaction['amount'] + $transaction['sender_fee'] + $transaction['partner_payment_sender_fee'];
$merchant_fubon_id             = 1387; //LIVE FUBON ID
$merchant_emart_id             = [193, 194, 1434]; // PHẦN ĐIỀU CHỈNH TIME RELOAD TRANG SUCCESS của EMART
$merchant_hungvuong_id         = [33]; // PHẦN ĐIỀU CHỈNH TIME RELOAD TRANG SUCCESS của HUNG VUONG
$time_reload                   = 6000;
if ($checkout_order['merchant_id'] == $merchant_fubon_id) {
    $time_reload = 30000;
}
if (in_array($checkout_order['merchant_id'], $merchant_emart_id)) {
    $time_reload = 2000;
}
if (in_array($checkout_order['merchant_id'], $merchant_hungvuong_id)) {
    $time_reload = 2000;
}
$isInstallment       = false;
$payment_method_info = PaymentMethod::getPaymentMethodById($transaction['payment_method_id']);
if ($payment_method_info) {
    $payment_method_code = $payment_method_info['code'];
    $isInstallment       = CheckoutOrder::isInstallmentByPaymentMethodCode($payment_method_code);
}
?>

<?php include(__DIR__ . '/../version_3_0/includes/header.php'); ?>
<main>
    <div class="container">
        <div class="accordion box-collapse" id="accordionExample">
            <div class="card">
                <div class="card-body p-0">
                    <div class="form-row">
                        <div class="col-md-6 bg-alert p-4 bg-primary" style="opacity: 0.9">
                            <div class="box-alert ba-success">
                                <span><img src="dist/images/icons8-success-480.png" alt=""></span>
                                <h2><?= Translate::get('Thanh toán thành công') ?>!</h2>
                                <p><?= Translate::get('Tất cả chi phí của đơn hàng đã được thanh toán lúc') . ' ' . date('d/m/Y H:i:s',
                                        $transaction['time_paid']) ?>
                                </p>
                                <ul class="btn-list-alert">
                                    <li><a href="<?= ROOT_URL . 'test/merchant_demo_4.php' ?> "
                                           class="btn-alert text-white btn-primary shadow"><i class="fa fa-undo mr-2"
                                                                                              aria-hidden="true"></i><?= Translate::get('Trở về trang mua') ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checkuot-detail">
                                <div class="cl-top">
                                    <p>
                                        <?= Translate::get('Mã hoá đơn') ?>
                                        <span><?= $checkout_order['order_code'] ?></span>
                                    </p>
                                    <p>
                                        <?= Translate::get('Mô tả') ?>
                                        <span><?= $checkout_order['order_description'] ?></span>
                                    </p>
                                </div>
                                <hr>
                                <div class="cl-bottom">
                                    <p>
                                        <?= Translate::get('Giá trị đơn hàng') ?>
                                        <span class="text-primary"><?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?></span>
                                    </p>
                                    <?php if ($isInstallment) {
                                        $total_amount = $checkout_order['amount'] + $checkout_order['sender_fee'] + $transaction['installment_fee_buyer'];
                                        $fee          = $checkout_order['sender_fee'] + $transaction['installment_fee_buyer'];
                                    } else {
                                        $total_amount = $checkout_order['amount'] + $checkout_order['sender_fee'];
                                        $fee          = $checkout_order['sender_fee'];

                                    } ?>
                                    <p>
                                        <?= Translate::get('Phí') ?>:
                                        <span class="text-primary"><?= ObjInput::makeCurrency($fee) ?> <?= $checkout_order['currency'] ?></span>
                                    </p>
                                    <p>
                                        <?= Translate::get('Tổng số tiền') ?>:
                                        <b class="text-primary"><?= ObjInput::makeCurrency($total_amount) ?> <?= $checkout_order['currency'] ?></b>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include(__DIR__ . '/../version_3_0/includes/footer.php'); ?>

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
