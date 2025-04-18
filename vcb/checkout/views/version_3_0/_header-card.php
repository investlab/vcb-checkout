<?php

use common\components\utils\Translate;

/** @var array $checkout_order */
/** @var Object $model */
/** @var string $lang_request */
?>
<div class="card-header" id="headingOne"
     style="border-bottom-right-radius: 0px;border-bottom-left-radius: 0px; ">
    <button class="btn btn-link btn-block text-left collapsed p-t-0" type="button"
            style="position: relative;line-height: initial;">
        <!--                        <img class="logo-bank" src="images/bank/shb.png" alt="">-->
        <img class="logo-bank"
             style="height: 36px !important; width: 88px !important;"
             src="dist/images/bank/<?=
             strtoupper(@$model->config['class']) ?>.png"
             alt="<?= @$model->config['class'] ?>">
        <?php
        $paymentMethods = [
            'QR-CODE' => 'Thanh toán bằng mã QR Ngân hàng',
            'ATM-CARD' => 'Thanh toán bằng thẻ ATM ngân hàng',
            'IB-ONLINE' => 'Thanh toán bằng tài khoản Internet Banking Ngân hàng',
            'CREDIT-CARD' => 'Thanh toán quốc tế qua thẻ',
            'TRA-GOP' => 'Thanh toán trả góp qua',
        ];

        // Lấy mã phương thức thanh toán
        $methodCode = @$model->info['method_code'];

        // Kiểm tra nếu phương thức thanh toán tồn tại trong mảng
        if (array_key_exists($methodCode, $paymentMethods)) {
            if ($methodCode == 'QR-CODE') {
                $translation = $paymentMethods[$methodCode];
                echo Translate::getV3($translation);
            } else {
                $translation = $paymentMethods[$methodCode];
                echo Translate::getV3($model->info['name'], null, $translation);
            }

        } else {
            // Nếu không có phương thức nào khớp, in ra tên mặc định
            echo Translate::getV3($model->info['name']);
        }
        ?>
        <!-- NÚT Chọn lại  -->
        <p style="margin-top: 10px;top: 60px;position: absolute;">
            <?php if (Yii::$app->controller->action->id !== 'verify'): ?>
                <a href="<?=
                Yii::$app->urlManager->createAbsoluteUrl([
                    "version_3_0/index",
                    "token_code" => $checkout_order['token_code']
                ]) ?>"
                   id="btn_back_method"
                   class="tit-other text-primary font-weight-bolder text-uppercase d-none"
                   style="width: max-content;"><i
                            class="las la-redo-alt mr-1"></i><?= Translate::getV3('Chọn lại') ?></a>
            <?php endif; ?>
        </p>
    </button>
</div>
<script>
    $(document).ready(function () {
        let key = 'is_show_btb_back_' + '<?=$checkout_order['token_code']?>';
        let is_show_btn_back = getItemWithExpiry(key);
        console.log('is_show_btn_back: ' + is_show_btn_back);
        if (is_show_btn_back === true) {
            $('#btn_back_method').removeClass('d-none')
        }
    })

</script>