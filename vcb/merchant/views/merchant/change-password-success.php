<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\components\utils\Strings;
use common\components\utils\Translate;

$this->title = Translate::get('Đổi mật khẩu kết nối');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?=Translate::get('Đổi mật khẩu kết nối')?></h1>
    <div class="row">
        <div class="col-md-8 pdtop2">
            <div class="media">
                <span class="pull-left">
                    <i class="icon-password"></i>
                </span>
                <div class="media-body">
                    <h4 class="media-heading greenFont"><?=Translate::get('Đổi mật khẩu kết nối thành công')?></h4>
                    <p> <?=Translate::get('Hệ thống đã đổi mật khẩu kết nối theo yêu cầu của bạn')?> !</p>
                    <p class="pdtop2"><a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['merchant/index'])?>" class="btn btn-primary btn-sm"><?=Translate::get('Xem thông tin Merchant')?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>