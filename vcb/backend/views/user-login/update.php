<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật tài khoản Merchant');
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
                           href="<?= Yii::$app->urlManager->createUrl('user-login/index') ?>"><i
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
            <div class=col-lg-12>
                <!-- Start col-lg-12 -->
                <div class="panel panel-primary">
                    <!-- Start .panel -->
                    <div class=panel-heading>
                        <h4><?= Translate::get('Cập nhật tài khoản Merchant') ?></h4>
                    </div>
                    <div class=panel-body>
                        <div class="form-horizontal" role=form>
                            <?php
                            $form = ActiveForm::begin(['id' => 'update-user-login-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('user-login/update'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class=form-group>
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">Merchant</label>

                                    <div class="col-lg-8 col-md-8">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$user_login['merchant_info']['name'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">Email login</label>

                                    <div class="col-lg-8 col-md-8">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$user_login['email'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Số điện thoại') ?></label>

                                    <div class="col-lg-8 col-md-8">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$user_login['mobile'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Dải IP') ?></label>

                                    <div class="col-lg-8 col-md-8">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$user_login['ips'] ?>">
                                    </div>
                                </div>
                                <!-- End .form-group  -->
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Họ và tên') ?> <span
                                            class="text-danger">*</span> </label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'fullname')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <!-- End .form-group  -->

                                <div class="form-group date">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Ngày sinh/Giới
                                        tính') ?> </label>

                                    <div class="col-lg-4 col-md-4">
                                        <?= $form->field($model, 'birthday', [
                                            'inputTemplate' => '{input} <i class="im-calendar s16 left-input-icon"></i>',
                                        ])->label(false)
                                            ->textInput(array('class' => 'form-control left-icon datepicker', 'placeholder' => Translate::get('Ngày-Tháng-Năm'))) ?>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <?= $form->field($model, 'gender')->dropDownList($gender_arr)->label(false) ?>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">&nbsp;</label>

                                    <div class="col-lg-8 col-md-8">
                                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                        <a href="<?= Yii::$app->urlManager->createUrl('user-login/index') ?>"
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
