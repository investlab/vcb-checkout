<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\utils\Translate;
$this->title = Translate::get('Thêm quyền hệ thống');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper add">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header><?= Translate::get('Thêm quyền hệ thống') ?></h1>
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
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên quyền') ?><span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'name')->textInput(array('class' => 'form-control'))->label(false); ?>
                </div>
            </div>            
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã quyền') ?><span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'code')->textInput(array('class' => 'form-control text-uppercase'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Quyền cấp cha') ?><span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'parent_id')->dropDownList($parent_id_array, ['id' => 'parent_id', 'class' => 'form-control'])->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mô tả') ?></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'description')->textInput(array('class' => 'form-control'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tiêu đề') ?></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'title')->textInput(array('class' => 'form-control'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Liên kết') ?></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'link')->textInput(array('class' => 'form-control'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Vị trí') ?><span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'position')->textInput(array('class' => 'form-control'))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Trạng thái') ?><span class="text-danger">*</span></label>
                <div class="col-lg-9 col-md-9">
                    <?php echo $form->field($model, 'status')->dropDownList($status_array, ['id' => 'status', 'class' => 'form-control'])->label(false); ?>
                </div>
            </div>            
            <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable pdtop8">
                <?= Html::submitButton(Translate::get('Thêm'), ['class' => 'btn btn-primary']) ?>
                <a class="btn btn-default" data-dismiss="modal" href="<?= Yii::$app->urlManager->createUrl(['right/index']) ?>"><?= Translate::get('Bỏ qua') ?></a>	
            </div>
            <?php ActiveForm::end(); ?>
        </div>		
    </div>
</div>
<script>
    $('#parent_id').select2();
</script>