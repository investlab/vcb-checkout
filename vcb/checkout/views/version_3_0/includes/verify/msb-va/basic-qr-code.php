<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;
use common\models\db\PartnerPayment;
use common\models\db\PaymentMethod;
/** @var $model */
$provider_bank_qr = 'UNKNOWN_BANK_QR';
if(PaymentMethod::isQrPayment($model->payment_method_code)){
    $provider_bank_qr = PartnerPayment::getProviderBankQr($model->partner_payment_code);
}

$device         = \common\components\utils\CheckMobile::isMobile();
$no_change_text = 'Tại màn hình chuyển tiền, Quý khách vui lòng <strong>không thay đổi</strong> thông tin số tiền và nội dung chuyển tiền!';
//echo ($model->payment_transaction['partner_payment_info']['qr_data'] );die();
?>
<?php if ($device == 'mobile'): ?>
<div class="form-qr-code" style="margin-top: 0; padding: 0 15px 15px 15px">
    <?php else: ?>
    <div class="form-qr-code">
        <?php endif; ?>
        <?php require_once(__DIR__ . '/../../../_header-card.php') ?>

        <div class="card-body py-0 background-white row no-gutters align-items-center">
            <div class="col-sm-5 pay-qrcode pt-0">
                <p>
                    <img src="images/<?= Translate::get('tutorial-vi.png') ?>" alt="">
                </p>
                <div class="text-center mb-2 mb-md-3 mt-md-2">
                    <?php if($provider_bank_qr !== 'UNKNOWN_BANK_QR'):?>
                        <img class="img-responsive" src="dist/images/payment_method/<?= strtolower($provider_bank_qr) ?>.png" width="80" alt="">
                    <?php endif; ?>
                </div>
                <div class="pq-img m-0">
                    <?php if (isset($model->payment_transaction['partner_payment_info']['auth_site']) && $model->payment_transaction['partner_payment_info']['auth_site'] == 'QRCODE247'): ?>
                        <img style="width: 50%;border: 1px solid #dcdcdc;margin: auto;"
                             src="<?= $model->payment_transaction['partner_payment_info']['qr247_data']['qrcode_image'] ?>"
                             class="img img-responsive">
                        <div class="pq-spin text-center mt-2 mt-md-3 d-flex align-items-center justify-content-center">
                            <a style="color: #51affd;" download="qr"
                               href="<?= $model->payment_transaction['partner_payment_info']['qr247_data']['qrcode_image'] ?>">
                                <i class="fa fa-download-alt"></i>
                                Tải xuống hình ảnh</a>
                        </div>
                    <?php else: ?>
                        <img style="width: 50%;border: 1px solid #dcdcdc;margin: auto;"
                             src="<?= $model->payment_transaction['partner_payment_info']['qr_data'] ?>"
                             class="img img-responsive">
                        <div class="pq-spin text-center mt-2 mt-md-3 d-flex align-items-center justify-content-center">
                            <a style="color: #51affd;" download="qrcode_<?= time()?>"
                               href="<?= $model->payment_transaction['partner_payment_info']['qr_data'] ?>"><i class="fa fa-download"></i><?= Translate::get('Tải xuống hình ảnh') ?></a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pq-spin text-center mb-2 mb-md-3 d-flex align-items-center justify-content-center">
                    <i class="fa fa-spinner fa-spin fa-fw"></i>
                    <span class="ml-1"><?= Translate::get('Mã QR đang chờ quét') ?></span>
                </div>
            </div>
            <div class="col-sm-7 qr-right">
                <div class="qr-right-wr">
                    <div class="qr-footer d-flex align-items-center">
                        <div class="qr-text pq-text cf-left">
                            <p><?= Translate::get('Hãy dùng chức năng Quét QR trên App Mobile Banking hoặc Ví điện tử của bạn quét mã dưới đây để hoàn tất thanh toán') ?>
                                .</p>
                            <strong class="text-danger text-uppercase"><?= Translate::get('Lưu ý') ?>: </strong>
                            <p style="color: #FA7940;">1. <?= Translate::get($no_change_text) ?>.</p>
                            <p style="color: #FA7940;">
                                2. <?= Translate::get('Vui lòng không đóng trình duyệt') ?>.</p>
                            <p></p>
                            <div class="text-center">
                                <img src="images/DEFAULT.jpg" alt="">
                            </div>
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once(__DIR__ . '/../../../_footer-card.php') ?>

    </div>
</div>

<script type="text/javascript">
    setTimeout('document.location.reload();', 5000);
    $(".btn-checking").click(function () {
        $(this).prop('disabled', true);
    })
</script>