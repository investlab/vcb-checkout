<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;

$this->title = Translate::get('Cập nhật danh sách phương thức thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['id' => 'edit-payment-method-form', 'enableAjaxValidation' => true, 'options' => ['enctype' => 'multipart/form-data']]); ?>
    <!-- Start .content-wrapper -->
    <div class=content-wrapper>
        <div class=row>
            <!-- Start .row -->
            <!-- Start .page-header -->
            <div class="col-lg-12 heading">
                <div id="page-heading" class="heading-fixed">
                    <!-- InstanceBeginEditable name="EditRegion1" -->
                    <h1 class="page-header"><?= Translate::get('Cập nhật danh sách phương thức thanh toán') ?></h1>
                    <!-- Start .option-buttons -->
                    <div class="option-buttons">
                        <div class="addNew">
                            <button type="submit" class="btn btn-danger">
                                <i class="fa-save"></i> <?= Translate::get('Lưu') ?>
                            </button>
                            &nbsp;<a class="btn btn-default btn-sm" href="<?= $index_url ?>"><?= Translate::get('Bỏ qua') ?></a></div>
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
                    <!-- Start panel 01 -->
                    <div class="panel panel-primary">
                        <!-- Start .panel -->
                        <div class=panel-heading>
                            <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
                        </div>
                        <div class=panel-body>
                            <div class=form-horizontal role=form>
                                <!-- End .form-group  -->
                                <div class=form-group>
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">
                                        <?= Translate::get('Tên nhóm') ?> :
                                    </label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $method_name ?>
                                    </div>
                                </div>
                                <!-- End .form-group  -->
                                <div class=form-group>
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">
                                        <?= Translate::get('Phương thức') ?>
                                    </label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'payment_id')
                                            ->checkboxList($payment_method,
                                                [
                                                    'item' => function($index, $label, $name, $checked, $value) {
                                                        $checked = $checked ? 'checked' : '';
                                                        return "<div class='col-md-6' style='padding-top: 10px'>
                                                                <input class='noStyle' type='checkbox' {$checked} name='{$name}' value='{$value}' tabindex='3'>
                                                                {$label}
                                                                </div>";
                                                    }
                                                ]
                                            )->label(false); ?>
                                    </div>
                                </div>
                                <!-- End .form-group  -->
                            </div>
                        </div>
                    </div>

                    <div class="addNew">
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-save"></i> <?= Translate::get('Lưu') ?>
                        </button>
                        &nbsp;
                        <a class="btn btn-default" href="<?= $index_url ?>">
                            <?= Translate::get('Bỏ qua') ?>
                        </a>
                    </div>
                </div>

            </div>
            <!-- InstanceEndEditable -->
        </div>
    </div>
<?php ActiveForm::end(); ?>