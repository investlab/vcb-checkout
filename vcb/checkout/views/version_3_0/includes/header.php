<?php

use common\components\utils\Translate;
use common\components\utils\ObjInput;
use common\models\db\CheckoutOrder;
use common\models\db\Merchant;
use common\models\db\PaymentMethod;

/** @var array $checkout_order */

$merchant = Merchant::findOne([$checkout_order["merchant_id"]]);
$logoMerchant = URL_CHECKOUT_ASSETS . 'vi/checkout/media/checkout-merchant-logo?path=' . $merchant->logo;
$currentUrl = Yii::$app->request->url;
$toEnUrl = str_replace('/vi/', '/en/', $currentUrl);
$toViUrl = str_replace('/en/', '/vi/', $currentUrl);

// check có hiển thị nút HỦY ĐƠN HÀNG hay không
$allow_cancel = true;
$action = Yii::$app->controller->action->id;
$is_installment = false;

if (isset($transaction['payment_method_id']) && intval($transaction['payment_method_id']) > 0) {
    $payment_method = \common\models\db\PaymentMethod::getPaymentMethodById($transaction['payment_method_id']);
    if ($payment_method) {
        // man QR - verify khong cho HUY DON HANG
        if (strpos($payment_method['code'], 'QR') !== false && !empty($action) && in_array($action,
                ['verify', 'success', 'failure', 'cancel'])) {
            $allow_cancel = false;
        }
    }
}

/** @var Object $model */
if (@$model->info['method_code'] == 'QR-CODE') {
    $allow_cancel = false;
}


?>
<?php
// TINH FEE VA TOTAL AMOUNT
$fee = isset($checkout_order['sender_fee']) && intval($checkout_order['sender_fee']) > 0
    ? $checkout_order['sender_fee'] : 0;
// Trong url hien tai, neu co 'TRA-GOP' thi PT hien tai la tra gop

if ($action == 'request' && strpos($currentUrl, 'TRA-GOP') !== false) {
    $is_installment = true;
}

if ($action == 'success') {
    $payment_method_info = PaymentMethod::getPaymentMethodById($transaction['payment_method_id']);
    if ($payment_method_info) {
        $payment_method_code = $payment_method_info['code'];
        $isInstallment = CheckoutOrder::isInstallmentByPaymentMethodCode($payment_method_code);
        if ($isInstallment) {
            $fee += $transaction['installment_fee_buyer'];
        }
    }
}

$total_amount = $checkout_order['amount'] + $fee;
?>

<header class="background-head-foot shadow">
    <div class="container container-fix">
        <div class="head-logo">
            <a href="#"><img src="<?= $logoMerchant ?>" alt="logo-merchant" style="height: 100%"></a>
            <h2>
                <?= $merchant->name ?>
                <span class="toggle-mobile" href="#">
                <span><?= number_format($total_amount) ?> <?= $checkout_order['currency'] ?></span>
            </span>
            </h2>
        </div>
        <ul class="head-list dropdown">
            <li>
                <a href="" class="dropdown-toggle" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false"><i class="las la-globe-americas"></i> <i
                            class="las la-angle-down m-l-10"></i>
                </a>
                <div class="dropdown-menu list-langue p-0" aria-labelledby="dropdownMenuLink">
                    <ul>
                        <li>
                            <a href="<?= $toEnUrl ?>"><?= Translate::get('Tiếng Anh') ?><img
                                        src="dist/images/icon-en.png" alt=""></a>
                            <a href="<?= $toViUrl ?>"><?= Translate::get('Tiếng Việt') ?><img
                                        src="dist/images/icon-vn.png" alt=""></a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="">
                <a class="dropdown-toggle toggle-dektop" href="#" role="button" id="dropdownMenuLink"
                   data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                        <span>
                            <i class="las la-file-invoice-dollar"></i>
                            <span class="order-total-amount"
                                  id="amount_order_total"><?= ObjInput::makeCurrency($total_amount) ?> <?= $checkout_order['currency'] ?></span><br>
                            <?php if ($checkout_order['currency_exchange'] != '' && $checkout_order['currency_exchange']): ?>
                                <?php $currency = json_decode($checkout_order['currency_exchange'], true); ?>
                                <span class="order-code">1 <?= $currency['currency_code'] ?> ~ <?= ObjInput::makeCurrency($currency['transfer']) ?> <?= $checkout_order['currency'] ?></span>
                            <?php endif; ?>

                        </span>
                    <i class="las la-angle-down"></i>
                </a>
                <div class="dropdown-menu check-list" aria-labelledby="dropdownMenuLink">
                    <div class="cl-top">
                        <p>
                            <?= Translate::get('Mã đơn hàng') ?>
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
                            <span class="text-primary"><?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?></span>
                        </p>
                        <p>
                            <?= Translate::get('Phí') ?>: <span
                                    class="text-primary"
                                    id="fee_cl"><?= ObjInput::makeCurrency($fee) ?> <?= $checkout_order['currency'] ?></span>
                        </p>
                        <p>
                            <label><?= Translate::get('Tổng số tiền') ?>:</label>
                            <b class="text-primary" id="amount_total_cl">
                                <?= ObjInput::makeCurrency($total_amount) ?> <?= $checkout_order['currency'] ?>
                            </b>

                        </p>
                    </div>
                    <?php
                    if ($allow_cancel): ?>
                        <hr>
                        <div class="cl-link">
                            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl([
                                Yii::$app->controller->id . '/transaction-destroy',
                                'token_code' => $checkout_order['token_code'],
                                'type' => 'user_cancel'
                            ], HTTP_CODE) ?>">
                                <i class="las la-times-circle"></i> <?= Translate::get('Hủy đơn hàng') ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </div>
</header>