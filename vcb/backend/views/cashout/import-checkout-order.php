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

$this->title = Translate::get('Import yêu cầu rút tiền cho đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header><?= Translate::get('Import yêu cầu rút tiền cho đơn hàng') ?></h1>
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><i
                                class="en-back"></i> <?= Translate::get('Quay lại') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <div class=row>
            <div class=col-lg-2>
            </div>
            <div class=col-lg-6>
                <div class="form-horizontal" role=form>
                    <?php
                    $form = ActiveForm::begin(['id' => 'import-checkout-order-form',                       
                        'action' => Yii::$app->urlManager->createUrl('cashout/import-checkout-order'),
                        'options' => ['enctype' => 'multipart/form-data']])
                    ?>
                    <div class="row">
                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">Merchant<span class="text-danger">*</span></label>
                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'merchant_id')->label(false)->dropDownList($model->getMerchants(), ['id' => 'merchant_id']) ?>
                            </div>
                        </div>
                        <div class=form-group>
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">Hình thức rút<span class="text-danger">*</span></label>
                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'method_id')->label(false)->dropDownList($model->getWithdrawMethods(), ['id' => 'method_id']) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-4 col-md-4 col-sm-12 control-label">File<span class="text-danger">*</span></label>
                            <div class="col-lg-8 col-md-8">
                                <?= $form->field($model, 'file_import')->label(false)->fileInput() ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Tiếp tục') ?></button>
                                <a class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><?= Translate::get('Bỏ qua') ?></a>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

