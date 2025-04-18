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

$this->title = Translate::get('Xác thực yêu cầu rút tiền cho đơn hàng về tài khoản ngân hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header><?= Translate::get('Xác thực yêu cầu rút tiền cho đơn hàng') ?></h1>
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <div class=row>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                            <?php foreach ($model->getColumns() as $col) :?>
                                <th><?= Translate::get($col['title']) ?></th>
                            <?php endforeach;?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $index => $row) :?>
                            <tr>
                                <?php foreach ($model->getColumns() as $key => $col) :?>
                                <?php if (isset($validate_rows[$index][$key]) && !empty($validate_rows[$index][$key])) :?>
                                <td><span class="text-danger" title="<?=$validate_rows[$index][$key]?>"><?= $row[$key] ?></span></td>
                                <?php else: ?>
                                <td><span class="text-success"><?= $row[$key] ?></span></td>
                                <?php endif; ?>
                                <?php endforeach;?>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-horizontal" role=form>
                    <?php
                    $form = ActiveForm::begin(['id' => 'import-checkout-order-form',
                        'enableAjaxValidation' => false,
                        'action' => Yii::$app->urlManager->createUrl(['cashout/verify-import-checkout-order', 
                            'import_id' => $model->import_id, 
                            'merchant_id' => $model->merchant_id, 
                            'method_id' => $model->method_id, 
                        ]),
                        'options' => ['enctype' => 'multipart/form-data']]);
                    ?>
                    <div class="hidden">
                    <?= $form->field($model, 'import_id')->hiddenInput()->label(false);?>
                    <?= $form->field($model, 'merchant_id')->hiddenInput()->label(false);?>
                    <?= $form->field($model, 'method_id')->hiddenInput()->label(false);?>
                    </div>
                    <?php if ($error_message != ''): ?>
                    <div class="alert alert-danger">
                        <?=$error_message?>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                <a class="btn btn-danger" href="<?= Yii::$app->urlManager->createUrl('cashout/import-checkout-order') ?>"><?= Translate::get('Import lại') ?></a>
                            </div>
                        </div>
                    </div>
                    <?php else:?>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary"><?= Translate::get('Xác thực') ?></button>
                                <a class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"><?= Translate::get('Bỏ qua') ?></a>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php ActiveForm::end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

