<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;
$device = CheckMobile::isMobile();
?>

<div class="row">
    <div class="form-horizontal">
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-1">
                <?php if ($model->error_message != '') :?>
                    <div class="alert alert-danger"><?=Translate::get($model->error_message)?></div>
                <?php endif;?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-3 control-label"></label>
            <div class="col-sm-7">
                <div class="bankwrap clearfix">
                    <?php if($device=='mobile'):?>
                        <img src="<?=\yii\helpers\Url::base()?>/bank/<?=$model->config['class']?>.png" style="width: 40%">
                    <?php else:?>
                        <img src="<?=\yii\helpers\Url::base()?>/bank/<?=$model->config['class']?>.png" style="width: 20%">
                    <?php endif;?>
                    <p><?=Translate::get($model->info['name'])?></p>
                </div>
            </div>
        </div>
    </div>
</div>