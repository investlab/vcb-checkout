<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice"> 
        <!--begin hoa don-->
        <!--header-->
        <?php require_once('includes/header.php') ?>
        <!--main-->
        <!--begin left Colm-->
        <div class="col-span-8 mfleft brdRight">
            <div class="col-sm-2"></div>
            <div class="col-sm-8 brdRightIner">
                <div class="row">
                    <div class="form-horizontal mform2 pdtop">
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <div class="alert alert-danger"><?=$model->error_message?></div>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
            <div class="col-sm-2"></div>
        </div>
        <!--footer-->

    </div>
</div>