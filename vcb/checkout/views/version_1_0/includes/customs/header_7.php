<?php

use common\components\utils\Translate;
use common\components\utils\ObjInput;
use common\components\utils\CheckMobile;

$model->info['view_path'] = __DIR__;

$device = CheckMobile::isMobile();
if (file_exists($model->info['view_path'] . DS . 'customs' . DS . 'header_' . $checkout_order["merchant_id"] . '.php')) {
    include(__DIR__ . DS . 'customs' . DS . 'header_' . $checkout_order["merchant_id"] . '.php');
} else {
    ?>
    <div class="col-sm-12">
        <div class="col-xs-12 col-sm-1 col-md-2"></div>
        <div class="col-xs-12 col-sm-10 col-md-8">
            <div class="col-xs-12 col-sm-12 col-md-8">

                <?php if ($device == 'desktop'): ?>
                    <table>
                        <tr>
                            <td>
                                <img src="<?= URL_CHECKOUT_ASSETS .  'vi/checkout/media/checkout-merchant-logo?path=' . $checkout_order['merchant_info']['logo'] ?>"
                                     style="width: 180px">
                            </td>
                            <td>
                                <span style="color: #2b542c; font-weight: bold;"><?= $checkout_order['merchant_info']['display_name'] != '' ? $checkout_order['merchant_info']['display_name'] : $checkout_order['merchant_info']['name'] ?></span>
                            </td>
                        </tr>
                    </table>
                <?php elseif ($device == 'mobile'): ?>
                    <?php if (Yii::$app->controller->action->id == 'verify' && ($model->info['method_code'] == 'QR-CODE')): ?>


                    <?php else: ?>
                        <div class="col-sm-3" style="text-align: center">
                            <img src="<?= URL_CHECKOUT_ASSETS .  'vi/checkout/media/checkout-merchant-logo?path=' . $checkout_order['merchant_info']['logo'] ?>"
                                     style="width: 120px;padding-bottom: 10px; padding-top: 10px">
                            <br/>
                            <span style="color: #2b542c; font-weight: bold;"><?= $checkout_order['merchant_info']['name'] ?></span>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>


            </div>

            <?php // if(Yii::$app->controller->action->id == 'verify'):?>
            <?php if (!in_array($checkout_order['merchant_info']['id'], [149, 140, 141, 167, 193, 194]) || $device == 'desktop' || ($device == 'mobile' && Yii::$app->controller->action->id == 'verify' && ($model->info['method_code'] != 'QR-CODE'))): ?>
                <div class="col-xs-12 col-sm-12 col-md-4 row">
                    <!--            --><?php //if($device=='desktop'):?>
                    <h4 class="title1_1 show-mobile row">
                        <div class="btn-view-order-info text-right">
                            <p><?= Translate::get('Giá trị đơn hàng') ?></p>
                            <strong class="color-vcb mrgr5"><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong><?= $checkout_order['currency'] ?>
                            <?php
                            if ($checkout_order['currency_exchange']) {
                                $currency = json_decode($checkout_order['currency_exchange'], true)
                                ?>
                                <br>
                                <p class="text-info" style="font-size: 13px; margin-top: 5px;">
                                    <i>1 <?= $currency['currency_code'] ?>
                                        ~ <?= ObjInput::makeCurrency($currency['transfer']) ?> <?= $checkout_order['currency'] ?></i>
                                </p>
                            <?php }
                            ?>
                            <i class="fa fa-chevron-down"></i>
                            <i class="fa fa-chevron-up"></i>
                        </div>
                    </h4>
                    <div id="order-info" class="row mform invoiceBg hidden-mobile" role="form">
                        <div class="form-group">
                    <span for=""
                          class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Mã hóa đơn') ?>:</span>
                            <div class="col-sm-8">
                                <p class="form-control-static"> <?= $checkout_order['order_code'] ?></p>
                            </div>
                        </div>
                        <?php if (trim($checkout_order['order_description']) != ''): ?>
                            <div class="form-group">
                        <span for=""
                              class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Mô tả') ?>:</span>
                                <div class="col-sm-8">
                                    <p class="form-control-static"> <?= $checkout_order['order_description'] ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                    <span for=""
                          class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Tổng giá trị') ?>:</span>
                            <div class="col-sm-8">
                                <p class="form-control-static bold color-vcb fontS14"> <?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?> </p>
                            </div>
                        </div>
                        <!--                        <div class="form-group mline mrgb0 cancel-order" align="center">
                                                    <a class="btn btn-default btn-sm" href="<?= Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/cancel', 'token_code' => $checkout_order['token_code']]) ?>"><?= Translate::get('Hủy đơn hàng') ?></a>
                                                </div>-->
                    </div>
                    <!--            --><?php //else:?>
                    <!--                <div style="margin-left: 20px; margin-top: 20px">-->
                    <!--                    <table class="table" style="font-size: 12px">-->
                    <!--                        <tr>-->
                    <!--                            <td style="text-align: right">-->
                    <? //= Translate::get('Mã hóa đơn') ?><!--</td>-->
                    <!--                            <td>--><? //= $checkout_order['order_code'] ?><!--</td>-->
                    <!--                        </tr>-->
                    <!--                        <tr>-->
                    <!--                            <td style="text-align: right">-->
                    <? //= Translate::get('Mô tả') ?><!--</td>-->
                    <!--                            <td>--><? //= $checkout_order['order_description'] ?><!--</td>-->
                    <!---->
                    <!--                        </tr>-->
                    <!--                        <tr>-->
                    <!--                            <td style="text-align: right">-->
                    <? //= Translate::get('Tổng giá trị') ?><!--</td>-->
                    <!--                            <td>-->
                    <? //= ObjInput::makeCurrency($checkout_order['amount']) ?><!-- -->
                    <? //= $checkout_order['currency'] ?><!-- </td>-->
                    <!--                        </tr>-->
                    <!--                    </table>-->
                    <!--                </div>-->
                    <!--            --><?php //endif;?>
                </div>
            <?php endif; ?>
            <?php // endif;?>
        </div>
        <div class="col-xs-12 col-sm-1 col-md-2"></div>
    </div>
    <style>
        .navbar-header, .navbar-collapse {
            background-color: #f8f8f8;
        }

        .navbar-header {
            color: white !important;
        }

        .navbar-inverse:before, .navbar-inverse:after,
        {
            content: none;
        }

        ul.navbar-nav:before, ul.navbar-nav:after {
            content: none;
        }
    </style>
<?php }
?>