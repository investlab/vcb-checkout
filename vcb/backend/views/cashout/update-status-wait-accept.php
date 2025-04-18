<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Cashout;
use common\components\utils\Utilities;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Gửi duyệt yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id="ajax-result" class="content-wrapper ajax-target">
    <div class=row>
        <div class="col-lg-6 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class="page-header ajax-title"><?= Translate::get('Gửi duyệt yêu cầu rút tiền') ?></h1>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class=row>
            <div class=col-lg-6>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Gửi duyệt yêu cầu rút tiền') ?></h3>
                    </div>
                    <div class="panel-body ajax-body">
                        <?php
                        $form = ActiveForm::begin(['id' => 'ajax-form',
                            'method' => 'post',
                            'options' => [
                                'enctype' => 'multipart/form-data'
                            ]]);
                        ?>
                        <div class="form-horizontal" role="form">
                            <?php if ($errors != ""): ?>
                                <div class="alert alert-warning fade in">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×
                                    </button>
                                    <strong>Thông báo: </strong> <?= $errors ?>.
                                </div>
                            <?php endif; ?>

                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Merchant </label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control"
                                           value="<?= @$cashout['merchant_info']['name'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Số tiền rút') ?> </label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control"
                                           value="<?= ObjInput::makeCurrency(@$cashout['amount']) ?>">
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kênh rút tiền') ?><span
                                        class="text-danger">*</span></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'partner_payment_id')->label(false)->dropDownList($partner_payment_arr, ['id' => 'partner_payment_id']); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-5 col-lg-7 col-md-7">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
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
</div>