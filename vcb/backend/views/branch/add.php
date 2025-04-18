  <?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm mới chi nhánh');
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
                           href="<?= Yii::$app->urlManager->createUrl('branch/index') ?>"><i
                                class="en-back"></i> <?= Translate::get('Quay lại')?>
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
                        <h4><?= Translate::get('Thêm mới chi nhánh') ?></h4>
                    </div>
                    <div class=panel-body>
                        <div class="form-horizontal" role=form>
                            <?php
                            $form = ActiveForm::begin(['id' => 'add-branch-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('branch/add'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Tên chi nhánh') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'name')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Tỉnh/thành phố') ?> <span
                                                class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'city')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">&nbsp;</label>

                                    <div class="col-lg-8 col-md-8">
                                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                        <a href="<?= Yii::$app->urlManager->createUrl('branch/index') ?>"
                                           class="btn btn-default"><?= Translate::get('Bỏ qua') ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
