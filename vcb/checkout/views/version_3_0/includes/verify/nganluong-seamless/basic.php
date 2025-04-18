<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$merchant = \common\models\db\Merchant::findOne($checkout_order['merchant_id']);
?>
    <!--<h4 class="payopt-title btmLine hidden-xs"> --><? //=Translate::get('Online bằng thẻ ATM ngân hàng nội địa')?><!--</h4>-->
<?php
$form = ActiveForm::begin(['id' => 'form-checkout', 'options' => ['class' => 'active']]);
//print_r($checkout_order); exit();

$class_name = (new \ReflectionClass($model))->getShortName();
?>

<?php require_once(__DIR__ . '/../../../_header-card.php') ?>
    <div class="card-body">
        <div class="text-center">
            <!--    FORM OTP TẠI ĐÂY-->
            <img src="/vi/checkout/dist/images/OTP.svg" width="60px" alt="">
            <br>
            <strong class="text-danger text-uppercase">
                <?= Translate::get('Xác thực OTP') ?> <i class="fa fa-key"></i>
            </strong>
            <div class="text-muted" style="margin-bottom: 1rem; font-size: 14px">
                <?= Translate::get('Nhập mã OTP được gửi đến số điện thoại bạn đã đăng kí khi mở thẻ') ?>
            </div>
            <div class="otp-input-container">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" class="otp-input otp-input-element" maxlength="1"
                           name="<?= $class_name ?>[otp_digit_<?= $i ?>]"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);"/>
                <?php endfor; ?>
                <input type="text" hidden maxlength="1"
                       name="<?= $class_name ?>[otp]"/>
            </div>

        </div>
    </div>
<?php require_once(__DIR__ . '/../../../_footer-card.php') ?>
<?php ActiveForm::end(); ?>
<script>
    let enable_sumbit = false;

    var className = '<?= $class_name ?>';
    $("#form-checkout").on('beforeSubmit', function (e) {
        if (!enable_sumbit) {
            $('#cover-spin').show(0);
            // $(".btn").attr("disabled", "disabled")

        }
    })
</script>
