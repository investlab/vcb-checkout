<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\db\ProductCategory */
/* @var $form ActiveForm */
$this->title = 'Cập nhật menu';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper update">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header>Cập nhật quyền hệ thống</h1>
            </div>
        </div>
    </div>
    <div class="outlet">
        <div class="form-horizontal" role=form>
            <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>
            <?php if ($error_message != ""): ?>
            <div class="alert alert-warning fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>Lưu ý!</strong> <?= $error_message ?>.
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tên quyền<span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'title')->textInput(array('class' => 'form-control', 'placeholder' => 'Tên quyền', 'readonly'=>''))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Mã quyền<span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'refer_code')->textInput(array('class' => 'form-control text-uppercase', 'placeholder' => 'Mã quyền', 'readonly'=>''))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Mô tả</label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'properties')->textInput(array('class' => 'form-control', 'placeholder' => 'Mô tả'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tiêu đề</label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'title')->textInput(array('class' => 'form-control', 'placeholder' => 'Tiêu đề'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Liên kết</label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'link')->textInput(array('class' => 'form-control', 'placeholder' => 'Liên kết'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Vị trí<span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'position')->textInput(array('class' => 'form-control col-sm-2', 'placeholder' => 'Vị trí'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Trạng thái<span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'status')->dropDownList($status_array, ['id' => 'status', 'class' => 'form-control'])->label(false); ?>
                </div>
            </div>
            <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable pdtop8">
                <?= Html::submitButton('Cập nhật', ['class' => 'btn btn-primary']) ?>
                <a class="btn btn-default" data-dismiss="modal" href="<?= Yii::$app->urlManager->createUrl(['menu/index']) ?>">Bỏ qua</a>	
            </div>
            <?php ActiveForm::end(); ?>
        </div>		
    </div>
</div>