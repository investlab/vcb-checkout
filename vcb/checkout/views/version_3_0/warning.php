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
$tokenCode = Yii::$app->request->get('token_code');
?>
<!--THÊM NÚT CANCEL-->
<?php include(__DIR__ . '/../version_3_0/includes/header.php'); ?>
<main>
    <div class="container">
        <div class="accordion box-collapse" id="accordionExample">
            <div class="card">

                <div class="card-body p-0">
                    <div class="form-row">
                        <div class="col-md-6 bg-alert p-4 bg-warning" style="opacity: 0.9">
                            <div class="box-alert ba-success">
                                <span><img src="dist/images/icons-warning.png" alt=""></span>
                                <h2><?= Translate::get('Cảnh báo') ?>!</h2>
                                <p><?= Translate::get($error_message) ?>
                                </p>
                                <ul class="btn-list-alert">
                                    <li><a href="<?=Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/cancel", "token_code" => $tokenCode], HTTP_CODE) ?> "
                                           class="btn-alert text-white btn-warning"><?= Translate::get('Trở về trang mua') ?></a>
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
                                        <?= Translate::get('Giá trị đơn hàng') ?>:
                                        <span class="text-primary"><?= ObjInput::makeCurrency($checkout_order['amount']) ?> VND</span>
                                    </p>
                                    <p>

                                        <?php
                                        $fee = str_contains($paymentMethod->code,
                                            'TRA-GOP') ? $checkout_order['amount'] * 0.2 : 0;
                                        echo Translate::get('Phí') ?>:
                                        <span class="text-primary"><?= ObjInput::makeCurrency($fee) ?> VND</span>
                                    </p>
                                    <p>
                                        <label><?= Translate::get('Tổng số tiền') ?>:</label>
                                        <b class="text-primary"><?= ObjInput::makeCurrency($fee + $checkout_order['amount']) ?>
                                            VND</b>
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

