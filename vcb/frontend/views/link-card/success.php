<?php

use common\components\utils\Translate;

?>
<div class="col-span-8 mfleft brdRight success">
    <div class="col-xs-1 col-sm-1 col-md-2"></div>
    <div class="col-xs-10 col-sm-10 col-md-8 brdRightIner">
        <div class="row clearfix" id="info-success">
            <img src="<?= ROOT_URL ?>/checkout/web/images/check.png">
            <div class="col-xs-12 col-sm-12">
                <h4 class="payopt-title"><span
                            class="greenFont"> <?= Translate::get('Liên kết thành công') ?></span></h4>
                <p><?= Translate::get('Quý khách vừa liên kết thành công số thẻ') ?>
                    <strong class="greenFont"><?= $card_token['card_number_mask'] ?></strong>
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
<?php
$info = json_decode($card_token['info'], true);
if (isset($info['return_url']) && $info['return_url'] != "") {

    $params = [
        'order_code' => $info['customer_id'],
        'status' => $card_token['status'],
    ]

    ?>
    <script type="text/javascript">
        setTimeout('returnUrl();', 5000);
        function returnUrl() {
            document.location.href = "<?= $info['return_url'] . '?' . http_build_query($params)  ?>";
        }
    </script>
    <?php
}
?>
