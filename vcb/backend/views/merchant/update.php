<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Merchant;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật Merchant');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>&nbsp;</h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('merchant/index') ?>"><i
                                class="en-back"></i> <?= Translate::get('Quay lại') ?>
                        </a>
                    </div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class=row>
            <div class="panel panel-primary">
                <!-- Start .panel -->
                <div class=panel-heading>
                    <h4><?= Translate::get('Cập nhật Merchant') ?></h4>
                </div>
                <div class=panel-body>
                    <div class="form-horizontal" role=form>
                        <?php
                        $form = ActiveForm::begin(['id' => 'update-merchant-form',
                            'enableAjaxValidation' => true,
                            'action' => Yii::$app->urlManager->createUrl('merchant/update'),
                            'options' => ['enctype' => 'multipart/form-data']])
                        ?>
                        <div class="row">
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Tên Merchant') ?> <span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'name')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Merchant ID') ?> <span
                                            class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'merchant_code')->label(false)
                                        ->textInput(array('class' => 'form-control', 'maxlength' => 11,'placeholder' => Translate::get('Merchant ID được cung cấp bởi Vietcombank'))) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">Logo</label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'logo')->label(false)->fileInput(['id' => 'my-img']) ?>
                                    <img id='my-img-pre'
                                        src="<?= $merchant['logo'] != null ? $logo_url . $merchant['logo'] : $logo_url . 'no-image.jpg' ?>"
                                        name="logo" width="80" height="80">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">Website</label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'website')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Điện thoại') ?></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'mobile_notification')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">Email</label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'email_notification')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">URL nhận thông báo</label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'url_notification')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Chi nhánh')?></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'branch_id')->dropDownList($branchs, ['class' => 'form-control'])->label(false) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Token 3D-Secure')?></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'active3D')->dropDownList($active3D_arr, ['class' => 'form-control'])->label(false) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Luồng thanh toán')?></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'payment_flow')->dropDownList($payment_arr, ['class' => 'form-control'])->label(false) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">&nbsp;</label>

                                <div class="col-lg-8 col-md-8">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <a href="<?= Yii::$app->urlManager->createUrl('merchant/index') ?>"
                                       class="btn btn-default"><?= Translate::get('Bỏ qua') ?></a>
                                </div>
                            </div>
                        </div>
                        <?= $form->field($model, 'id')->label(false)
                            ->hiddenInput() ?>
                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript'>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#my-img-pre').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    $("#my-img").change(function(){
        readURL(this);
    });
</script>