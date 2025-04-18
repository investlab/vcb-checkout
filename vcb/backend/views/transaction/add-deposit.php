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

$this->title = Translate::get('Thêm mới giao dịch nạp');
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
                <h1 class=page-header><?= Translate::get('Thêm mới giao dịch nạp') ?></h1>
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
                        <strong><?= Translate::get('Thông báo') ?></strong> <?= $errors ?>.
                    </div>
                <?php } ?>
                <div class="form-horizontal" role="form">
                    <?php
                    $form = ActiveForm::begin(['id' => 'add-deposit-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('transaction/add-deposit'),
                                'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">

                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Phương thức nạp') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?=
                                $form->field($model, 'payment_method_id')->label(false)->dropDownList($payment_method_arr, [
                                    'id' => 'payment_method_id',
                                    'class' => 'form-control',
                                    'onchange' => 'cashout.changeMethod(this);',
                                    'data-url' => Yii::$app->urlManager->createUrl(['transaction/add-deposit'])
                                ]);
                                ?>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Kênh nạp') ?><span
                                    class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'partner_payment_id')->label(false)
                                            ->dropDownList($partner_payment_arr, ['id' => 'partner_payment_id', 'class' => 'form-control']);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant<span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
<?= $form->field($model, 'merchant_id')->label(false)->dropDownList($merchant_arr, ['id' => 'merchant_id']) ?>
                            </div>
                        </div>
                        <div class="form-group" id="classAmount">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Số tiền nạp') ?><span
                                    class="text-danger">*</span></label>

                            <div class="col-lg-8 col-md-8">
                                <?php
                                echo $form->field($model, 'amount', ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                        ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false);
                                ?>
                            </div>
                        </div>

                        <div class="form-group" id="classBankAccountCode">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã tham chiếu') ?>
                                <span class="text-danger ">*</span></label>

                            <div class="col-lg-8 col-md-8">
<?= $form->field($model, 'bank_refer_code')->label(false)->textInput(array('class' => 'form-control')) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                <a class="btn btn-default"
                                   href="<?= Yii::$app->urlManager->createUrl('transaction/index') ?>"><?= Translate::get('Bỏ
                                        qua') ?></a>
                            </div>
                        </div>

                    </div>
<?php ActiveForm::end() ?>
                </div>
            </div>

        </div>
    </div>
</div>

