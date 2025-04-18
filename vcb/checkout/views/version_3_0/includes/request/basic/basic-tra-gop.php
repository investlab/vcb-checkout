<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
?>
<style>
    .radio-toolbar {
        margin: 10px;
    }

    .radio-toolbar input[type="radio"] {
        opacity: 0;
        position: fixed;
        width: 0;
    }

    .radio-toolbar label {
        display: inline-block;
        background-color: #fff;
        padding: 5px 28px;
        font-family: sans-serif, Arial;
        font-size: 16px;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
    }

    .radio-toolbar label:hover {
        background-color: #dfd;
    }

    .radio-toolbar input[type="radio"]:focus + label {
        border: 2px dashed #444;
    }

    .radio-toolbar input[type="radio"]:checked + label {
        background-color: #FFF;
        border-color: #4c4;
    }



</style>

<div class="panel-heading rlv">
    <div class="logo-method">
        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-','_', strtolower($model->info['method_code'])) . '.png'?>" alt="loading...">
    </div>
    <h4 class="panel-title color-vcb"><strong><?=Translate::get('Thanh toán trả góp')?></strong></h4>
</div>

    <?php
    $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active']]);
    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
    ?>
<?php //if (!empty($model->fields)):?>
    <div class="row">
        <div class="form-horizontal">
            <div class="form-group">
                <label for="" class="col-sm-3 control-label"><?=Translate::get('Ngân hàng')?>:</label>
                <div class="col-sm-7">
                    <div class="bankwrap clearfix"><i class="<?=$model->config['class']?>"></i>
                        <div class="cardInfo">
                            <p class="hidden-xs"><?=Translate::get($model->info['name'])?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-sm-3 control-label"><?=Translate::get('Loại thẻ')?>:</label>
                <div class="col-sm-7" >
                    <ul class="cardList clearfix ">
                        <?php foreach ($model->getBank($model->payment_method_code) as $item => $value){?>
                            <li>
                                <div class="boxWrap" >
                                    <a  class="btn-card" id = "<?php echo $value ?>-bank">
                                        <i class="<?=$value?>"></i>
                                    </a>
                                </div>

                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <label id = "label"  class="col-sm-3 control-label" style="top: 13px;display: none"><?=Translate::get('Kỳ hạn trả góp')?>:</label>

                <div class="col-sm-7" style="position: relative; min-height: 70px;">


                    <?php foreach ($model->getBank($model->payment_method_code) as $item => $value){?>

                    <div class="radio-toolbar" id="<?= $value?>" style="display: none; position: absolute; top: 0px;">
                        <?php foreach ($model->getCardInfo($model->payment_method_code,$value)[$value] as $item1 => $value1){?>

                        <input id = "<?= $value1 ?>" type="radio"  name="card_info" value="<?= $value1 ?>" >
                        <label for="<?= $value1 ?>"><?= $item1 ?> tháng</label>

                        <?php } ?>

                    </div>
                    <p>&nbsp;</p>
                    <?php } ?>
                </div>

            </div>
        </div>
    <div class="hide-for-xs hidden-mobile"><hr></div>
    <div class="row">
        <div class="form-horizontal mform0">
            <?php if ($model->getPayerFee() != 0 || \common\models\db\Merchant::hasViewFeeFree($checkout_order['merchant_info'])): ?>
                <div class="form-group mrgb0 mline hidden-mobile">
                    <label for="" class="col-sm-4 control-label"><?= Translate::get('Giá trị đơn hàng') ?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <strong><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong> <?= $checkout_order['currency'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0 mline hidden-mobile">
                    <label for="" class="col-sm-4 control-label"><?= Translate::get('Phí thanh toán') ?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?php if ($model->getPayerFee() != 0) : ?>
                                <strong><?= ObjInput::makeCurrency($model->getPayerFee()) ?></strong> <?= $model->merchant_fee_info['currency'] ?>
                            <?php else: ?>
                                <strong><?= Translate::get('Miễn phí') ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 col-xs-6 control-label"><?= Translate::get('Tổng tiền') ?>:</label>
                <div class="col-sm-8 col-xs-6">
                    <p class="form-control-static fontS14 bold text-danger"> <strong><?= ObjInput::makeCurrency($model->getPaymentAmount()) ?> <?= $checkout_order['currency'] ?></strong> </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
       $('#JCB-bank').click(function () {
           $('#label').css('display','block');
           $('#JCB').css('display','block');
           $('#VISA').css('display','none');
           $('#MASTERCARD').css('display','none');
       });
        $('#VISA-bank').click(function () {
           $('#VISA').css('display','block');
           $('#label').css('display','block');
           $('#JCB').css('display','none');
           $('#MASTERCARD').css('display','none')
       });
        $('#MASTERCARD-bank').click(function () {
            $('#label').css('display','block');
           $('#MASTERCARD').css('display','block');
           $('#VISA').css('display','none');
           $('#JCB').css('display','none');
       });
    })
</script>


