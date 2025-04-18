<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;

use common\components\utils\Translate;
$this->title = "Đổi mật khẩu tài khoản";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?= Translate::get('Khai báo mật khẩu đăng nhập mới') ?></h1>
    <div class="RegisterBox">
        <div class="help-block"><?= Translate::get('Bạn vui lòng nhập mật khẩu đăng nhập mới và xác nhận bằng mật khẩu giao dịch để hoàn tất.') ?></div>
        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
        <div class="whitebox">
            <div class="panel-body">
                <?php if ($error != ''):?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif;?>
                <?php $form = ActiveForm::begin(['id' => 'change-forget-password', 'options' => ['class' => ''],'action' => '',]); ?>
<!--                <form action="" method="post" enctype="multipart/form-data" name="" id="" onsubmit="" class="form-horizontal" role="form"><input type="hidden" name="form_id">-->
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Email tài khoản') ?>: </label>
                        <div class="col-sm-8">
                            <p class="form-control-static bold"><?= $data['email']?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Mật khẩu đăng nhập mới') ?>:</label>
                        <div class="col-sm-8">
                            <?= $form->field($model, 'new_password')->label(false)->passwordInput(array('placeholder' => Translate::get("Mật khẩu mới")))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label"><?= Translate::get('Nhập lại mật khẩu') ?>:</label>
                        <div class="col-sm-8">
                            <?= $form->field($model, 'confirm_password')->label(false)->passwordInput(array('placeholder' => Translate::get('Nhập lại mật khẩu mới')))?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-4 pdr5">
                            <button type="submit" class="btn btn-block btn-success "><?= Translate::get('Hoàn tất') ?></button>
                        </div>
                    </div>
<!--                </form>-->
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>