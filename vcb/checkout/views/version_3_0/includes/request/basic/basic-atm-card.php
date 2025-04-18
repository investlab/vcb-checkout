<?php

use common\components\libs\MTQCaptcha;
use common\components\utils\ObjInput;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use common\components\utils\Translate;
use common\components\utils\CheckMobile;

$device = CheckMobile::isMobile();
?>
<?php require_once(__DIR__ . '/../../../_header-card.php') ?>
<div class="card-body">
    <?php $form = ActiveForm::begin([
        'id' => 'form-checkout',
        'class' => 'form-request',
        'action' => $model->getRequestActionForm(),
        'options' => ['class' => 'active']
    ]); ?>
    <div class="form-row">
        <div class="col-md-6">
            <?php if (!empty($model->fields)): ?>
                <?php
                echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
                echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
                ?>

                <div class="form-group">
                    <?php if ($model->error_message != '') : ?>
                        <div class="alert alert-danger"><?= Translate::get($model->error_message) ?></div>
                    <?php endif; ?>
                </div>
                <?php foreach ($model->fields as $code => $field) : ?>
                    <?php if ($code == 'BANK_ACCOUNT') : ?>
                        <div class="form-group">
                            <?= $form->field($model, 'card_number')->input('text', array(
                                'class' => 'form-control card-number-input',
                                'maxlength' => 19,
                                'placeholder' => Translate::get("Nhập số thẻ ATM")
                            )); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($code == 'BANK_NAME') : ?>
                        <div class="form-group">
                            <?= $form->field($model, 'card_fullname')->input('text', array(
                                'class' => 'form-control text-uppercase card-fullname-input-atm',
                                'maxlength' => 255,
                                'id' => 'card_fullname',
                                'placeholder' => Translate::get("Nhập tên chủ thẻ")
                            )); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($code == 'MOBILE') : ?>
                        <div class="form-group">
                            <div class="form-group">
                                <?= $form->field($model, 'mobile')->input('text', array(
                                    'class' => 'form-control text-uppercase',
                                    'maxlength' => 255,
                                    'id' => 'mobile',
                                    'placeholder' => Translate::get('Số điện thoại chủ thẻ')
                                )) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($code == 'ISSUE_MONTH') : ?>
                        <div class="form-group row">
                            <label for="" class="col-sm-12 control-label"><?= Translate::get('Ngày phát hành') ?>
                                :</label>
                            <div class="col-sm-6 col-xs-6">
                                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array(
                                    'class' => 'form-control input-size card-expired-input',
                                    'id' => 'expMonth'
                                ))->label(false); ?>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <?= $form->field($model, 'card_year')->dropDownList($model->getIssueCardYears(),
                                    array(
                                        'class' => 'form-control input-size card-expired-input',
                                        'enableClientValidation' => false,
                                        'id' => 'expYear'
                                    ))->label(false); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($code == 'EXPIRED_MONTH') : ?>

                        <div class="form-group row">
                            <label for="" class="col-sm-12 control-label"><?= Translate::get('Ngày hết hạn') ?>
                                :</label>
                            <div class="col-sm-6 col-xs-6">
                                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array(
                                    'class' => 'form-control input-size card-expired-input',
                                    'id' => 'expMonth'
                                ))->label(false); ?>
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(),
                                    array(
                                        'class' => 'form-control input-size card-expired-input',
                                        'enableClientValidation' => false,
                                        'id' => 'expYear'
                                    ))->label(false); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?= $form->field($model, 'verifyCode')->widget(MTQCaptcha::className(), [
                'options' => [
                    'class' => 'form-control',
                    'maxlength' => 3,
                    'placeholder' => Translate::get('Nhập mã bảo mật'),
                ],
                'captchaAction' => 'version_1_0/captcha',

                'template' => '
                            <div class="row">
                                <div class="col-sm-6 col-xs-6 pdr5">
                                    {input}
                                    <div class="invalid-feedback">{error}</div> <!-- Hiển thị lỗi -->
                                </div>
                                <div class="col-sm-6 col-xs-6 pdl5 form-verify-image">
                                    {image}
                                </div>
                            </div>'
            ]) ?>

            <?php else: ?>
                <div class="row">
                    <div class="form-horizontal mform2 pdtop">
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <?php if ($model->error_message != '') : ?>
                                    <div class="alert alert-danger"><?= Translate::get($model->error_message) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php require_once(__DIR__ . '/../../../_card-number.php') ?>
        </div>

    </div>
</div>
<?php require_once(__DIR__ . '/../../../_footer-card.php') ?>
<?php ActiveForm::end(); ?>
<script>
    let check_expired = false;

    function validateExpirationDate() {
        check_expired = false;
        var currentDate = new Date();
        var currentMonth = currentDate.getMonth() + 1; // Tháng bắt đầu từ 0 nên cần cộng 1
        var currentYear = currentDate.getFullYear();
        var selectedMonth = parseInt($('#expMonth').val());
        var selectedYear = parseInt($('#expYear').val());
        $('#expMonth').removeClass('is-invalid-expired');
        console.log('currentMonth:' + currentMonth);
        console.log('currentYear:' + currentYear);
        console.log('selectedMonth:' + selectedMonth);
        console.log('currentMonth:' + selectedYear);

        <?php if(isset($model->fields['ISSUE_MONTH'])): ?>
        if (selectedYear > currentYear || (selectedYear === currentYear && selectedMonth > currentMonth)) {
            alert('Ngày phát hành không hợp lệ.');
            $('#expMonth').addClass('is-invalid-expired');
            check_expired = true;
        }
        <?php elseif (isset($model->fields['EXPIRED_MONTH'])): ?>
        if (selectedYear < currentYear || (selectedYear === currentYear && selectedMonth < currentMonth)) {
            alert('Ngày hết hạn không hợp lệ.');
            $('#expMonth').addClass('is-invalid-expired');
            check_expired = true;
        }
        <?php endif; ?>

    }


    $(document).ready(function () {
        $('#expYear').change(function () {
            validateExpirationDate();
        });
        $('#expMonth').change(function () {
            validateExpirationDate();
        });
        $('#form-checkout').on('submit', function (event) {
            console.log(check_expired);
            if (check_expired === true) {
                console.log('1234')
                event.preventDefault();
            }
        });
    });
</script>

