<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\libs\MTQCaptcha;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Xác nhận yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?=Translate::get('Xác nhận yêu cầu rút tiền')?></h1>
    <div class="row pdtop2">
        <div class="col-md-9">
            <div class="form-horizontal pdtop2 mform" role="form"> 
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label bold"><?=Translate::get('Số tài khoản')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><strong class="fontS14 text-primary">
                                <?= isset($cashout['bank_account_code']) && $cashout['bank_account_code'] != null ? $cashout['bank_account_code'] : "" ?>
                        </strong></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Tên chủ tài khoản')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= isset($cashout['bank_account_name']) && $cashout['bank_account_name'] != null ? $cashout['bank_account_name'] : "" ?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Chi nhánh')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= isset($cashout['bank_account_branch']) && $cashout['bank_account_branch'] != null ? $cashout['bank_account_branch'] : "" ?></p>
                    </div>
                </div>
                <div class="hide-for-xs"><hr></div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Mã yêu cầu')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= isset($cashout['id']) && $cashout['id'] != null ? $cashout['id'] : "" ?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"><?=Translate::get('Thời gian')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><span class="text-danger">
                                <?= isset($cashout['time_begin']) && intval($cashout['time_begin']) > 0 ? date('H:i,d-m-Y', $cashout['time_begin']) : '' ?>
                        </span> <?=Translate::get('đến')?> <span class="text-danger"><?= isset($cashout['time_end']) && intval($cashout['time_end']) > 0 ? date('H:i,d-m-Y', $cashout['time_end']) : '' ?></span></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Số tiền rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><strong class="fontS14 text-primary"> <?= isset($cashout['amount']) && $cashout['amount'] != null ? ObjInput::makeCurrency($cashout['amount']) : 0 ?></strong> VND</p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Phí rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><?= isset($cashout['receiver_fee']) && $cashout['receiver_fee'] != null ? ObjInput::makeCurrency($cashout['receiver_fee']) : 0 ?>
                            &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND']?></p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Số tiền nhận được')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"><strong class="fontS14 text-success">
                                <?=  ObjInput::makeCurrency(@$cashout['amount'] - @$cashout['receiver_fee']) ?>
                            </strong> VND</p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "><?=Translate::get('Hình thức rút')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static"> <?= isset($cashout['payment_method_info']['name']) && $cashout['payment_method_info']['name'] != null ? $cashout['payment_method_info']['name'] : "" ?></p>
                    </div>
                </div>
                <div class="hide-for-xs"><hr></div>

                <?php $form = ActiveForm::begin(['id' => 'withdraw-verify-form', 'options' => ['class' => 'form-horizontal p25']]); ?>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label"><?=Translate::get('Mã bảo mật')?> <span  class="text-danger ">*</span></label>
                        <?= $form->field($model, 'verifyCode')->widget(MTQCaptcha::className(), [
                            'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                            'template' => '<div class="col-sm-4">{input}</div><div class="col-sm-4 pdl5 verify-code">{image}</div><div class="col-sm-offset-3 col-sm-8">',
                        ])->label(false)?>
                        <?='</div>'?>
                    </div>
                    <div class="form-group noneMrbtm">
                        <div class="col-sm-offset-4 col-sm-3 pdr5">
                            <button type="submit" class="btn btn-block btn-success"><?=Translate::get('Xác nhận')?></button>
                        </div>
                        <div class="col-sm-3 pdl5">
                            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/withdraw') ?>" class="btn btn-danger"><?=Translate::get('Quay lại')?></a>
                        </div>
                    </div>
                <?= $form->field($model, 'cashout_id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>