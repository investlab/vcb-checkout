<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Product;

use common\components\utils\Translate;
$this->title = "Yêu cầu lấy mật khẩu đăng nhập";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?= Translate::get('Quên mật khẩu/xác thực tài khoản') ?></h1>
    <div class="RegisterBox">
        <div class="help-block" style="text-align: center"><?= Translate::get('Để lấy lại mật khẩu đăng nhập/link kích hoạt tài khoản Vietcombank, bạn nhập chính xác địa chỉ email đã đăng ký tài khoản Vietcombank vào form dưới đây. Hệ thống sẽ gửi một đường link kích hoạt tới email của bạn để xác thực yêu cầu.') ?></div>
        <div id="RgWarrning" class="alert alert-warning" role="alert" style="display: none;"></div>
        <div class="whitebox">
            <div class="panel-body">
                <div class="media"> <span class="pull-left"> <i class="icon-mail"></i> </span>
                    <div class="media-body">
                        <p>Vietcombank đã gửi đường link kích hoạt yêu cầu lấy lại mật khẩu đăng nhập tài khoản tới địa chỉ email <strong><?= $email ?></strong>. Bạn vui lòng truy cập hộp thư và bấm vào link kích hoạt để tiếp tục.</p>
                        <p>- Vui lòng click <a href="<?= $index ?>" class="linktxt">vào đây</a> để đăng nhập.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>