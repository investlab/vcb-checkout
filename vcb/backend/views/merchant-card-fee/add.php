<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\MerchantCardFee;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm phí thẻ cào');
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
                           href="<?= Yii::$app->urlManager->createUrl('merchant-card-fee/index') ?>"><i
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
                        <h4><?= Translate::get('Thêm phí thẻ cào') ?></h4>
                    </div>
                    <div class=panel-body>
                        <?php
                        if ($errors != null || $errors != '') {
                            ?>

                            <div class="alert alert-danger fade in">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get($errors) ?>.
                            </div>
                        <?php } ?>
                        <div class="form-horizontal" role=form>
                            <?php
                            $form = ActiveForm::begin(['id' => 'add-merchant-card-fee-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('merchant-card-fee/add'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại thẻ') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'card_type_id')->label(false)->dropDownList($card_type_arr,
                                            [
                                                'id' => 'card_type_id',
                                                'class' => 'form-control',
                                            ]); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại hóa đơn') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'bill_type')->label(false)
                                            ->dropDownList($bill_type_arr, [
                                                'id' => 'bill_type',
                                                'class' => 'form-control',
                                            ]); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kỳ thanh toán') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'cycle_day')->label(false)
                                            ->dropDownList($cycle_day_arr, [
                                                'id' => 'cycle_day',
                                                'class' => 'form-control',
                                            ]); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Đối tác') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'partner_id')->label(false)
                                            ->dropDownList($partner_arr, [
                                                'id' => 'partner_id',
                                                'class' => 'form-control',
                                            ]); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label">Merchant</label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phần trăm phí') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'percent_fee',
                                            ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">%</span>
                                </div>{error}{hint}'])
                                            ->textInput(array('class' => 'form-control', 'value' => 0))->label(false); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Thời gian bắt đầu') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'time_begin', [
                                            'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                                        ])->label(false)
                                            ->textInput(array('class' => 'form-control datetimepaid', 'autocomplete' => 'off')) ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label">&nbsp;</label>

                                    <div class="col-lg-7 col-md-7">
                                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                        <a class="btn btn-default"
                                           href="<?= Yii::$app->urlManager->createUrl('merchant-card-fee/index') ?>"><?= Translate::get('Bỏ
                                            qua') ?></a>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <br>

                            <?php ActiveForm::end() ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

