<?php
use common\components\utils\Translate;
?>
<div class="col-span-8 mfleft brdRight success">
    <div class="col-xs-1 col-sm-1 col-md-2"></div>
    <div class="col-xs-10 col-sm-10 col-md-8 brdRightIner">
        <div class="row clearfix" id="info-success">
            <!--<img src="<?= ROOT_URL ?>/checkout/web/images/circle_close_delete-128.png">-->
            <div class="col-xs-12 col-sm-12">
                <h4 class="payopt-title"><span class="greenFont"> <?= Translate::get('Liên kết thẻ chờ xác thực') ?></span></h4>
                <p><?= Translate::get('Thẻ của bạn đã bị trừ tiền, tuy nhiên do một số cảnh báo từ tổ chức thẻ quốc tế nên '
                        . 'chúng tôi sẽ kiểm tra lại thông tin giao dịch trước khi xử lý giao dịch của bạn.') ?>
                </p>
            </div>
        </div>
        <div class="row boxreport clearfix">
            <div class="col-xs-12 col-sm-2 col-md-1 no-padding" id="icon-loading">
            </div>
            <div class="col-xs-12 col-sm-10 col-md-11 nlOrder-warning">
            </div>
        </div>
    </div>
    <div class="col-xs-1 col-sm-1 col-md-2"></div>
</div>