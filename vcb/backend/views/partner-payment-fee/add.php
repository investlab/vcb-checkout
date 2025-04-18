<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\PartnerPaymentFee;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Thêm phí kênh thanh toán');
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
                           href="<?= Yii::$app->urlManager->createUrl('partner-payment-fee/index') ?>"><i
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
                        <h4><?= Translate::get('Thêm phí kênh thanh toán') ?></h4>
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
                            $form = ActiveForm::begin(['id' => 'add-partner-payment-fee-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('partner-payment-fee/add'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="row">
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kênh thanh toán') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'partner_payment_id')->label(false)->dropDownList($partner_payment_arr, ['id' => 'partner_payment_id']); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Nhóm phương thức thanh toán') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'method_id')->label(false)->dropDownList($method_arr,
                                            [
                                                'id' => 'method_id',
                                                'class' => 'form-control',
                                                'onchange' => 'merchant_fee.changeMethod();',
                                                'data-url' => Yii::$app->urlManager->createUrl(['payment-method/get-payment-method-by-method-id']),
                                            ]); ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phương thức thanh
                                        toán') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?= $form->field($model, 'payment_method_id')->label(false)
                                            ->dropDownList($payment_method_arr, [
                                                'id' => 'payment_method_id',
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
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Số tiền tối thiểu áp
                                        dụng') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'min_amount',
                                            ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                            ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định người
                                        chuyển') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'sender_flat_fee',
                                            ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                            ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm người
                                        chuyển') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'sender_percent_fee',
                                            ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">%</span>
                                </div>{error}{hint}'])
                                            ->textInput(array('class' => 'form-control', 'value' => 0))->label(false); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định người
                                        nhận') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'receiver_flat_fee',
                                            ['template' => '<div class="input-group">{input}
                                <span class="input-group-addon" id="basic-addon2">VND</span>
                                </div>{error}{hint}'])
                                            ->textInput(array('class' => 'form-control input_number', 'value' => 0))->label(false); ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm người
                                        nhận') ?></label>

                                    <div class="col-lg-7 col-md-7">
                                        <?php echo $form->field($model, 'receiver_percent_fee',
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
                                           href="<?= Yii::$app->urlManager->createUrl('partner-payment-fee/index') ?>"><?= Translate::get('Bỏ
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

<script language="javascript" type="text/javascript">
    merchant_fee.changeMethod();
</script>
