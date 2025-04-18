<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\db\Product */
/* @var $form ActiveForm */
$this->title = 'Cập nhật tin tức';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'options' => ['enctype' => 'multipart/form-data']]); ?>
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class="page-header">Cập nhật tin tức</h1>

                <div class="option-buttons">
                    <div class="addNew"><?= Html::submitButton('Cập nhật', ['class' => 'btn btn-danger']) ?>&nbsp;
                        <a class="btn btn-default btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl(['news/index']) ?>">Bỏ qua</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class=row>
            <div class=col-lg-12>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title>Thông tin chung</h3>
                    </div>
                    <div class=panel-body>
                        <div class="row">
                            <div class="col-md-12">
                                <div class=form-horizontal role=form>
                                    <div class=form-group>
                                        <label class="col-lg-2 col-md-2 col-sm-12 control-label">Tiêu đề tin<span
                                                class="text-danger">*</span></label>

                                        <div class="col-lg-4 col-md-4">
                                            <?php echo $form->field($model, 'title')->textInput(array('class' => 'form-control', 'placeholder' => 'Tiêu đề tin'))->label(false); ?>
                                        </div>

                                        <label class="col-lg-2 col-md-2 col-sm-12 control-label">Danh mục tin tức<span
                                                class="text-danger">*</span></label>

                                        <div class="col-lg-4 col-md-4">
                                            <?php echo $form->field($model, 'news_category_id')->dropDownList($news_category, ['class' => 'form-control'])->label(false); ?>
                                        </div>
                                    </div>

                                    <div class=form-group>
                                        <label class="col-lg-2 col-md-2 col-sm-12 control-label">Ảnh</label>

                                        <div class="col-lg-4 col-md-4">
                                            <?= $form->field($model, 'image')->label(false)->fileInput() ?>
                                            <img
                                                src="<?= $model->image != null ? $image_url . $model->image : $image_url . 'no-image.jpg' ?>"
                                                width="80" height="80">
                                        </div>

                                        <label class="col-lg-2 col-md-2 col-sm-12 control-label">Thời gian đăng
                                            tin</label>

                                        <div class="col-lg-4 col-md-4">
                                            <?= $form->field($model, 'time_publish', [
                                                'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                                            ])->label(false)
                                                ->textInput(array('class' => 'form-control datepicker')) ?>
                                        </div>
                                    </div>

                                    <div class=form-group>
                                        <label class="col-lg-2 col-md-2 col-sm-12 control-label">Rewrite Rule</label>

                                        <div class="col-lg-4 col-md-4">
                                            <?php echo $form->field($model, 'rewrite_rule')->textInput(array('class' => 'form-control', 'placeholder' => 'home.html'))->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title>Mô tả</h3>
                    </div>
                    <div class=panel-body>
                        <div class=form-horizontal role=form>
                            <div class=form-group>
                                <?php echo $form->field($model, 'description')->textarea(array('rows' => 15, 'class' => 'form-control', 'placeholder' => ''))->label(false); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title>Nội dung</h3>
                    </div>
                    <div class=panel-body>
                        <div class=form-horizontal role=form>
                            <div class=form-group>
                                <?php echo $form->field($model, 'content')->textarea(array('class' => 'form-control ckeditor'))->label(false); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
                <div style="padding:10px 0px 30px"><?= Html::submitButton('Cập nhật', ['class' => 'btn btn-danger']) ?>
                    &nbsp;
                    <a class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl(['news/index']) ?>">Bỏ
                        qua</a></div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>