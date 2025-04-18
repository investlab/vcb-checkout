<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Đổi mật khẩu đăng nhập');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?=Translate::get('Đổi mật khẩu đăng nhập')?></h1>
    <div class="row">
        <div class="col-md-8 pdtop2">
            <div class="media">
                <span class="pull-left">
                    <i class="icon-password"></i>
                </span>
                <div class="media-body">
                    <h4 class="media-heading greenFont"><?=Translate::get('Đổi mật khẩu đăng nhập thành công')?></h4>
                    <p> <?=Translate::get('Để tài khoản của bạn được an toàn hơn, bạn không nên cung cấp mật khẩu cho bất kỳ ai.')?> <?=DOMAIN?> <?=Translate::get('không yêu cầu bạn cung cấp mật khẩu bằng bất kỳ lý do nào.')?> </p>
                    <p class="pdtop2"><a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['user-info/index'])?>" class="btn btn-primary btn-sm"><?=Translate::get('Xem thông tin tài khoản')?></a> <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['user-logout/index'])?>" class="btn btn-default btn-sm"><?=Translate::get('Đăng xuất')?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>