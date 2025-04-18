<?php

use common\components\utils\Translate;

?>
<div class="header-logo custom-19">
    <div class="row">
        <div class="col-sm-12 background-white">
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <div class="col-xs-12 col-sm-10 col-md-8">
                <div class="col-12 row" style="margin-bottom: 50px">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo/logo_vcb.png" name="logo-vcb">
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <img src="<?= ROOT_URL ?>/checkout/web/images/merchant_logo/fwd.png" name="logo-fwd">
                    </div>
                </div>
                <div class="clearfix">
                </div>
                <div class="col-12 row">
                    <div class="col-xs-12 col-sm-12 col-md-4"></div>
                    <div class="col-xs-12 col-sm-12 col-md-4"></div>
                    <div class="col-xs-12 col-sm-12 col-md-4 buy-info">
                        <h4 class="title1_1 show-mobile row">
                            <div class="btn-view-order-info text-right">
                                <p><?= Translate::get('Tên khách hàng') ?></p>
                                <strong class="color-vcb"><?= $link_card['card_holder'] ?></strong>
                                <i class="fa fa-caret-down"></i>
                            </div>
                        </h4>
                        <div id="order-info" class="row mform invoiceBg hidden-mobile" role="form">
                            <div class="form-group">
                                <span for=""
                                      class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Email') ?>:</span>
                                <div class="col-sm-8">
                                    <p class="form-control-static"> <?= $link_card['customer_email'] ?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <span for=""
                                      class="col-sm-4 control-label pdr5 text-right form-control-static"><?= Translate::get('Số điện thoại') ?>:</span>
                                <div class="col-sm-8">
                                    <p class="form-control-static"> <?= $link_card['customer_mobile'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
        </div>
    </div>
</div>

<style>
    .header-logo img {
        width: 180px;
        height: 80px;
        margin-top: 50px;
    }

    .header-logo img[name=logo-vcb] {
        float: left;
        margin-left: 20px;
    }

    .header-logo img[name=logo-fwd] {
        float: right;
        margin-right: 20px;
    }

    @media only screen and (max-width: 576px) {
        .header-logo img {
            width: 100px;
            height: 50px;
            margin: 30px 0 0 0 !important;
        }

        div.btn-view-order-info.text-right p {
            font-size: 13px !important;
        }

        strong.color-vcb.mrgr5 {
            font-size: 15px !important;
        }


        .header-logo + div.col-sm-12 > div.col-xs-12.col-sm-10.col-md-8 {
            padding: 0px !important;
        }

        .header-logo div.col-xs-12.col-sm-10.col-md-8, h4.title1_1.show-mobile.row {
            margin-bottom: 20px !important;
        }
    }

</style>