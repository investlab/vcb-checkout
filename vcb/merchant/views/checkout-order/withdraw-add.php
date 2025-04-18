<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 6/11/2018
 * Time: 13:37
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\Cashout;
use common\models\db\Method;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\components\utils\Utilities;

$this->title = Translate::get('Lịch sử yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage">Rút tiền về tài khoản Ngân Hàng</h1>
    <div class="row pdtop2">
        <div class="col-md-9">
            <?php if ($error_message != ''):?>
            <div class="alert alert-danger"><?=$error_message?></div>
            <?php endif;?>
            <?php $form = ActiveForm::begin(['id' => '', 'options' => ['class' => '']]); ?>
                <div class="form-horizontal pdtop2" role="form">
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Số dư khả dụng')?>:</label>
                        <div class="col-sm-7">
                            <p class="form-control-static"><strong class="fontS15"><?= ObjInput::makeCurrency(common\models\db\Account::getBalance(UserLogin::get('merchant_id'), 'VND')) ?></strong> đ</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Hình thức rút')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'payment_method_id')->label(false)->dropDownList($payment_methods, ['onChange' => "setPaymentMethod(this.value);", 'class' => 'form-control'])?>
                        </div>
                    </div>
                    <?php $payment_method_info = $model->getPaymentMethodInfo()?>
                    <?php if (Method::isWithdrawIBOffline($method_code)) :?>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Số tiền yêu cầu rút')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'amount')->label(false)->input('text', array('placeholder' => Translate::get('Số tiền yêu cầu rút'), 'class' => 'form-control input_number'))?>
                            <div class="help-block mrgb0">Số tiền tối thiểu <?= ObjInput::makeCurrency($payment_method_info['min_amount'])?> đ</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Số tài khoản')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'bank_account_code')->label(false)->input('text', array('placeholder' => Translate::get('Số tài khoản'), 'class' => 'form-control'))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Tên chủ tài khoản')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'bank_account_name')->label(false)->input('text', array('placeholder' => Translate::get('Tên chủ tài khoản'), 'class' => 'form-control'))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Chi nhánh')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'bank_account_branch')->label(false)->input('text', array('placeholder' => Translate::get('Chi nhánh'), 'class' => 'form-control'))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Tỉnh thành')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'zone_id')->label(false)->dropDownList($model->getZones(),['class' => 'form-control'])?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?=Translate::get('Mã bảo mật')?> <span  class="text-danger ">*</span></label>
                        <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                            'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                            'template' => '<div class="col-sm-3">{input}</div><div class="col-sm-4 pdl5 verify-code">{image}</div><div class="col-sm-offset-4 col-sm-8">',
                        ])->label(false)?>
                        <?='</div>'?>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-3">
                            <button data-loading-text="Loading..." class="btn btn-block btn-primary btn-loading" type="submit"><?=Translate::get('Rút tiền')?></button>
                        </div>
                        <div class="col-sm-3 pdl5">
                            <a class="btn btn-default" href="<?=Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-add'])?>"><?=Translate::get('Thực hiện lại')?></a>
                        </div>
                    </div>
                    <?php elseif (Method::isWithdrawWallet($method_code)):?>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Số tiền yêu cầu rút')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'amount')->label(false)->input('text', array('placeholder' => Translate::get('Số tiền yêu cầu rút'), 'class' => 'form-control input_number'))?>
                            <div class="help-block mrgb0">Số tiền tối thiểu <?= ObjInput::makeCurrency($payment_method_info['min_amount'])?> đ</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="formGroupInputLarge"><?=Translate::get('Email tài khoản')?> <span  class="text-danger ">*</span></label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'bank_account_code')->label(false)->input('text', array('placeholder' => Translate::get('Email tài khoản'), 'class' => 'form-control'))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?=Translate::get('Mã bảo mật')?> <span  class="text-danger ">*</span></label>
                        <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                            'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                            'template' => '<div class="col-sm-3">{input}</div><div class="col-sm-3 pdl5 verify-code">{image}</div><div class="col-sm-offset-4 col-sm-7">',
                        ])->label(false)?>
                        <?='</div>'?>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-3">
                            <button data-loading-text="Loading..." class="btn btn-block btn-primary btn-loading" type="submit"><?=Translate::get('Rút tiền')?></button>
                        </div>
                        <div class="col-sm-3 pdl5">
                            <a class="btn btn-default" href="<?=Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-add'])?>"><?=Translate::get('Thực hiện lại')?></a>
                        </div>
                    </div>
                    <?php else:?>
                    <?php if (intval($model->payment_method_id) > 0):?>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-7">
                            <div class="alert alert-danger">Hệ thống hiện chưa hỗ trợ rút tiền với hình thức này!</div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php endif;?>                   
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    function setPaymentMethod(value) {
        document.location.href="<?=Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/withdraw-add'])?>?payment_method_id="+value;
    }
</script>