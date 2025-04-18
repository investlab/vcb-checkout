<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm mới phương thức thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Thêm mới phương thức thanh toán') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('payment-method/index') ?>"><i
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
            <div class=col-lg-2>
            </div>
            <div class=col-lg-6>
                <div class="form-horizontal" role=form>
                    <?php
                    $form = ActiveForm::begin(['id' => 'add-payment-method-form',
                        'enableAjaxValidation' => true,
                        'action' => Yii::$app->urlManager->createUrl('payment-method/add'),
                        'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">
                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Ngân hàng/Ví điện tử') ?> <span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'bank_id')->label(false)->dropDownList($model->getBanks(),
                                    [
                                        'id' => 'bank_id',
                                        'class' => 'form-control',
                                    ]); ?>
                            </div>
                        </div>
                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Nhóm phương thức') ?> <span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'method_id')->label(false)->dropDownList($model->getMethods(),
                                    [
                                        'id' => 'method_id',
                                        'class' => 'form-control',
                                    ]); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên') ?> <span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'name')->label(false)
                                    ->textInput(array('class' => 'form-control')) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Cấu hình') ?></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'config')->label(false)
                                    ->textInput(array('class' => 'form-control')) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền tối thiểu') ?> <span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'min_amount')->label(false)
                                    ->textInput(array('class' => 'form-control')) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mô tả') ?></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'description')->label(false)
                                    ->textarea(array('class' => 'form-control')) ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">&nbsp;</label>

                            <div class="col-lg-8 col-md-8">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                <a href="<?= Yii::$app->urlManager->createUrl('payment-method/index') ?>"
                                   class="btn btn-default">
                                    <?= Translate::get('Bỏ qua') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $('#bank_id').select2();
</script>