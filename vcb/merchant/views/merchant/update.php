<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Cập nhật thông tin merchant');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont" xmlns="http://www.w3.org/1999/html">
    <h1 class="titlePage"><?=Translate::get('Cập nhật thông tin merchant')?></h1>
    <div class="row margin-top-10">
        <div class="col-xs-12 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php if ($error != ''):?>
                        <div class="alert alert-danger"><?=Translate::get($error)?></div>
                    <?php endif;?>
                    <?php if ($model->getMessage() != ''):?>
                        <div class="alert alert-success"><?=Translate::get($model->getMessage())?></div>
                    <?php endif;?>
                    <?php $form = ActiveForm::begin(['id' => '', 'options' => ['enctype' => 'multipart/form-data']]); ?>
                    <div class="form-horizontal" role="form">
                        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('Tên merchant')?> <span class="text-danger">(*)</span></label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'name')->label(false)->textInput(array('placeholder' => "Tên merchant"))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('Địa chỉ website')?>:</label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'website')->label(false)->textInput(array('placeholder' => "Địa chỉ website"))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('Email nhận thông báo')?>:</label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'email_notification')->label(false)->textInput(array('placeholder' => "Email nhận thông báo"))?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('URL nhận thông báo')?>:</label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'url_notification')->label(false)->textInput(array('placeholder' => "URL nhận thông báo"))?>
                            </div>
                        </div>
                        <!--div class="form-group">
                        <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('Số điện thoại nhận thông báo')?>:</label>
                        <div class="col-sm-7">
                            <?= $form->field($model, 'mobile_notification')->label(false)->textInput(array('placeholder' => "Số điện thoại nhận thông báo"))?>
                        </div>
                    </div-->
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="formGroupInputLarge"><?=Translate::get('Logo')?>:</label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'logo')->label(false)->fileInput()?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"><?=Translate::get('Mã bảo mật')?> :</label>
                            <?= $form->field($model, 'verifyCode')->widget(\common\components\libs\MTQCaptcha::className(), [
                                'options' => ['class' => 'form-control right-icon text-uppercase', 'maxlength' => 3],
                                'template' => '<div class="col-sm-4">{input}</div><div class="col-sm-4 pdl5 verify-code">{image}</div><div class="col-sm-offset-3 col-sm-8">',
                            ])->label(false)?>
                            <?='</div>'?>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-4">
                                <button data-loading-text="Loading..." class="btn btn-block btn-primary btn-loading" type="submit"><?=Translate::get('Cập nhật')?></button>
                            </div>
                        </div>

                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>