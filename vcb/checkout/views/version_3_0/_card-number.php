<?php

use common\components\utils\Translate;

/** @var Object $model */
?>


<?php if (isset($model->fields) && !empty($model->fields)): ?>
    <div class="box-card box-card-custom">
        <img src="dist/images/bank/<?=
        strtoupper(@$model->config['class']) ?>.png" alt="">
        <p class="card-number">
            <span>xxxx</span>
            <span>xxxx</span>
            <span>xxxx</span>
            <span>xxxx</span>
        </p>
        <p class="card-name"><?= Translate::get('Chủ tài khoản') ?><br/>
            <span class="card-fullname text-uppercase">____ ___ _</span></p>

        <?php if ($model->fields['ISSUE_MONTH']): ?>
            <p class="card-date"><?= Translate::get('Phát hành') ?>
                <br/><span>__/__</span></p>
        <?php endif; ?>


        <?php if ($model->fields['EXPIRED_MONTH']): ?>
            <p class="card-date"><?= Translate::get('Hết hạn') ?>
                <br/><span>__/__</span></p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="box-card box-card-custom">
        <img src="dist/images/bank/<?=
        strtoupper(@$model->config['class']) ?>.png" alt="<?= @$model->config['class'] ?>">
        <p class="card-number">
            <span>xxxx</span>
            <span>xxxx</span>
            <span>xxxx</span>
            <span>xxxx</span>
        </p>
        <p class="card-name"><?= Translate::get('Chủ tài khoản') ?><br/>
            <span class="card-fullname text-uppercase">____ ___ ___</span></p>
        <p class="card-date"><?= Translate::get('Hết hạn') ?><br/><span>XX/XX</span></p>
    </div>
<?php endif; ?>

<?php if (in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_XNC']) || in_array($checkout_order["merchant_id"],
        $GLOBALS['MERCHANT_BCA'])): ?>
    <div class="text-rules">
        <input type="checkbox" class="form-check-input" id="accept-terms">
        <label class="form-check-label stylecheck" for="accept-terms"><?= Translate::get('Tôi đồng ý với') ?>
            <a href="javascript::" class="text-primary" data-toggle="modal"
               data-target="#access-terms-modal"><?= Translate::get('điều khoản') ?></a>
            <?= Translate::get('của Cổng thanh toán') ?></label>
        <div class="invalid-feedback"><?= Translate::get('Bạn phải chấp nhận các điều khoản và điều kiện.') ?></div>
    </div>

    <script>
        let checked_terms = false;
        $(document).ready(function () {
            checked_terms = false;
            $('#accept-terms').closest('form').on('submit', function (event) {
                // Kiểm tra xem checkbox có được checked hay không
                if (!$('#accept-terms').is(':checked')) {
                    event.preventDefault(); // Ngăn không cho form submit
                    // Thêm lớp is-invalid vào checkbox để hiển thị lỗi
                    $('#accept-terms').addClass('is-invalid');
                    // Focus vào checkbox
                    $('#accept-terms').focus();
                } else {
                    checked_terms = true;
                    // Xóa lớp is-invalid nếu checkbox đã được chọn
                    $('#accept-terms').removeClass('is-invalid');
                }
            });
        });
    </script>

<?php endif; ?>
