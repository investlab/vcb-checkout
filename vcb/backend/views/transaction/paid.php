<?php

use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Transaction;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật thanh toán giao dịch');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Update transaction') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('transaction/index') ?>"><i
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
                    $form = ActiveForm::begin(['id' => 'transaction-paid-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('transaction/paid'),
                                'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-lg-5 col-md-5 col-sm-12 control-label"><?= Translate::get('Transaction ID') ?></label>

                            <div class="col-lg-7 col-md-7">
                                <input class="form-control" disabled name="order_code" value="<?= @$transaction['id'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-5 col-md-5 col-sm-12 control-label"><?= Translate::get('Số tiền giao dịch') ?></label>

                            <div class="col-lg-7 col-md-7">
                                <input class="form-control" disabled name="order_code" value="<?= ObjInput::makeCurrency(@$transaction['amount']) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-5 col-md-5 col-sm-12 control-label"><?= Translate::get('Thời gian thanh toán') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-7 col-md-7">
                                <?=
                                        $form->field($model, 'time_paid', [
                                            'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon">'
                                            . '<i class="fa-calendar"></i></span></div>',
                                        ])->label(false)
                                        ->textInput(array('class' => 'form-control datetimepaid'))
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-5 col-md-5 col-sm-12 control-label"><?= Translate::get('Mã giao dịch bên ngân hàng') ?></label>

                            <div class="col-lg-7 col-md-7">
                                <?php
                                $model->bank_refer_code = $transaction['bank_refer_code'];
                                echo $form->field($model, 'bank_refer_code')->label(false)
                                        ->textInput(array('class' => 'form-control'));
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-5 col-md-5 col-sm-12 control-label">&nbsp;</label>

                            <div class="col-lg-7 col-md-7">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                <a class="btn btn-default"
                                   href="<?= Yii::$app->urlManager->createUrl('transaction/index') ?>"><?= Translate::get('Bỏ
                                    qua') ?></a>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                    <?=
                            $form->field($model, 'id')->label(false)
                            ->hiddenInput()
                    ?>
                    <?php ActiveForm::end() ?>
                </div>
            </div>

        </div>
    </div>
</div>

