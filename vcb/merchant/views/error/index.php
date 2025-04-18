<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = 'Thông báo lỗi';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container"> 
    <div id="header">
        <div class="clearfix">
            <div class="clearfix">
                <h1 class="logo"><a href=""><img src="<?= ROOT_URL .'logo.png'?>"></h1>
            </div>
        </div>
    </div>
    <div id="wrapbody">
        <div class="panel panel-danger" style="margin:50px 0px;">
            <div class="panel-body">
                <div class="media bigIconError">
                    <span class="pull-left">
                        <i class="fa fa-times-circle" style="color: #c9302c;font-size: 47px;margin-right: 10px;"></i>
                    </span>
                    <div class="media-body">
                        <h4 class="media-heading text-danger" style="font-size: 18px;"><?=Translate::get("Thông báo")?>:</h4>
                        <p><?= Translate::get($error_message) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>