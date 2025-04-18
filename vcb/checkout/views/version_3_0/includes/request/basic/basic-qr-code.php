<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;
$device = CheckMobile::isMobile();
?>

<div class="alert alert-danger">
    <?= \common\components\utils\Translate::get('Lỗi kết nối tới hệ thống thanh toán') ?>
</div>