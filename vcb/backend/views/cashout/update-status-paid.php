<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Cashout;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Chuyển ngân yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Cập nhật chuyển ngân yêu cầu rút tiền') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><i
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
                    $form = ActiveForm::begin(['id' => 'cashout-paid-form',
                        'enableAjaxValidation' => true,
                        'action' => Yii::$app->urlManager->createUrl('cashout/update-status-paid'),
                        'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">
                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant </label>

                            <div class="col-lg-8 col-md-8">
                                <input type="text" disabled class="form-control"
                                       value="<?= @$cashout['merchant_info']['name'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền rút') ?> </label>

                            <div class="col-lg-8 col-md-8">
                                <input type="text" disabled class="form-control"
                                       value="<?= ObjInput::makeCurrency(@$cashout['amount']) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Thời gian chuyển ngân') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'time_paid', [
                                    'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                                ])->label(false)
                                    ->textInput(array('class' => 'form-control datetimepaid')) ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã giao dịch bên ngân') ?>
                                hàng </label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'bank_refer_code')->label(false)
                                    ->textInput(array('class' => 'form-control')) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Phí kênh rút tiền thu của Merchant') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?php echo $form->field($model, 'receiver_fee',
                                    ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                    ->textInput(array('class' => 'form-control input_number',
                                        'value' => ObjInput::makeCurrency(@$cashout['transaction_info']['partner_payment_receiver_fee'])))->label(false); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-5 col-lg-7 col-md-7">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                <a href="<?= Yii::$app->urlManager->createUrl('cashout/index')?>" class="btn btn-default"><?= Translate::get('Bỏ qua') ?></a>
                            </div>
                        </div>

                    </div>
                    <?= $form->field($model, 'id')->label(false)
                        ->hiddenInput(array('class' => 'form-control')) ?>
                    <?php ActiveForm::end() ?>
                </div>
            </div>

        </div>
    </div>
</div>

