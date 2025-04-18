<?php

use common\components\utils\Translate;
?>
<div class="col-sm-12 background-white">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8 rlv clearfix">
        <div class="col-xs-12 col-sm-12 col-md-8 media">
            <div class="Merchantlogo"  title="Vietcombank" data-placement="bottom">
                <img class="col-sm-3" src="<?= ROOT_URL ?>/frontend/web/images/vcb_logo.png">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4 row">
            <h4 class="title1_1 show-mobile row">
                <div class="btn-view-order-info text-right">
                    <p><?= Translate::get('Tên khách hàng') ?></p>
                    <strong class="color-vcb"><?= $link_card['card_holder'] ?></strong>
                    <i class="fa fa-caret-down"></i>
                </div>
            </h4>
            <div id="order-info" class="row mform invoiceBg hidden-mobile" role="form">
                <div class="form-group">
                    <span for="" class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Email') ?>:</span>
                    <div class="col-sm-8">
                        <p class="form-control-static"> <?= $link_card['customer_email'] ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <span for="" class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Số điện thoại') ?>:</span>
                    <div class="col-sm-8">
                        <p class="form-control-static"> <?= $link_card['customer_mobile'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="iGqbIb"></div>
    </div>
</div>