<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
$merchant=\common\models\db\Merchant::findOne($checkout_order['merchant_id']);
?>
<!--<h4 class="payopt-title btmLine hidden-xs"> --><?//=Translate::get('Online bằng thẻ ATM ngân hàng nội địa')?><!--</h4>-->
<?php
$form = ActiveForm::begin(['id' => 'form-checkout', 'options' => ['class' => 'active']]);
//print_r($checkout_order); exit();
?>
    <div class="row" style="padding-top: 30px;">
        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-7">

                    <div class="bankwrap clearfix"> <img src="<?=\yii\helpers\Url::base()?>/bank/<?=$model->config['class']?>.png">
                        <div class="cardInfo">
                            <p class="hidden-xs"><?=Translate::get($model->info['name'])?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-7"> <?=Translate::get('Hệ thống ngân hàng vừa gửi cho bạn một Mã xác thực vào số điện thoại mà bạn đã đăng ký khi mở thẻ. Nhập mã đó vào form dưới đây để xác thực giao dịch.')?> </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-1">
                <?php if ($model->error_message != '') :?>
                    <div class="alert alert-danger"><?=$model->error_message?></div>
                <?php endif;?>
                </div>
            </div>
            <div class="form-group">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Mã xác thực OTP')?>:</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'otp')->input('text', array('class' => 'form-control', 'maxlength' => 8))->label(false);?>
                </div>
            </div>
            <div class="form-group">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Mã bảo mật')?>: </label>
                <div class="col-sm-7">
                <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                    'options' =>['class' => 'form-control text-uppercase', 'maxleng' => 3],
                    'template' => '<div class="row"><div class="col-sm-5 col-xs-7 pdr5">{input}</div><div class="col-sm-7 col-xs-5 pdl5 form-verify-image">{image}</div></div>',
                ])->label(false) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="hide-for-xs"><hr></div>
    <div class="row">
        <div class="form-horizontal">
            <?php if ($model->getPayerFee() != 0 || \common\models\db\Merchant::hasViewFeeFree($checkout_order['merchant_info'])):?>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Giá trị đơn hàng')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <strong><?= ObjInput::makeCurrency($checkout_order['amount'])?></strong> <?=$checkout_order['currency']?>
                    </p>
                </div>
            </div>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Phí thanh toán')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <?php if ($model->getPayerFee() != 0) :?>
                        <strong><?= ObjInput::makeCurrency($model->getPayerFee())?></strong> <?=$checkout_order['currency']?>
                        <?php else:?>
                        <strong><?=Translate::get('Miễn phí')?></strong>
                        <?php endif;?>
                    </p>
                </div>
            </div>
            <?php endif;?>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 col-xs-6 control-label"><?=Translate::get('Tổng tiền')?>:</label>
                <div class="col-sm-8 col-xs-6">
                    <p class="form-control-static fontS14 bold text-danger"> <strong><?= ObjInput::makeCurrency($model->getPaymentAmount())?> <?=$checkout_order['currency']?></strong> </p>
                </div>
            </div>
            <div id="btn-payment" style="text-align: center">
                <div class="col-sm-4"></div>
                <div class="col-sm-8" style="text-align: center">

                    <?php
                    $btn_return  = '';
                    $btn_cancel = '';
                    $colpayment = 4;
                    $colother = 3;
                    if ($merchant->exception=='no_return'){
                        $btn_return = 'none';
                        $colpayment = 6;
                        $colother = 4;
                    }elseif ($merchant->exception=='no_cancel'){
                        $btn_cancel='none';
                        $colnumber = 6;
                        $colother = 4;
                    }
                    ?>

                    <button class="col-sm-12 col-md-<?=$colpayment?> col-xs-12 btn" type="submit" name="pay-button" id="pay-button">TIẾP TỤC</button>
                    <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/cancel", "token_code" => $checkout_order['token_code']], HTTP_CODE)?>" style="display: <?=$btn_return?>">
                        <div class="col-sm-12 col-md-<?=$colother?> col-xs-12 btn" id="return-btn">
                            <span>CHỌN LẠI</span>
                        </div>
                    </a>
                    <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/transaction-destroy", "token_code" => $checkout_order['token_code']], HTTP_CODE)?>" style="display: <?=$btn_cancel?>">
                        <div class="col-sm-12 col-md-<?=$colother?> col-xs-12 btn" id="cancel-btn">
                            <span style="color: #c7254e">HỦY THANH TOÁN</span>
                        </div>
                    </a>



                </div>
                <div class="col-sm-1"></div>
            </div>

        </div>
    </div>
<?php ActiveForm::end();?>