<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

$this->title = Translate::get("Quên mật khẩu tài khoản");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main clearfix">
    <h1 class="loginTitle" align="center"><?=Translate::get('Quên mật khẩu đăng nhập')?></h1>
    <div class="LoginBox">
     
        <div class="whitebox">
            <div class="panel-body">
                <div class="alert alert-info">
                    <?=Translate::get('Vui lòng liên hệ bộ phận hỗ trợ để lấy lại mật khẩu đăng nhập của bạn!')?>
                </div>
               
            </div>
        </div>
    </div>
</div>
    