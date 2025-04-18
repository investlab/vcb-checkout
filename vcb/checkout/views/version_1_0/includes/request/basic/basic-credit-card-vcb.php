<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
?>
<div class="panel-heading rlv">
    <div class="logo-method">
        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-','_', strtolower($model->info['method_code'])) . '.png'?>" alt="loading...">
    </div>
    <h4 class="panel-title color-vcb"><strong><?=Translate::get('Thanh toán qua thẻ Visa / MasterCard / JCB / Amex')?></strong></h4>
</div>
<?php
    if (!isset($form)) {
        $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active']]);
        echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
        echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
    }
?>
<div class="row">
    <div class="form-horizontal">
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-1">
                <?php if ($model->error_message != '') :?>
                    <div class="alert alert-danger"><?=Translate::get($model->error_message)?></div>
                <?php endif;?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Ngân hàng')?>:</label>
            <div class="col-sm-7">
                <div class="bankwrap clearfix"><i class="<?=$model->config['class']?>"></i>
                    <div class="cardInfo">
                        <p class="hidden-xs"><?=Translate::get($model->info['name'])?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Số thẻ')?>:</label>
            <div class="col-sm-7">
            <?= $form->field($model, 'card_number')->input('text', array('class' => 'form-control input-size', 'maxlength' => 19))->label(false);?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Tên chủ thẻ')?>:</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'card_fullname')->input('text', array('class' => 'form-control text-uppercase input-size', 'maxlength' => 255,'placeholder'=>Translate::get('Nhập tên in trên thẻ, viết hoa không dấu')))->label(false);?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?=Translate::get('Ngày hết hạn')?>:</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control input-size', 'id' => 'expMonth'))->label(false);?>
            </div>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control input-size','id' => 'expYear'))->label(false);?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?=Translate::get('Mã CVV/CVC2')?>:</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_cvv')->input('password', array('class' => 'form-control input-numeric input-size', 'maxlength' => 4))->label(false);?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Mã bảo mật')?>:</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                    'options' =>['class' => 'form-control text-uppercase input-size', 'maxlength' => 3],
                    'template' => '<div class="row"><div class="col-sm-5 col-xs-7 pdr5">{input}</div><div class="col-sm-7 col-xs-5 pdl5 form-verify-image">{image}</div></div>',
                ])->label(false) ?>
            </div>
        </div>
    </div>
</div>
<div class="hide-for-xs hidden-mobile"><hr></div>
<div class="row">
    <div class="form-horizontal mform0">
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
                            <strong><?= ObjInput::makeCurrency($model->getPayerFee())?></strong> <?=$model->merchant_fee_info['currency']?>
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
    </div>
</div>