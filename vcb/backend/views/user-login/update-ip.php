<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Merchant;
use common\components\utils\Utilities;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cập nhật dải IP');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id="ajax-result" class="content-wrapper ajax-target">
    <div class=row>
        <div class="col-lg-6 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class="page-header ajax-title"><?= Translate::get('Cập nhật dải IP') ?></h1>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class=row>
            <div class=col-lg-6>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Cập nhật dải IP') ?></h3>
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
                            <?php if ($error_message != ""): ?>
                                <div class="alert alert-warning fade in">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <strong>Thông báo: </strong> <?= $error_message ?>.
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Họ tên') ?> </label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control" value="<?= @$user_login['fullname'] ?>">
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Merchant </label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control" value="<?= @$user_login['merchant_info']['name'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label">Email </label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control" value="<?= @$user_login['email'] ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Số điện thoại') ?></label>

                                <div class="col-lg-8 col-md-8">
                                    <input type="text" disabled class="form-control" value="<?= @$user_login['mobile'] ?>">
                                </div>
                            </div>
                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Dải IP') ?></label>

                                <div class="col-lg-8 col-md-8">
                                    <?= $form->field($model, 'ips')->label(false)
                                        ->textInput(array('class' => 'form-control','value' => @$user_login['ips'])) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-lg-8 col-md-8">
                                    <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
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