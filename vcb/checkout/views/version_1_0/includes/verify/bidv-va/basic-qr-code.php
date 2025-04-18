<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
$device = \common\components\utils\CheckMobile::isMobile();
$no_change_text = 'Tại màn hình chuyển tiền, Quý khách vui lòng <strong>không thay đổi</strong> thông tin số tiền và nội dung chuyển tiền!';

?>
<?php if($device == 'mobile'): ?>

    <div class="row form-qr-code" style="margin-top: 0; padding: 0 15px 15px 15px">
<?php else:?>
    <div class="row form-qr-code">

<?php endif;?>

    <div class="col-md-12" style="margin-top: 0 !important;">
        <div class="panel-heading rlv" style="padding: 0; ">
            <?php if(Yii::$app->controller->action->id == 'verify' && $device == 'mobile' && ( $model->info['method_code'] == 'QR-CODE') ):?>
<!--                <h3 style="font-family: sans-serif; margin: 0; padding-left: 20px;-->
<!--                 font-weight: 600" class="text-left">Quét QR thanh toán</h3>-->

            <?php else:?>
                <div class="panel-heading rlv">
                    <div class="logo-method">
                        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-', '_', strtolower($model->info['method_code'])) . '.png' ?>" alt="loading...">
                    </div>
                    <h4 class="panel-title color-vcb"><strong><?= Translate::get($model->info['name']) ?></strong></h4>
                </div>
            <?php endif;?>
        </div>
    </div>
    <div class="col-sm-12">

        <div class="col-sm-4 text-center"><i class="<?= $model->config['class'] ?>"></i> </div>
        <div class="col-sm-8">&nbsp;</div>
    </div>
    <div class="col-sm-12">
        <div class="col-sm-4 ">
            <div class="qr-code text-center" style="background: none">
                <img style="width: 75%;border: 1px solid #dcdcdc;margin: auto;"
                        src="<?= $model->payment_transaction['partner_payment_info']['qr_data'] ?>" class="img img-responsive">
            </div>
            <p>&nbsp;</p>
            <?php if(Yii::$app->controller->action->id == 'verify' && $device == 'mobile' && ( $model->info['method_code'] == 'QR-CODE') ):?>
                <div class="text-center">

                    <i class="text-center" style="color: orange; font-weight: initial; font-size: 10pt"><?=Translate::get('Khách hàng chỉ quét 01 lần để thanh toán')?> </i>
                    <br>
                    <i class="text-center" style="color: orange; font-weight: initial; font-size: 10pt"><?=Translate::get($no_change_text)?> </i>
                </div>
                    <div class="text-center">
                        <h3 class="form-control-static  bold"
                            style="padding: 0!important;margin: 10pt 0 !important; color: #097fe6c2">
                            <?= ObjInput::makeCurrency($model->getPaymentAmount()) ?> <?= $checkout_order['currency'] ?></h3>
                    </div>
                <div style=" font-size: small;margin-bottom: 50px;">
                    <p><span style="color: #90BE4E"><?= Translate::get('Bước')?> 1:</span> <?= Translate::get('Khách hàng sử dụng Internet banking trên điện thoại, bật tính năng quét mã QRcode và quét mã QR đang hiển thị')?> </p>
                    <p><span style="color: #90BE4E"><?= Translate::get('Bước')?> 2:</span> <?= Translate::get('Thu ngân/Khách hàng kích vào nút "Kiểm tra giao dịch" để chủ động kiểm tra trạng thái của giao dịch')?> </p>
                </div>

                <p class="text-uppercase" >
                    <button class="btn btn-primary"
                            style="width: 100% !important; background-color: #90BE4E; border: none; padding: 10px 0;"
                            type="button" onclick="document.location.reload();"><?= Translate::get('Kiểm tra giao dịch') ?></button>
                </p>

            <?php endif; ?>

        </div>
        <?php if(Yii::$app->controller->action->id == 'verify' && $device == 'mobile' && ( $model->info['method_code'] == 'QR-CODE') ):?>

        <?php else:?>
            <div class="col-sm-8 alert alert-warning">
                <div class="form-horizontal">
                    <?php if ($model->getPayerFee() != 0 || \common\models\db\Merchant::hasViewFeeFree($checkout_order['merchant_info'])): ?>
                        <p>&nbsp;</p>
                        <div class="form-group mrgb0 mline ">
                            <label for="" class="col-sm-6 control-label"><?= Translate::get('Giá trị đơn hàng') ?>:</label>
                            <div class="col-sm-6">
                                <p class="form-control-static">
                                    <strong><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong> <?= $checkout_order['currency'] ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group mrgb0 mline ">
                            <label for="" class="col-sm-6 control-label"><?= Translate::get('Phí thanh toán') ?>:</label>
                            <div class="col-sm-6">
                                <p class="form-control-static">
                                    <?php if ($model->getPayerFee() != 0) : ?>
                                        <strong><?= ObjInput::makeCurrency($model->getPayerFee()) ?></strong> <?= $checkout_order['currency'] ?>
                                    <?php else: ?>
                                        <strong><?= Translate::get('Miễn phí') ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="form-group mline">
                            <label for="" class="col-sm-6 col-xs-6 control-label"><?= Translate::get('Tổng tiền') ?>:</label>
                            <div class="col-sm-6 col-xs-6">
                                <?php if($device == 'mobile'):?>
                                    <p class="form-control-static fontS14 bold text-danger" style="padding: 0!important;">
                                        <strong><?= ObjInput::makeCurrency($model->getPaymentAmount()) ?> <?= $checkout_order['currency'] ?></strong> </p>
                                <?php else:?>
                                    <p class="form-control-static fontS14 bold text-danger">
                                        <strong><?= ObjInput::makeCurrency($model->getPaymentAmount()) ?> <?= $checkout_order['currency'] ?></strong> </p>
                                <?php endif;?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mline">
                        <p>&nbsp;</p>
                        <p class="text-center text-uppercase medium"><?= Translate::get('Để hoàn thành thanh toán, bạn sử dụng ứng dụng Mobile Banking của ngân hàng để Quét.') ?></p>
                        <p class="text-center text text-danger text-uppercase"><b><?= Translate::get($no_change_text) ?></b></p>

                        <p class="text-center text text-danger text-uppercase"><b><?= Translate::get('Xin vui lòng KHÔNG ĐÓNG TRÌNH DUYỆT!') ?></b></p>
                        <p>&nbsp;</p>

                        <p class="text-center  text-uppercase">
                            <span class=" text text-warning"><img src="<?= ROOT_URL ?>checkout/web/images/022.gif" alt=""> <?= Translate::get('Chờ quét mã QR') ?></span>
                        </p>
                        <p class="text-center text-uppercase">
                            <button class="btn btn-primary btn-vcb-color" type="button" onclick="document.location.reload();"><?= Translate::get('Kiểm tra giao dịch') ?></button>
                        </p>
                        <p>&nbsp;</p>
                    </div>

                </div>
            </div>

        <?php endif;?>
    </div>
</div>



<script type="text/javascript">
    setTimeout('document.location.reload();', 5000);
</script>