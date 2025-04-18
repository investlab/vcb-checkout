<?php

use yii\helpers\Html;
use common\components\utils\Translate;

$this->title = Translate::get('Thông báo lỗi');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid"> 
<!--    <div id="header">
        <div class="clearfix">
            <div class="clearfix">
               <h1 class="logo"><a href=""><img src="/logo.png"></a></h1>
            </div>
        </div>
    </div>-->
<div class="col-sm-2"></div>
<div  class="col-sm-8" style="padding: 150px 0; ">
        <div class="panel panel-danger">
            <div class="panel-body" style="padding: 20px">
                <div class="media bigIconError">
                    <span class="pull-left">
                        <i class="fa fa-times-circle" style="color: #c9302c;font-size: 47px;margin-right: 10px;"></i>
                    </span>
                    <div class="media-body">
                        <h4 class="media-heading text-danger" style="font-size: 18px;"><?= Translate::get('Thông báo') ?>:</h4>
                        <p><?= Html::encode(Translate::get($error_message)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>