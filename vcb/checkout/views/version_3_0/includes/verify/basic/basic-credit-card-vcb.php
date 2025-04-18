<?php
use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\payments\CyberSourceVcb;

?>





<div class="row" style="padding-top: 30px;">
    <div class="form-horizontal">
        <div class="form-group">
            <div class="col-xs-12 col-sm-12 col-md-offset-2 col-md-10 col-lg-offset-4 col-lg-7">
                <div class="bankwrap clearfix"><i class="<?=$model->config['class']?>"></i>
                    <div class="cardInfo">
                        <p class="hidden-xs"><?=Translate::get($model->info['name'])?></p>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12">
                <div class="panel panel-default" id="content-verify">
                    <div class="panel-body">
                        <div class="media">
                            <span class="pull-left"><img src="https://upload.nganluong.vn/public/images/3dsecu.jpg" style="width:80px" /></span>
                            <div class="media-body">
                                <h4 class=" media-heading"><?= Translate::get('Thẻ 3D Secure') ?></h4>
                                <p><?= Translate::get('Thẻ của bạn đang sử dụng mật khẩu bảo vệ khi thanh toán trực tuyến. Đây là mật khẩu được cấp bởi ngân hàng phát hành thẻ đối với chủ thẻ đăng ký sử dụng dịch vụ Verified by VISA cho thẻ Visa và dịch vụ MasterCard® SecureCodeTM cho thẻ MasterCard®.') ?></p>
                                <p><?= Translate::get('Để hoàn tất quá trình thanh toán, bạn bấm vào nút "Xác nhận thanh toán" dưới đây và nhập chính xác mật khẩu do ngân hàng đã cấp cho bạn. Trường hợp bạn chưa rõ hoặc quên mật khẩu, vui lòng liên hệ với ngân hàng phát hành thẻ để biết thêm thông tin.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group noneMrbtm" style="display: flex">
                    <form id="PAEnrollForm" name="PAEnrollForm" action="<?= @$model->partner_payment->cyber_info['response_info']['acsURL']; ?>" method="post" />
                    <input type="hidden" name="PaReq" value="<?= @$model->partner_payment->cyber_info['response_info']['paReq']; ?>"/>
                    <input type="hidden" name="TermUrl" value="<?= @$model->partner_payment->verify_url; ?>" />
                    <input type="hidden" name="MD" value="<?= @$model->partner_payment->cyber_info['response_info']['xid']; ?>" />
                    <button class="btn btn-success" type="submit"><?= Translate::get('Xác nhận thanh toán')?></button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="form-horizontal mform0">
        <?php if ($model->getPayerFee() != 0 || \common\models\db\Merchant::hasViewFeeFree($checkout_order['merchant_info'])):?>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Giá trị đơn hàng')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <strong><?= ObjInput::makeCurrency($checkout_order['amount'])?></strong> <?=$checkout_order['currency']?>
                    </p>
                </div>
            </div>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?=Translate::get('Phí thanh toán')?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <?php if ($model->getPayerFee() != 0) :?>
                            <strong><?= ObjInput::makeCurrency($model->getPayerFee())?></strong> <?=$checkout_order['currency']?>
                        <?php else:?>
                            <strong><?=Translate::get('Miễn phí')?></strong>
                        <?php endif;?>
                    </p>
                </div>
            </div>
        <?php endif;?>
        <div class="form-group mrgb0 mline hidden-mobile">
            <label for="" class="col-sm-4 col-xs-6 control-label"><?=Translate::get('Tổng tiền')?>:</label>
            <div class="col-sm-8 col-xs-6">
                <p class="form-control-static fontS14 bold text-danger"> <strong><?= ObjInput::makeCurrency($model->getPaymentAmount())?> <?=$checkout_order['currency']?></strong> </p>
            </div>
        </div>
    </div>
</div>