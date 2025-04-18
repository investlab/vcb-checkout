<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

$this->title = Translate::get("Đăng nhập tài khoản Merchant");
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?=Translate::get('Đăng nhập tài khoản Merchant')?><br><br></h1>
    <div class="LoginBox">
        <div class="well well-sm">
            <div class="panel-body">
                <?php if ($error != ''):?>
                <div class="alert alert-danger"><?=Translate::get($error)?></div>
                <?php endif;?>
                <?php $form = ActiveForm::begin(['id' => 'login-form', 'options' => ['class' => '']]); ?>
                    <div class="form" role="form">
                        <div class="form-group">
                            <label class="" for="email"><?=Translate::get('Email tài khoản')?></label>
                            <?= $form->field($model, 'username')->label(false)->textInput(array('placeholder' => Translate::get("Email tài khoản")))?>
                        </div>
                        <div class="form-group">
                            <label class="" for="passwword"><?=Translate::get('Mật khẩu')?></label>
                            <?= $form->field($model, 'password')->label(false)->passwordInput()?>
                        </div>
                        <div class="form-group">
                            <label class="" for="capcha"><?=Translate::get('Mã bảo mật')?></label>
                            <div class="capcha clearfix">
                                <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                                        'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                                        'template' => '{input}<div class="col-sm-4 pdl5 verify-code">{image}</div><div class="clearfix"></div>',
                                    ])->label(false)?>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-primary"><?=Translate::get('Đăng nhập')?></button>
                        </div>
                        <div class="hidden">
                            <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['user-login/request-forget-password'])?>" class="linktxt"><?=Translate::get('Quên mật khẩu đăng nhập')?></a>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <!--div align="center"><a class="btn btn-default btn-block" href=""><?=Translate::get('Đăng ký tài khoản tại đây')?></a></div-->
    </div>
</div>
