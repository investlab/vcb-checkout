<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;
$device = CheckMobile::isMobile();
?>

<?php if (!empty($model->fields)):?>
    <?php
    $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active']]);
    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
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
                <label for="" class="col-sm-3 control-label"></label>
                <div class="col-sm-7">
                    <div class="bankwrap clearfix">
                        <?php if($device=='mobile'):?>
                          <img src="<?=\yii\helpers\Url::base()?>/bank/<?=$model->config['class']?>.png" style="width: 40%">
                        <?php else:?>
                            <img src="<?=\yii\helpers\Url::base()?>/bank/<?=$model->config['class']?>.png" style="width: 20%">
                        <?php endif;?>
                        <p><?=Translate::get($model->info['name'])?></p>
                    </div>


                </div>
            </div>
            <div class="container-fluid">

            <?php foreach ($model->fields as $code => $field) :?>
            <?php if ($code == 'BANK_ACCOUNT') :?>
            <div class="form-group">
                <label for="" class="col-sm-3 control-label"><?=Translate::get('Số thẻ ATM')?>:</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'card_number')->input('text', array('class' => 'form-control', 'maxlength' => 19))->label(false);?>
                </div>
            </div>
            <?php endif;?>
            <?php if ($code == 'BANK_NAME') :?>
            <div class="form-group">
                <label for="" class="col-sm-3 control-label"><?=Translate::get('Tên chủ thẻ')?>:</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'card_fullname')->input('text', array('class' => 'form-control text-uppercase', 'maxlength' => 255))->label(false);?>
                </div>
            </div>
            <?php endif;?>

            <?php if ($code == 'IDENTITY_NUMBER') :?>
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label"><?=Translate::get('Số căn cước/CCCD')?>:</label>
                    <div class="col-sm-7">
                        <?= $form->field($model, 'identity_number')->input('text', array('class' => 'form-control text-uppercase', 'maxlength' => 12,'id' => 'identity_number'))->label(false);?>
                    </div>
                </div>
            <?php endif;?>

            <?php if ($code == 'ISSUE_MONTH') :?>
            <div class="form-group">
                <label for="" class="col-sm-3 col-xs-12 control-label"><?=Translate::get('Ngày phát hành')?>:</label>
                <div class="col-sm-3 col-xs-6">
                    <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control'))->label(false);?>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <?= $form->field($model, 'card_year')->dropDownList($model->getIssueCardYears(), array('class' => 'form-control'))->label(false);?>
                </div>
            </div>
            <?php endif;?>
            <?php if ($code == 'EXPIRED_MONTH') :?>
            <div class="form-group">
                <label for="" class="col-sm-3 col-xs-12 control-label"><?=Translate::get('Ngày hết hạn')?>:</label>
                <div class="col-sm-3 col-xs-6">
                    <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control'))->label(false);?>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control'))->label(false);?>
                </div>
            </div>
            <?php endif;?>
                <?php if ($code == 'MOBILE') :?>
                    <div class="form-group">
                        <label for="" class="col-sm-3 col-xs-12 control-label"><?=Translate::get('Số điện thoại chủ thẻ')?>:</label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'mobile')->input('number', array('class' => 'form-control'))->label(false);?>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
            <div class="form-group">
                <label for="" class="col-sm-3 control-label"><?=Translate::get('Mã bảo mật')?>:</label>
                <div class="col-sm-7">
                <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                    'options' =>['class' => 'form-control text-uppercase', 'maxlength' => 3],
                    'template' => '<div class="row"><div class="col-sm-5 col-xs-7 pdr5">{input}</div><div class="col-sm-7 col-xs-5 pdl5 form-verify-image">{image}</div></div>',
                ])->label(false) ?>
                </div>
            </div>
            </div>
        </div>
    </div>

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
            <?php endif;?>
        </div>
    </div>

<?php else:?>
    <div class="row">
        <div class="form-horizontal mform2 pdtop">
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-1">
                    <?php if ($model->error_message != '') :?>
                        <div class="alert alert-danger"><?=Translate::get($model->error_message)?></div>
                    <?php endif;?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-7">
                    <div class="bankwrap clearfix"><i class="<?=$model->config['class']?>"></i>
                        <div class="cardInfo">
                            <p class=""><?=Translate::get($model->info['name'])?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif;?>