<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Đổi mật khẩu kết nối');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont" xmlns="http://www.w3.org/1999/html">
    <h1 class="titlePage"><?=Translate::get('Đổi mật khẩu kết nối')?></h1>
    <div class="row margin-top-10">
        <div class="col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <ul class="xpay-progress">
                        <li class="active"><span class="number"><b>1</b></span><p><?=Translate::get('Nhập mật khẩu')?></p></li>
                        <li><span class="number"><b>2</b></span><p><?=Translate::get('Hoàn tất')?></p></li>
                    </ul>
                    <?php if ($error != ''):?>
                        <div class="alert alert-danger"><?=$error?></div>
                    <?php endif;?>
                    <?php $form = ActiveForm::begin(['id' => '', 'options' => ['class' => '']]); ?>
                    <div class="form-horizontal pdtop2" role="form">
                        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
                        <div class="form-group">
                            <label class="col-sm-4 col-md-3 control-label" for="formGroupInputLarge"><?=Translate::get('Mật khẩu kết nối mới')?> <span  class="text-danger ">*</span></label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'new_password')->label(false)->passwordInput(array('placeholder' => Translate::get("Mật khẩu kết nối mới")))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 col-md-3 control-label" for="formGroupInputLarge"><?=Translate::get('Nhập lại mật khẩu kết nối')?> <span  class="text-danger ">*</span></label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'confirm_password')->label(false)->passwordInput(array('placeholder' => Translate::get("Nhập lại mật khẩu kết nối mới")))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-4 col-md-3 control-label"><?=Translate::get('Mã bảo mật')?> <span  class="text-danger ">*</span></label>
                            <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                                'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                                'template' => '<div class="col-xs-6 col-sm-4">{input}</div><div class="col-sm-4 pdl5 verify-code">{image}</div><div class="col-sm-offset-3 col-sm-8">',
                            ])->label(false)?>
                            <?='</div>'?>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-4 col-md-offset-3 col-md-3">
                                <button data-loading-text="Loading..." class="btn btn-block btn-primary btn-loading" type="submit"><?=Translate::get('Tiếp tục')?></button>
                            </div>
                        </div>

                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>