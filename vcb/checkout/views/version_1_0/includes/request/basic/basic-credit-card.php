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
    <h4 class="panel-title color-vcb"><strong><?=Translate::get('Thanh toán bằng thẻ Visa / MasterCard')?></strong></h4>
</div>
<?php if (!empty($model->fields)):?>
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
                        <div class="alert alert-danger"><?=$model->error_message?></div>
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
            <span style="display: none" id="flex_key" ><?= !empty($model->checkout_order['flex_key'])? $model->checkout_order['flex_key']: '' ?></span>
            <?php foreach ($model->fields as $code => $field) :?>
                <?php if ($code == 'BANK_ACCOUNT') :?>
                    <div class="form-group">
                        <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label" id="cardNumber-label"><?=Translate::get('Số thẻ')?>:</label>
                        <div class="col-sm-7">
                            <div id="cardNumber-container" class="form-control"></div>
                        </div>
                    </div>
                <?php endif;?>
                <?php if ($code == 'BANK_NAME') :?>
                    <div class="form-group">
                        <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Tên chủ thẻ')?>:</label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'card_fullname')->input('text', array('class' => 'form-control text-uppercase input-size', 'maxlength' => 255))->label(false);?>
                        </div>
                    </div>
                <?php endif;?>
                <?php if ($code == 'EXPIRED_MONTH') :?>
                    <div class="form-group">
                        <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?=Translate::get('Ngày hết hạn')?>:</label>
                        <div class="col-sm-3 col-xs-6">
                            <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control input-size', 'id' => 'expMonth'))->label(false);?>
                        </div>
                        <div class="col-sm-3 col-xs-6">
                            <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control input-size','id' => 'expYear'))->label(false);?>
                        </div>
                    </div>
                <?php endif;?>
                <?php if ($code == 'CARD_CVV') :?>
                    <div class="form-group">
                        <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?=Translate::get('Mã CVV/CVC2:')?>:</label>
                        <div class="col-sm-7" id="card-cvv-input">
                            <?= $form->field($model, 'card_cvv')->input('password', array('class' => 'form-control input-numeric cvv-info input-size', 'maxlength' => 4))->label(false);?>
                            <a id="card_cvv_info" href="#" onclick="return false;" class="col-xs-5 col-sm-4 input-group-addon bold input-size" data-toggle="popover" data-placement="right" data-trigger="focus" title="Mã CVV/CVC2" data-content="Là mã số bí mật gồm 3 đến 4 chữ số được in phía sau thẻ của bạn">?</a>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
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
    <input type="hidden" id="flex-response" name="flex-response" value="">
    <input type="hidden" id="create-token-res" name="create-token-res">
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

<?php else:?>
    <div class="row">
        <div class="form-horizontal mform2 pdtop">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-7">
                    <div class="bankwrap clearfix"><i class="<?=$model->config['class']?>"></i>
                        <div class="cardInfo">
                            <p class=""><?=Translate::get($model->info['name'])?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-1">
                    <?php if ($model->error_message != '') :?>
                        <div class="alert alert-danger"><?=Translate::get($model->error_message)?></div>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
    <div class="hide-for-xs"><hr></div>
    <div class="row">
        <div class="form-horizontal mform0">
            <div class="form-group mrgb0 mline">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Phí thanh toán')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <?php if ($model->payer_fee != 0) :?>
                            <strong><?= ObjInput::makeCurrency($model->merchant_fee_info['sender_flat_fee'])?></strong> <?=$model->merchant_fee_info['currency']?> + <?=$model->merchant_fee_info['sender_percent_fee']?>%
                        <?php else:?>
                            <strong><?=Translate::get('Miễn phí')?></strong>
                        <?php endif;?>
                    </p>
                </div>
            </div>
            <div class="form-group mrgb0 mline">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Tổng tiền')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static fontS14 bold text-danger"> <strong><?= ObjInput::makeCurrency($model->getPaymentAmount())?> <?=$checkout_order['currency']?></strong> </p>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.4.0/flex-microform.min.js"></script>
<?php
    $this->registerJsFile('js/flex_cybersource.js');
?>
