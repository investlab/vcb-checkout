<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\CheckoutOrderBackup;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật thanh toán');
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
                           href="<?= Yii::$app->urlManager->createUrl('checkout-order-backup/index') ?>"><i
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
                        <h4><?= Translate::get('Cập nhật thanh toán') ?></h4>
                    </div>
                    <div class=panel-body>

                        <div class="form-horizontal" role=form>
                            <?php
                            $form = ActiveForm::begin(['id' => 'checkout-order-paid-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('checkout-order-backup/update-status-paid'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã đơn hàng') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$checkout_order['order_code'] ?>">
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label">Merchant </label>

                                    <div class="col-lg-7 col-md-7">
                                        <input type="text" disabled class="form-control"
                                               value="<?= @$checkout_order['merchant_info']['name'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Số tiền đơn hàng') ?> </label>

                                    <div class="col-lg-7 col-md-7">
                                        <input type="text" disabled class="form-control"
                                               value="<?= ObjInput::makeCurrency(@$checkout_order['amount']) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Thời gian thanh toán') ?><span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'time_paid', [
                                            'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                                        ])->label(false)
                                            ->textInput(array('class' => 'form-control datetimepaid')) ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã giao dịch kênh thanh
                                        toán') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'bank_refer_code')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-lg-7 col-md-7">
                                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                        <a href="<?= Yii::$app->urlManager->createUrl('checkout-order-backup/index') ?>"
                                           class="btn btn-default"><?= Translate::get('Bỏ qua') ?></a>
                                    </div>
                                </div>

                            </div>
                            <?= $form->field($model, 'id')->label(false)
                                ->hiddenInput(array('class' => 'form-control')) ?>
                            <?= $form->field($model, 'transaction_id')->label(false)
                                ->hiddenInput(array('class' => 'form-control', 'value' => @$checkout_order['transaction_id'])) ?>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


