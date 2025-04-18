<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\PartnerPaymentAccount;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm tài khoản kênh thanh toán');
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
                           href="<?= Yii::$app->urlManager->createUrl('partner-payment-account/index') ?>"><i
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
                        <h4><?= Translate::get('Thêm tài khoản kênh thanh toán') ?></h4>
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
                            $form = ActiveForm::begin(['id' => 'add-partner-payment-account-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('partner-payment-account/add'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class=form-group>
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">Merchant <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']); ?>
                                    </div>
                                </div>
                                <div class=form-group >
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">Kênh thanh toán <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'partner_payment_id')->label(false)->dropDownList($partner_payment_arr, [
                                            'id' => 'partner_payment_id',
                                            'class' => 'form-control',
                                            'data-url' => Yii::$app->urlManager->createUrl(['partner-payment-account/get-fields-by-partner']),
                                            ]); ?>
                                    </div>
                                </div>
                                <!-- End .form-group  -->

                                <div class=form-group id="token_key" style="display: none">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label" id="token_key_label"></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'token_key')->label(false)->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group id="checksum_key"  style="display: none">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label" id="checksum_key_label"></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'checksum_key')->label(false)->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class="form-group" id="partner_payment_account"  style="display: none">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label" id="partner_payment_account_label" > </label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'partner_payment_account')->label(false)->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group id="partner_merchant_id"  style="display: none">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label" id="partner_merchant_id_label"></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'partner_merchant_id')->label(false)->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group id="partner_merchant_password"  style="display: none">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label" id="partner_merchant_password_label"></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'partner_merchant_password')->label(false)->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-12 control-label">&nbsp;</label>

                                    <div class="col-lg-8 col-md-8">
                                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                        <a href="<?= Yii::$app->urlManager->createUrl('partner-payment-account/index') ?>"
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
<script>
    $('#partner_payment_id').change(function () {
        var partner_payment_id = $('#partner_payment_id').val();
        var url = $('#partner_payment_id').attr('data-url');
        $.get(url, {'id': partner_payment_id}, function (data) {
            fields = JSON.parse(data);
            $.each(fields, function (key, value) {
                if (value['display'] === true) {
                    $('#' + key).css('display','block');
                    $('#' + key + '_label').html(value['label']);
                    $('#partnerpaymentaccountform-' + key).val(value['value']);
                } else {
                    $('#' + key).css('display','none');
                    $('#partnerpaymentaccountform-' + key).val('');
                }
            });
        });
    })
</script>
