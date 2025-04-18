<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\MerchantFee;
use common\models\db\Method;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm yêu cầu rút tiền cho đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$method_code = $model->getMethodCode();
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Thêm yêu cầu rút tiền cho đơn hàng') ?></h1>
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
                <?php
                if ($errors != null || $errors != '') {
                    ?>

                    <div class="alert alert-danger fade in">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?= Translate::get('Thông báo') ?></strong> <?= $errors ?>.
                    </div>
                <?php } ?>
                <div class="form-horizontal" role=form>
                    <?php
                    $form = ActiveForm::begin(['id' => 'add-cashout-form',
                        'enableAjaxValidation' => true,
                        'action' => Yii::$app->urlManager->createUrl('cashout/add-checkout-order'),
                        'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">

                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Phương thức rút tiền') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'payment_method_id')->label(false)->dropDownList($payment_method_arr,
                                    [
                                        'id' => 'payment_method_id',
                                        'class' => 'form-control',
                                        'onchange' => 'cashout.changeMethod(this);',
                                        'data-url' => Yii::$app->urlManager->createUrl(['cashout/add-checkout-order'])
                                    ]); ?>
                            </div>
                        </div>
                        <?php if (Method::isWithdrawATMCard($method_code)) : ?>
                            <div class=form-group>
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant<span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']) ?>
                                </div>
                            </div>

                            <div class="form-group" id="classAmount">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền rút') ?><span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?php echo $form->field($model, 'amount',
                                        ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                        ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                </div>
                            </div>

                            <div class="form-group" id="classBankAccountCode">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số thẻ') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_code')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                        
                            <div class="form-group" id="classBankAccountName">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên chủ thẻ') ?>
                                    <span class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_name')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            
                            <div class="form-group" id="classBankCardMonth">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tháng trên thẻ') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_card_month')->label(false)->dropDownList($model->getCardMonths(), array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classBankCardYear">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Năm trên thẻ') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_card_year')->label(false)->dropDownList($model->getCardYears(), array('class' => 'form-control')) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <a class="btn btn-default"
                                       href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><?= Translate::get('Bỏ
                                        qua') ?></a>
                                </div>
                            </div>
                        <?php elseif (Method::isWithdrawIBOffline($method_code)) : ?>
                            <div class=form-group>
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant<span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classAmount">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền rút') ?><span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?php echo $form->field($model, 'amount',
                                        ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                        ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                </div>
                            </div>

                            <div class="form-group" id="classBankAccountName">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên chủ tài khoản') ?>
                                    <span class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_name')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classBankAccountCode">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tài khoản') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_code')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classBankAccountBranch">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên chi nhánh') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_branch')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classZone">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tỉnh/Thành phố') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'zone_id')->label(false)->dropDownList($model->getZones(), array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <a class="btn btn-default"
                                       href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><?= Translate::get('Bỏ
                                        qua') ?></a>
                                </div>
                            </div>
                        <?php
                        elseif (Method::isWithdrawWallet($method_code)) : ?>
                            <div class=form-group>
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant<span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']) ?>
                                </div>
                            </div>
                            <div class="form-group" id="classAmount">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền rút') ?><span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?php echo $form->field($model, 'amount',
                                        ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                        ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                </div>
                            </div>

                            <div class="form-group" id="classBankAccountCode">
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Email ví điện tử') ?>
                                    <span class="text-danger ">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'bank_account_code')->label(false)->textInput(array('class' => 'form-control')) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <a class="btn btn-default"
                                       href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><?= Translate::get('Bỏ
                                        qua') ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
            </div>

        </div>
    </div>
</div>

