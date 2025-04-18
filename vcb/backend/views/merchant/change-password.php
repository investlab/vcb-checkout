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

$this->title = Translate::get('Thay đổi mật khẩu');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id="ajax-result" class="content-wrapper ajax-target">
    <div class=row>
        <div class="col-lg-6 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class="page-header ajax-title"><?= Translate::get('Thay đổi mật khẩu') ?></h1>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class=row>
            <div class=col-lg-6>
                <div class="panel panel-primary">
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Thay đổi mật khẩu') ?></h3>
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
                                    <strong><?= Translate::get('Thông báo') ?>: </strong> <?= Translate::get($error_message) ?>.
                                </div>
                            <?php endif; ?>

                            <div class=form-group>
                                <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mật khẩu mới') ?> <span class="text-danger">*</span></label>

                                <div class="col-lg-9 col-md-9">
                                    <?= $form->field($model, 'new_password')->label(false)
                                        ->textInput(array('class' => 'form-control')) ?>
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