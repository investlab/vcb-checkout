<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thông tin tài khoản');
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Start .content-wrapper -->
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Thông tin tài khoản') ?></h1>
                <!-- Start .option-buttons -->

                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class=row>
            <div class=col-lg-12>
                <!-- Start col-lg-12 -->
                <div class="panel panel-default plain">
                    <div class="panel-heading white-bg"></div>
                    <!-- Start .panel -->
                    <div class=panel-body>
                        <?php $form = ActiveForm::begin(['id' => 'update-user-form',
                            'options' => ['enctype' => 'multipart/form-data'],
                            'enableAjaxValidation' => true,
                            'action' => Yii::$app->urlManager->createUrl('default/update')
                        ]); ?>

                        <div class="form-horizontal" role=form>
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Tên đăng nhập') ?></label>

                                <div class="col-lg-4 col-md-4">
                                    <p class="form-control-static"><?= $users->username ?></p>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Mật khẩu') ?></label>

                                <div class="col-lg-4 col-md-4">
                                    <p class="form-control-static"><a href="#change_pass" data-toggle="modal"><i
                                                class="fa-refresh"></i> <?= Translate::get('Đổi mật khẩu') ?></a></p>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Họ và tên') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'fullname')->label(false)
                                        ->textInput(array('class' => 'form-control', 'value' => $users->fullname)) ?>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">Email <span class="text-danger">*</span></label>

                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'email')->label(false)
                                        ->textInput(array('class' => 'form-control', 'value' => $users->email)) ?>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Số di động') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'mobile')->label(false)
                                        ->textInput(array('class' => 'form-control', 'value' => $users->mobile)) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Số cố định') ?> </label>
                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'phone')->label(false)
                                        ->textInput(array('class'=>'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group date">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Ngày sinh /Giới tính') ?></label>
                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'birthday', [
                                        'inputTemplate' => '{input} <i class="im-calendar s16 left-input-icon"></i>',
                                    ])->label(false)
                                        ->textInput(array('class' => 'form-control left-icon datepicker', 'placeholder' => Translate::get('Ngày-Tháng-Năm'))) ?>
                                </div>
                                <div class="col-lg-2 col-md-2">
                                    <?= $form->field($model, 'gender')->dropDownList($user_gender)->label(false) ?>
                                </div>
                            </div>

                            <div class=form-group>
                                <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                                    <?= Html::submitButton('<i class="fa-save"></i> '.Translate::get('Lưu'), ['class' => 'btn btn-danger btn-sm', 'name' => 'update-button']) ?>&nbsp;
                                    <a href="<?= Yii::$app->urlManager->createUrl('default/index')?>" class="btn btn-default btn-sm"><?= Translate::get('Bỏ qua') ?></a>
                                </div>
                            </div>
                            <!-- End .form-group  -->
                        </div>
                        <?= $form->field($model, 'id')->label(false)
                            ->hiddenInput(array('class' => 'form-control')) ?>

                        <?php ActiveForm::end() ?>
                    </div>
                </div>

            </div>


        </div>

        <!-- Reset Mật khẩu User -->
        <div class="modal fade" id="change_pass" tabindex=-1 role=dialog aria-hidden=true>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?= Translate::get('Reset mật khẩu') ?></h4>
                    </div>
                    <div class="modal-body">
                        <!-- content in modal, tinyMCE 4 texarea -->
                        <?php
                        $form = ActiveForm::begin(['id' => 'edit-user-pass',
                            'enableAjaxValidation' => true,
                            'action' => Yii::$app->urlManager->createUrl('default/change-pass'),
                            'options' => [
                                'enctype' => 'multipart/form-data',
                                'data-pjax' => '']]);
                        ?>
                        <div class="form-horizontal" role=form>
                            <!-- End .form-group  -->

                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mật khẩu cũ') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-9 col-md-9">
                                    <?= $form->field($model_pass, 'password')->label(false)
                                        ->passwordInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mật khẩu mới') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-9 col-md-9">
                                    <?= $form->field($model_pass, 'newPass')->label(false)
                                        ->passwordInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Nhập lại mật khẩu mới') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-9 col-md-9">
                                    <?= $form->field($model_pass, 'rePass')->label(false)
                                        ->passwordInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                            </div>

                            <!-- End .form-group  -->
                        </div>
                        <?php ActiveForm::end() ?>
                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>


        <!-- InstanceEndEditable -->
    </div>

</div>
