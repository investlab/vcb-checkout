<?php

use common\components\libs\MTQCaptcha;
use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use common\components\utils\Translate;

?>

<?php require_once(__DIR__ . '/../../../_header-card.php') ?>
    <div class="card-body">
        <?php /** @var Object $model */
        $form = ActiveForm::begin([
            'id' => 'form-checkout',
            'action' => $model->getRequestActionForm(),
            'options' => ['class' => 'active']
        ]);
        ?>

        <div class="form-row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php if ($model->error_message != '') : ?>
                        <div class="alert alert-danger"><?= Translate::get($model->error_message) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($model->fields)): ?>
                    <?php
                    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
                    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
                    ?>
                    <?php foreach ($model->fields as $code => $field) : ?>
                        <?php if ($code == 'BANK_ACCOUNT') : ?>
                            <div class="form-group">
                                <?= $form->field($model, 'account_number')->input('text', array(
                                    'class' => 'form-control card-number-input',
                                    'maxlength' => 19,
                                    'placeholder' => Translate::get("Nhập số thẻ ATM")
                                )); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($code == 'BANK_NAME') : ?>
                            <div class="form-group">
                                <?= $form->field($model, 'account_fullname')->input('text', array(
                                    'class' => 'form-control text-uppercase card-fullname-input-atm',
                                    'maxlength' => 255,
                                    'id' => 'card_fullname',
                                    'placeholder' => Translate::get("Nhập tên chủ thẻ")
                                )); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($code == 'ISSUE_MONTH') : ?>
                            <div class="form-group">
                                <label for=""
                                       class="col-sm-3 col-xs-12 control-label"><?= Translate::get('Ngày phát hành') ?>
                                    :</label>
                                <div class="col-sm-3 col-xs-6">
                                    <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(),
                                        array('class' => 'form-control'))->label(false); ?>
                                </div>
                                <div class="col-sm-3 col-xs-6">
                                    <?= $form->field($model, 'card_year')->dropDownList($model->getIssueCardYears(),
                                        array('class' => 'form-control'))->label(false); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($code == 'EXPIRED_MONTH') : ?>
                            <div class="form-group">
                                <label for=""
                                       class="col-sm-3 col-xs-12 control-label"><?= Translate::get('Ngày hết hạn') ?>
                                    :</label>
                                <div class="col-sm-3 col-xs-6">
                                    <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(),
                                        array('class' => 'form-control'))->label(false); ?>
                                </div>
                                <div class="col-sm-3 col-xs-6">
                                    <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(),
                                        array('class' => 'form-control'))->label(false); ?>
                                </div>
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
                                    ))->label(false); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($code == 'IDENTITY_NUMBER') : ?>
                            <div class="form-group">
                                <div class="form-group">
                                    <?= $form->field($model, 'identity_number')->input('text', array(
                                        'class' => 'form-control text-uppercase',
                                        'maxlength' => 255,
                                        'id' => 'mobile',
                                        'placeholder' => Translate::get('Số CMT/CCCD'),
                                    ))->label(false); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>

                <?php endif; ?>
                <div class="form-group">
                    <?= $form->field($model, 'verifyCode')->widget(MTQCaptcha::className(), [
                        'options' => [
                            'class' => 'form-control',
                            'maxlength' => 3,
                            'placeholder' => 'Nhập mã bảo mật',
                        ],
                        'captchaAction' => 'version_1_0/captcha',

                        'template' => '
                            <div class="row">
                                <div class="col-sm-5 col-xs-7 pdr5">
                                    {input}
                                    <div class="invalid-feedback">{error}</div> <!-- Hiển thị lỗi -->
                                </div>
                                <div class="col-sm-7 col-xs-5 pdl5 form-verify-image">
                                    {image}
                                </div>
                            </div>'
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <?php require_once(__DIR__ . '/../../../_card-number.php') ?>
            </div>
        </div>
    </div>
<?php require_once(__DIR__ . '/../../../_footer-card.php') ?>
<?php ActiveForm::end(); ?>