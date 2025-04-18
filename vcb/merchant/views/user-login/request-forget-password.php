<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;

use common\components\utils\Translate;
$this->title = "Quên tài khoản đăng nhập merchant";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?= Translate::get('Quên mật khẩu/xác thực tài khoản') ?></h1>
    <div class="RegisterBox">
        <div class="help-block" style="text-align: center"><?= Translate::get('Để lấy lại mật khẩu đăng nhập/link kích hoạt tài khoản Vietcombank, bạn nhập chính xác địa chỉ email đã đăng ký tài khoản Vietcombank vào form dưới đây. Hệ thống sẽ gửi một đường link kích hoạt tới email của bạn để xác thực yêu cầu.') ?></div>
        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
        <div class="whitebox">
            <div class="panel-body">
                <?php if ($error != ''):?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif;?>
                <?php $form = ActiveForm::begin(['id' => 'request-forget-password', 'options' => ['class' => '','style' => 'display:grid'],'action' => '',]); ?>
                    <div class="form-group">
                        <label for="inputEmail" class="col-sm-4 control-label label-request-forget-password"><?= Translate::get('Email đăng nhập') ?>:</label>
                        <div class="col-sm-8">
                            <?= $form->field($model, 'email')->label(false)->textInput(array('placeholder' => Translate::get("Email tài khoản đăng nhập")))?>

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inpuCapcha" class="col-sm-4 control-label label-request-forget-password"><?=Translate::get('Mã bảo mật')?> <span  class="text-danger ">*</span></label>
                        <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                            'options' => ['class' => 'form-control right-icon text-uppercase col-sm-8', 'maxlength' => 3],
                            'template' => '<div class="col-xs-6 col-sm-5">{input}</div><div class="col-sm-3 pdl5 verify-code">{image}</div><div class="col-sm-offset-4 col-sm-8">',
                        ])->label(false)?>
                        <?='</div>'?>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-4 pdr5">
                            <button type="submit" class="btn btn-block btn-success "><?= Translate::get('Hoàn tất') ?></button>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>