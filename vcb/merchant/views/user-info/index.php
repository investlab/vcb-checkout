<?php

use common\components\utils\Converts;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Thông tin tài khoản');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont no-padding-right">
    <h1 class="titlePage"><?=Translate::get('Thông tin tài khoản')?></h1>
    <div class="graybg pdtop2 clearfix" style="border-bottom:1px solid #dcdcdc">
        <div class="col-md-6">
            <div style="position:relative; overflow: hidden; float: left;">
                <?php if (UserLogin::get('merchant_logo') != ''):?>
                <img class="thumbnail" src="<?=UserLogin::get('merchant_logo')?>" style="max-width: 200px; z-index: 1;"/>
                <?php else:?>
                <img class="thumbnail" src="images/merchant_logo_default.png" style="max-width: 200px; z-index: 1;"/>
                <?php endif;?>
                <div style="position:absolute; z-index: 2; right: 1px; top:1px; background: #DDD; padding: 2px 5px 2px 5px;"><a class="linktxt font11" href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/update'])?>"><i class="fa fa-edit"></i></a></div>
            </div>
        </div>
        <div class="col-md-6">
            <table class="table-condensed">
                <tbody>
<!--                    <tr>-->
<!--                        <td scope="row">--><?//=Translate::get('Số dư khả dụng')?><!--:</td>-->
<!--                        <td><h3><span class="label label-success">--><?//=ObjInput::makeCurrency(common\models\db\Account::getBalance(UserLogin::get('merchant_id'), 'VND', $account_info))?><!-- VND</span></h3></td>-->
<!--                    </tr>-->
<!--                    <tr>-->
<!--                        <td scope="row">--><?//=Translate::get('Số dư chờ chuyển')?><!--:</td>-->
<!--                        <td><h4><span class="label label-warning">--><?//=ObjInput::makeCurrency(@$account_info['balance_pending'])?><!-- VND</span></h4></td>-->
<!--                    </tr>-->
                </tbody>
            </table>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6">
            <div class="scrollx-auto">
                <table class="table table-responsive no-margin ">
                    <tbody>
                    <tr>
                        <td scope="row"><?=Translate::get('Tên merchant')?>:</td>
                        <td><?=UserLogin::get('merchant_name')?></td>
                    </tr>
                    <tr>
                        <td scope="row">Website:</td>
                        <td>
                            <a href="<?=UserLogin::get('merchant_website')?>" target="_blank">
                                <?= Converts::convertString(UserLogin::get('merchant_website')) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row"><?=Translate::get('Email nhận thông báo')?>:</td>
                        <td><?=UserLogin::get('merchant_email_notification')?> [ <a class="linktxt font11" href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/update'])?>"><i class="fa fa-edit"></i></a> ]</td>
                    </tr>
                    <tr>
                        <td scope="row"><?=Translate::get('URL nhận thông báo')?>:</td>
                        <td><?= Converts::convertString(UserLogin::get('merchant_url_notification'))?> [ <a class="linktxt font11" href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/update'])?>"><i class="fa fa-edit"></i></a> ]</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <hr><br/>
            <div style="margin-bottom:15px" align="center">
                <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/change-password'])?>" class="btn btn-primary"><?=Translate::get('Đổi mật khẩu kết nối')?></a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="UserBox">
                <table class="table table-responsive no-margin ">
                    <tbody>
                        <tr>
                            <td scope="row"><?=Translate::get('Tên tài khoản')?>:</td>
                            <td><?=UserLogin::get('fullname')?></td>
                        </tr>
                        <tr>
                            <td scope="row"><?=Translate::get('Địa chỉ Email')?>:</td>
                            <td><?=UserLogin::get('email')?></td>
                        </tr>
                        <tr>
                            <td scope="row"><?=Translate::get('Điện thoại di động')?>:</td>
                            <td><?=UserLogin::get('mobile')?></td>
                        </tr>
                    </tbody>
                </table>
                <hr><br/>
                <div style="margin-bottom:15px" align="center">
                    <a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['user-info/change-password'])?>" class="btn btn-primary"><?=Translate::get('Đổi mật khẩu đăng nhập')?></a>
                </div>
            </div>
        </div>
    </div>
</div>
