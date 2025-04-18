<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Thông tin hoàn tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?= Translate::get('Thông tin hoàn tiền') ?></h1>

    <div class="row">
        <div class="col-md-9">
            <div class="form-horizontal pdtop2 mform" role="form" style="display: contents;">
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-5 col-md-4 control-label bold"> <?= Translate::get('Hoàn lại cho Người mua')?>:</label>
                    <div class="col-sm-7 col-md-8">
                        <div class="form-control-static">
                            <h4 class=" media-heading">
                                <?= isset($checkout_order['buyer_fullname']) && $checkout_order['buyer_fullname'] != null ? $checkout_order['buyer_fullname'] : "" ?>
                            </h4>
                            <div>
                                <i class="fa fa-envelope"></i>
                                <?= isset($checkout_order['buyer_email']) && $checkout_order['buyer_email'] != null ? $checkout_order['buyer_email'] : "" ?>
                            </div>
                            <div>
                                <i class="fa fa-phone"></i>
                                <?= isset($checkout_order['buyer_mobile']) && $checkout_order['buyer_mobile'] != null ? $checkout_order['buyer_mobile'] : "" ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hide-for-xs">
                    <hr>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-5 col-md-4 control-label bold"> <?= Translate::get('Mã token')?>:</label>
                    <div class="col-sm-7 col-md-8">
                        <p class="form-control-static">
                            <?= isset($checkout_order['token_code']) && $checkout_order['token_code'] != null ? $checkout_order['token_code'] : "" ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-5 col-md-4 control-label"> <?= Translate::get('Mã đơn hàng')?>:</label>
                    <div class="col-sm-7 col-md-8">
                        <p class="form-control-static">
                            <?= isset($checkout_order['order_code']) && $checkout_order['order_code'] != null ? $checkout_order['order_code'] : "" ?>
                        </p>
                    </div>
                </div>
                 <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Số tiền đơn hàng')?>:</label>

                    <div class="col-sm-7 col-md-8 pdr5">
                        <p class="form-control-static"><strong class="fontS14 text-success">
                                <?= isset($checkout_order['amount']) && $checkout_order['amount'] != null ? ObjInput::makeCurrency($checkout_order['amount']) : 0 ?>
                            </strong> VND</p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Thời gian thanh toán')?>:</label>
                    <div class="col-sm-7 col-md-8 pdr5">
                        <p class="form-control-static"><?= isset($checkout_order['time_paid']) && intval($checkout_order['time_paid']) > 0 ? date('H:i,d-m-Y', $checkout_order['time_paid']) : '' ?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Hình thức thanh toán')?>:</label>
                    <div class="col-sm-7 col-md-8 pdr5">
                        <p class="form-control-static">
                            <?= isset($checkout_order['payment_method_name']) && $checkout_order['payment_method_name'] != null ? Translate::get($checkout_order['payment_method_name']) : "" ?>
                        </p>
                    </div>
                </div>
                <div class="hide-for-xs">
                    <hr>
                </div>
                <?php
                    $form = ActiveForm::begin(['id' => 'checkout-order-wait-refund-form',
                        'enableAjaxValidation' => false,])
                    ?>
                    <div class="row">
                        <?php if (!empty($error)) {?>
                        <div class="col-lg-12">
                            <div class="alert alert-danger fade in">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong><?= Translate::get('Lỗi') ?></strong> <?= $error ?>.
                            </div>
                        </div>
                        <?php }?>
                        <div class="form-group mrgb0">
                            <label class="col-sm-5 col-md-4 control-label"><?= Translate::get('Loại hoàn tiền') ?><span class="text-danger"> *</span></label>
                            <div class="col-sm-7 col-md-8 pdr5">
                                <?= $form->field($model, 'refund_type')->label(false)
                                    ->dropDownList($refund_type_arr, [
                                        'id' => 'refund_type',
                                        'onchange' => 'if ($(this).val() == 1) {'
                                        . '$("#refund_amount").val(' . $checkout_order['amount'] . ');'
                                        . '$("#refund_amount").attr("disabled","disabled");'
                                        . '$(".field-refund_amount.has-error").removeClass("has-error");'
                                        . '$("p.help-block.help-block-error").text("");'
                                        . '}else{$("#refund_amount").val();$("#refund_amount").removeAttr("disabled");}'
                                    ]); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-5 col-md-4 control-label"><?= Translate::get('Số tiền hoàn lại') ?><span class="text-danger"> *</span></label>
                            <div class="col-sm-7 col-md-8 pdr5">
                                <?= $form->field($model, 'refund_amount')->label(false)
                                    ->input('number', ['class' => 'form-control', 'id' => 'refund_amount']); ?>
                                <span style="color:#737373;">
                                    (Số tiền hoàn lại không lớn hơn <?php echo ObjInput::makeCurrency($checkout_order['amount']) . ' ' . $checkout_order['currency'];?>)
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Lí do hoàn lại')?>:</label>
                            <div class="col-sm-7 col-md-8 pdr5">
                                <?= $form->field($model, 'refund_reason')->label(false)
                                    ->textarea(['class' => 'form-control', 'id' => 'refund_reason', 'placeholder' => 'Nội dung']) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-lg-7 col-md-7">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Xác nhận') ?></button>
                                <a href="<?= Yii::$app->urlManager->createUrl('checkout-order/index') ?>"
                                   class="btn btn-default"><?= Translate::get('Bỏ qua') ?></a>
                            </div>
                        </div>
                    </div>
                    <?= $form->field($model, 'order_id')->label(false)
                        ->hiddenInput(array('class' => 'form-control')) ?>
                <?php ActiveForm::end() ?>
            </div>
            <!--begin button-->
            <div class="pdtop mobile-btn-center">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index')?>" class="btn btn-danger"><?= Translate::get('Quay lại')?></a>
            </div>
        </div>
    </div>

</div>
<script>
    if ($('#refund_type').val() == '1') {
        $("#refund_amount").val(<?php echo $checkout_order['amount']; ?>);
        $("#refund_amount").attr("disabled","disabled");
    }
</script>