<?php

use common\components\utils\Translate;
use common\components\utils\ObjInput;
use common\components\utils\CheckMobile;

$device = CheckMobile::isMobile();
//\yii\helpers\VarDumper::dump(Yii::$app->controller->action->id == 'verify',10,true);die();
?>
<div class="col-sm-12 header-logo">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8" style="margin-bottom: 50px">
        <div class="col-xs-6 col-sm-6 col-md-6">
            <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo/logo_vcb.png" name="logo-vcb">
        </div>
        <div class="col-xs-6 col-sm-6 col-md-6">
            <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo/fwd.png" name="logo-fwd">
        </div>
    </div>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
</div>
<div class="col-sm-12">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8" style="padding-right: 50px">
        <div class="col-xs-12 col-sm-12 col-md-8"></div>
        <!--        --><?php //// if(Yii::$app->controller->action->id == 'verify'):
        //        echo '<pre>';
        //        print_r($device);
        //        print_r(Yii::$app->controller->action->id);
        //        print_r($model->info['method_code']);
        //        die;?>
        <?php if ($device == 'desktop' || ($device == 'mobile' && (Yii::$app->controller->action->id != 'verify' || $model->info['method_code'] == 'QR-CODE'))): ?>
            <div class="col-xs-12 col-sm-12 col-md-4 row">
                <!--            --><?php //if($device=='desktop'):?>
                <h4 class="title1_1 show-mobile row" style="height: 50px;margin-bottom: 50px">
                    <div class="btn-view-order-info text-right" style="font-size: 15px">
                        <p><?= Translate::get('Số tiền thanh toán') ?></p>
                        <strong class="color-vcb mrgr5"><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong><?= $checkout_order['currency'] ?>
                        <?php

                        if ($checkout_order['currency_exchange']) {
                            $currency = json_decode($checkout_order['currency_exchange'], true)
                            ?>
                            <br>
                            <p class="text-info" style="font-size: 13px; margin-top: 5px;">
                                <i>1 <?= $currency['currency_code'] ?>
                                    ~ <?= ObjInput::makeCurrency($currency['sell']) ?> <?= $checkout_order['currency'] ?></i>
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
                                                    <a class="btn btn-default btn-sm" href="<?= Yii::$app->urlManager->createAbsoluteUrl([
                        Yii::$app->controller->id . '/cancel',
                        'token_code' => $checkout_order['token_code']
                    ]) ?>"><?= Translate::get('Hủy đơn hàng') ?></a>
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

    .header-logo img {
        width: 180px;
        height: 80px;
        margin-top: 50px;
    }

    .header-logo img[name=logo-vcb] {
        float: left;
        margin-left: 20px;
    }

    .header-logo img[name=logo-fwd] {
        float: right;
        margin-right: 20px;
    }

    /*@media only screen and (max-width: 480px) {*/
    /*    .header-logo img {*/
    /*        width: 100px;*/
    /*        height: 50px;*/
    /*        margin: 50px 0 0 0 !important;*/
    /*    }*/
    /*}*/

    @media only screen and (max-width: 576px) {
        .header-logo img {
            width: 100px;
            height: 50px;
            margin: 30px 0 0 0 !important;
        }

        div.btn-view-order-info.text-right p {
            font-size: 13px !important;
        }

        strong.color-vcb.mrgr5 {
            font-size: 15px !important;
        }

        #accordion {
            margin-top: 0px !important;
        }

        .header-logo + div.col-sm-12 > div.col-xs-12.col-sm-10.col-md-8 {
            padding: 0px !important;
        }

        .header-logo div.col-xs-12.col-sm-10.col-md-8, h4.title1_1.show-mobile.row {
            margin-bottom: 20px !important;
        }
    }

</style>
