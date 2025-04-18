<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Translate;
?>
<div class="navigator">
    <div class="col-xs-12 col-sm-1"></div>
    <div class="col-xs-12 col-sm-10 main clearfix">
       
        <div class="text-right flag hidden-xs">
<!--            <span class="flag-icon flag-icon-id disabled" title="Indonesia"></span>-->
<!--            <span class="flag-icon flag-icon-my disabled" title="Malaysia"></span>-->
<!--            <span class="flag-icon flag-icon-ph disabled" title="Philippines"></span>-->
<!--            <span class="flag-icon flag-icon-th disabled" title="Thailand"></span>-->
            <span class="flag-icon flag-icon-vn" title="Vietnam"></span>
        </div>
    </div>
    <div class="col-xs-12 col-sm-1"></div>
</div>

<div id="header">
    <div class="col-xs-12 col-sm-1"></div>
    <div class="col-xs-12 col-sm-10 main clearfix">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="<?= ROOT_URL ?>" class="navbar-brand" style="width: 120px;"><img src="<?= ROOT_URL ?>logo.png"></a>
                <div class="accSetup " style="display:">
                    <ul class="clearfix">
                        <li class="mAcc"><a href="#menu" onclick="return showMMobile();"><b><i class="fa fa-user"></i>&nbsp; <i class="fa fa-ellipsis-v"></i></b></a></li>
                        <li>
                            <a><b class="accName"><?= UserLogin::get('fullname') ?></b></a>
                            <ul class="accDrop" style="display:">
                                <li><a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['user-info/index']) ?>"><i class="fa fa-user"></i> <?= Translate::get('Thông tin tài khoản') ?></a></li>
                                <li><a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['user-info/change-password']) ?>"><i class="fa fa-user"></i> <?= Translate::get('Đổi mật khẩu đăng nhập') ?></a></li>
                                <li><a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['user-logout/index']) ?>"><i class="fa fa-sign-out"></i> <?= Translate::get('Thoát') ?></a></li>
                            </ul>
                        </li>
<!--                        <li><strong class="accName text-success" title="--><?//= Translate::get('Số dư khả dụng') ?><!--"><i class="icon-Money"></i> --><?//= ObjInput::makeCurrency(common\models\db\Account::getBalance(UserLogin::get('merchant_id'), 'VND')) ?><!-- VND</strong></li>-->
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-1"></div>
</div>