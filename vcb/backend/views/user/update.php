<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật quản trị');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(['id' => 'update-user-form',
    'options' => ['enctype' => 'multipart/form-data'],
    'enableAjaxValidation' => true,
]); ?>
<div class=content-wrapper>
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header><?= Translate::get('Cập nhật quản trị viên') ?></h1>

                <div class="option-buttons">
                    <div class="addNew">
                        <?= Html::submitButton('<i class="fa-save"></i>'. Translate::get('Lưu'), ['class' => 'btn btn-danger btn-sm', 'name' => 'update-button']) ?>
                        &nbsp;
                        <a href="<?= Yii::$app->urlManager->createUrl('user/index') ?>"
                           class="btn btn-default btn-sm"><?= Translate::get('Bỏ qua') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class=row>
            <div class=col-lg-12>
                <!-- Start col-lg-12 -->
                <?php
                if ($message != '') {
                    ?>
                    <div class="alert alert-danger fade in">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get($message) ?>.
                    </div>
                <?php } ?>
                <div class="panel panel-primary">
                    <!-- Start .panel -->
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
                    </div>
                    <div class=panel-body>
                        <div class="form-horizontal" role=form>
                            <!-- End .form-group  -->
                            <div class=form-group>
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Họ và tên') ?> <span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'fullname')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <!-- End .form-group  -->
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Tên đăng nhập') ?> <span
                                        class="text-danger">*</span> </label>

                                <div class="col-lg-8 col-md-8">
                                    <input class="form-control" name="username" value="<?= $model->username ?>"
                                           readonly=""/>
                                </div>
                            </div>
                            <!-- End .form-group  -->
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label">Email <span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'email')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Số di động') ?> <span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'mobile')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Số cố định') ?> </label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'phone')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
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
                                <div class="col-lg-4 col-md-4">
                                    <?= $form->field($model, 'gender')->dropDownList($user_gender)->label(false) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 col-md-2 col-sm-12 control-label"><?= Translate::get('Chi nhánh') ?> </label>


                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'branch_id')->dropDownList($branchs, ['class' => 'form-control'])->label(false) ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-12" style="padding:20px 0px 30px">
                    <?= Html::submitButton('<i class="fa-save"></i>'.Translate::get('Lưu'), ['class' => 'btn btn-danger btn-sm', 'name' => 'update-button']) ?>
                    &nbsp;
                    <a href="<?= Yii::$app->urlManager->createUrl('user/index') ?>"
                       class="btn btn-default btn-sm"><?= Translate::get('Bỏ qua') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $form->field($model, 'id')->label(false)
    ->hiddenInput(array('class' => 'form-control')) ?>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    function setDistrict(obj) {
        $(obj).attr('disabled', true);
        $('#district_id').attr('disabled', true);
        $.get('<?php echo Yii::$app->urlManager->createUrl('get-ajax/get-district-by-zone-id'); ?>', {
            zone_id: obj.value,
            district_id: 0
        }, function (data) {
            $('#district_id').attr('disabled', false);
            if (data) {
                $('#district_id').html(data);
            }
            $(obj).attr('disabled', false);
        });
    }
    function setZone(obj) {
        $(obj).attr('disabled', true);
        $('#zone_id').attr('disabled', true);
        $.get('<?php echo Yii::$app->urlManager->createUrl('get-ajax/get-wards-by-district-id'); ?>', {
            district_id: obj.value,
            wards_id: 0
        }, function (data) {
            $('#zone_id').attr('disabled', false);
            if (data) {
                $('#zone_id').html(data);
            }
            $(obj).attr('disabled', false);
        });
    }
</script>