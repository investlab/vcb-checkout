<?php
use common\components\utils\Translate;
?>
<div class="col-span-8 mfleft brdRight success">
    <div class="col-xs-1 col-sm-1 col-md-2"></div>
    <div class="col-xs-10 col-sm-10 col-md-8 brdRightIner">
        <div class="row clearfix" id="info-success">
            <div class="col-xs-12 col-sm-12">
                <h4 class="payopt-title"><span class="color-orange"> <?= Translate::get('Liên kết thất bại') ?></span></h4>
                <p><?= Translate::get('Bạn vui lòng kiểm tra lại thông tin thẻ') ?>
                </p>
            </div>
        </div>
        <div class="row boxreport clearfix cancel">
            <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">
            </div>
            <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">
            </div>
        </div>
    </div>
    <div class="col-xs-1 col-sm-1 col-md-2"></div>
</div>