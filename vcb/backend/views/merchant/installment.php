<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Merchant;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cấu hình trả góp');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile(ROOT_URL . '/backend/web/css/installment.css');
$this->registerJsFile(ROOT_URL . '/backend/web/js/installment.js');
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>&nbsp;</h1>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class=row>
            <div class="panel panel-primary">
                <!-- Start .panel -->
                <div class=panel-heading>
                    <h4><?= Translate::get('Cấu hình trả góp merchant ' . $merchant['name']) ?></h4>
                </div>
                <div class="panel-body" id="body-form-installment">
                    <div class="form-horizontal padding-15px-ipad" role=form>
                        <?php
                        $form = ActiveForm::begin(['id' => 'installment-config-merchant-form',
                            'enableAjaxValidation' => false,
                            'action' => Yii::$app->urlManager->createUrl('merchant/installment'),
                            'options' => ['enctype' => 'multipart/form-data']])
                        ?>
                        <div class="row">
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Chọn ngân hàng') ?></label>

                                <div class="col-lg-7 col-md-7">
                                    <select class="form-control" name="bank_code" id="bank-code">
                                        <?php foreach ($banks as $key => $bank) : ?>
                                            <option value="<?= $bank['code'] ?>"> <?= Translate::get($bank['name']) ?> </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kỳ hạn') ?></label>
                                <div class="col-lg-7 col-md-7">
                                    <?php if (!empty($installment_cycle)) :?>
                                    <?php foreach ($installment_cycle as $key => $cycle) : ?>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="noStyle cycle-bank" id="month<?= $cycle['month'] ?>" name="installment_cycle[]" value="<?= $cycle['month'] ?>">
                                            <?= Translate::get($cycle['name']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại thẻ') ?></label>
                                <div class="col-lg-7 col-md-7">
                                    <?php if (!empty($installment_card)) :?>
                                        <?php foreach ($installment_card as $key => $card) : ?>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="noStyle card-bank" id="card<?= $key ?>" name="installment_card[]" value="<?= $key ?>">
                                                <?= Translate::get($card) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group button-form">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"></label>
                                <div class="col-lg-7 col-md-7">
                                    <a href="<?= Yii::$app->urlManager->createUrl('installment-config/index'); ?>"><button type="button" class="btn btn-default">Bỏ qua</button></a>
                                    <button type="submit" class="btn btn-primary">Cấu hình</button>
                                </div>
                            </div>
                        </div>
                        <?= $form->field($model, 'id')->label(false)
                            ->hiddenInput() ?>
                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var get_info_config_url = '<?= Yii::$app->urlManager->createUrl('installment-config/get-info-config'); ?>';
    $('#bank-code').select2();
</script>
