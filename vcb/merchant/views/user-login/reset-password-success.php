<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;
use common\components\utils\Translate;

$this->title = Translate::get("Đổi mật khẩu tài khoản");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?= Translate::get('Khai báo mật khẩu đăng nhập mới') ?></h1>
    <div class="RegisterBox">
        <div class="help-block"><?= Translate::get('Bạn vui lòng nhập mật khẩu đăng nhập mới và xác nhận bằng mật khẩu giao dịch để hoàn tất.') ?></div>
        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
        <div class="whitebox">
            <div class="panel-body"> 
                <div class="message message-success">
                    <i class="glyphicon glyphicon-ok"></i>
                    <?= Translate::get('Bạn đã đổi thành công mật khẩu đăng nhập cho tài khoản') ?> <a><?= htmlentities($email) ?></a>.
                    <div class="clearfix"></div><br>
                    <div class="text-center">
                        <a class="btn btn-success" href="<?= Yii::$app->urlManager->createAbsoluteUrl('user-login/index') ?>"><?= Translate::get('Đăng nhập tài khoản') ?></a>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>