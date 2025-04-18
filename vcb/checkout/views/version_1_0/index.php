<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;

$device = CheckMobile::isMobile();
$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
if (isset($_GET['debug']) && $_GET['debug'] == 'duclm') {
    var_dump($checkout_order["merchant_id"]);
}
?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice">
        <!--begin hoa don-->
        <!--header-->
        <?php include('includes/header.php') ?>
        <!--main-->
        <div class="col-sm-12 brdRight">
            <input type="hidden" id="enable-confirm"
                   value="<?= in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_CLICK_TO_ACCEPT']) ?>">
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">
                <?php if ($device == 'desktop'): ?>
                    <h4 class=""><!--<?= Translate::get('Chọn phương thức thanh toán') ?>--></h4>
                <?php endif; ?>
                <div class="panel-group methods row" id="accordion">
                    <?php foreach ($models as $key => $model) : ?>
                        <?php if (!empty($model->payment_models)) : ?>
                            <div class="panel panel-default">
                                <a class="collapsed"
                                   data-toggle="<?= array_key_exists("REQUEST-CREDIT-V2", $checkout_order['merchant_config']) && $checkout_order['merchant_config']['REQUEST-CREDIT-V2'] && $model->info['id'] == 17 ? '' : 'collapse' ?>"
                                   data-parent="#accordion"
                                   href="<?= array_key_exists("REQUEST-CREDIT-V2", $checkout_order['merchant_config']) && $checkout_order['merchant_config']['REQUEST-CREDIT-V2'] && $model->info['id'] == 17 ? Yii::$app->urlManager->createAbsoluteUrl([
                                       Yii::$app->controller->id . '/request-v2',
                                       'token_code' => $checkout_order['token_code'],
                                       'payment_method_code' => "CREDIT-CARD"
                                   ], HTTP_CODE) : '#' . strtolower($model->info['code']) ?>">
                                    <div class="panel-heading rlv">
                                        <div class="logo-method col-sm-3">
                                            <img width="58" height="44"
                                                 src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-', '_', $key) . '.png' ?>"
                                                 alt="loading...">
                                        </div>
                                        <?php if ($checkout_order["merchant_id"] == 19 && $model->info['id'] == 17): ?>
                                            <h4 class="panel-title col-sm-9">Thanh toán qua thẻ Visa / MasterCard /
                                                JCB</h4>
                                        <?php else: ?>
                                            <h4 class="panel-title col-sm-9"><?= Translate::get($model->info['name']) ?></h4>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div id="<?= strtolower($model->info['code']) ?>" class="panel-collapse collapse ">
                                    <div class="panel-body form_option">
                                        <?php if (strtolower($model->info['code']) == "credit-card" && $checkout_order["merchant_id"] != 19) : ?>
                                            <p>
                                                <i style="font-size: 14px;color: red;font-weight: bold; font-family: inherit;"><?= Translate::get("Quý khách vui lòng kiểm tra và chọn đúng biểu tượng in trên thẻ") ?></i>
                                            </p>
                                        <?php endif; ?>
                                        <ul class="cardList clearfix ">
                                            <?php foreach ($model->payment_models as $key => $payment_model) :
                                                if (strpos($key, '-TRA-GOP') && !empty($list_bank_installment)) {
                                                    foreach ($list_bank_installment as $item => $value) :
                                                        if ($key == $item) {
                                                            ?>

                                                            <li>
                                                                <div class="boxWrap"><a
                                                                            href="<?= Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/request', 'token_code' => $checkout_order['token_code'], 'payment_method_code' => $payment_model->info['code']], HTTP_CODE) ?>"
                                                                            title="<?= Translate::get($payment_model->info['name']) ?>"><i
                                                                                class="<?= @$payment_model->config['class'] ?>"></i></a>
                                                                </div>
                                                            </li>

                                                        <?php } endforeach;
                                                } else {
                                                    ?>
                                                    <li>
                                                        <div class="boxWrap">
                                                            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/request',
                                                                'token_code' => $checkout_order['token_code'],
                                                                'payment_method_code' => $payment_model->info['code']], HTTP_CODE) ?>"
                                                               title="<?= Translate::get($payment_model->info['name']) ?>"
                                                            >
                                                                <i class="<?= @$payment_model->config['class'] ?>"></i>
                                                            </a>

                                                        </div>
                                                    </li>
                                                <?php }endforeach; ?>

                                        </ul>
                                        <div class="payment-loading hide text-center"><img
                                                    src="images/loading_icon.gif"
                                                    width="180"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="text-center btnCancel">
                    <a class=""
                       href="<?= Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/transaction-destroy', 'token_code' => $checkout_order['token_code'], 'type' => 'user_cancel'], HTTP_CODE) ?>"><span><?= Translate::get('HỦY THANH TOÁN') ?></span></a>
                </div>
            </div>
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
        </div>
        <!--footer-->

    </div>
</div>