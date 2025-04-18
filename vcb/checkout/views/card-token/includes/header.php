<?php
use common\components\utils\Translate;
use common\components\utils\ObjInput;
use common\components\utils\CheckMobile;
$device =  CheckMobile::isMobile();
?>
<div class="col-sm-12">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8">
        <div class="col-xs-12 col-sm-12 col-md-8">

            <?php if($device=='desktop'):?>
                <table>
                    <tr>
                        <td>
                            <?php if (trim($checkout_order['merchant_info']['logo']) != '' && file_exists(IMAGES_MERCHANT_PATH . $checkout_order['merchant_info']['logo'])) : ?>
                                <img class="col-sm-5" src="<?= IMAGES_MERCHANT_URL . $checkout_order['merchant_info']['logo'] ?>" style= "width: 180px">
                            <?php else:?>
                                <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo_default.png" style= "width: 180px">
                            <?php endif;?>
                        </td>
                        <td><span style="color: #2b542c; font-weight: bold;"><?= $checkout_order['merchant_info']['name'] ?></span></td>
                    </tr>
                </table>
            <?php elseif($device=='mobile'):?>
                <div class="col-sm-3" style="text-align: center">
                    <?php if (trim($checkout_order['merchant_info']['logo']) != '' && file_exists(IMAGES_MERCHANT_PATH . $checkout_order['merchant_info']['logo'])) : ?>
                        <img  src="<?= IMAGES_MERCHANT_URL . $checkout_order['merchant_info']['logo'] ?>" style= "width: 200px;padding-bottom: 10px; padding-top: 10px">
                    <?php else: ?>
                        <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo_default.png" style= "width: 200px;padding-bottom: 10px; padding-top: 10px">
                    <?php endif; ?>
                    <br/>
                    <span style="color: #2b542c; font-weight: bold;"><?= $checkout_order['merchant_info']['name'] ?></span>
                </div>
            <?php endif;?>



        </div>

        <div class="col-xs-12 col-sm-12 col-md-4 row">
            <?php if($device=='desktop'):?>
                <h4 class="title1_1 show-mobile row">
                    <div class="btn-view-order-info text-right">
                        <p><?= Translate::get('Giá trị đơn hàng') ?></p>
                        <strong class="color-vcb mrgr5"><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong><?= $checkout_order['currency'] ?>
                        <i class="fa fa-chevron-down"></i>
                        <i class="fa fa-chevron-up"></i>
                    </div>
                </h4>
                <div id="order-info" class="row mform invoiceBg hidden-mobile" role="form">
                    <div class="form-group">
                        <span for="" class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Mã hóa đơn') ?>:</span>
                        <div class="col-sm-8">
                            <p class="form-control-static"> <?= $checkout_order['order_code'] ?></p>
                        </div>
                    </div>
                    <?php if (trim($checkout_order['order_description']) != ''): ?>
                        <div class="form-group">
                            <span for="" class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Mô tả') ?>:</span>
                            <div class="col-sm-8">
                                <p class="form-control-static"> <?= $checkout_order['order_description'] ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <span for="" class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Tổng giá trị') ?>:</span>
                        <div class="col-sm-8">
                            <p class="form-control-static bold color-vcb fontS14"> <?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?> </p>
                        </div>
                    </div>
                    <!--                        <div class="form-group mline mrgb0 cancel-order" align="center">
                                                    <a class="btn btn-default btn-sm" href="<?= Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/cancel', 'token_code' => $checkout_order['token_code']]) ?>"><?= Translate::get('Hủy đơn hàng') ?></a>
                                                </div>-->
                </div>
            <?php else:?>
                <div style="margin-left: 20px; margin-top: 20px">
                    <table class="table" style="font-size: 12px">
                        <tr>
                            <td style="text-align: right"><?= Translate::get('Mã hóa đơn') ?></td>
                            <td><?= $checkout_order['order_code'] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><?= Translate::get('Mô tả') ?></td>
                            <td><?= $checkout_order['order_description'] ?></td>

                        </tr>
                        <tr>
                            <td style="text-align: right"><?= Translate::get('Tổng giá trị') ?></td>
                            <td><?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?> </td>
                        </tr>
                    </table>
                </div>
            <?php endif;?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
</div>