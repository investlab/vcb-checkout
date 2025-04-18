<?php 
use common\components\libs\MTQCaptcha;
use common\components\utils\Translate;
use yii\bootstrap\ActiveForm;
?>
<div class="col-sm-12 brdRight">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">
        <h4 class=""><?= Translate::get('Xác thực thẻ liên kết') ?></h4>
        <div class="panel-group row" id="accordion">
            <div class="panel-heading rlv">
                <div class="logo-method">
                    <img src="<?= ROOT_URL ?>/frontend/web/images/credit_card.png" alt="loading...">
                </div>
                <h4 class="panel-title color-vcb"><strong><?=Translate::get('Thẻ Visa / MasterCard / JCB')?></strong></h4>
            </div>
            <div class="form-horizontal" role=form>
                <?php
                $form = ActiveForm::begin(['id' => 'form-verify-2d',
                    'enableAjaxValidation' => false,
                    'action' => $verify2d_url,
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="row">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <?php if ($model->error_message != '') :?>
                                    <div class="alert alert-danger" id="error_message"><?=$model->error_message?></div>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label" id="cardNumber-label">
                                <?=Translate::get('Số tiền xác nhận')?>:
                            </label>
                            <div class="col-sm-4">
                                    <?= $form->field($model, 'verify_amount')->input('number', array('class' => 'form-control', 'maxlength' => 10))->label(false);?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?=Translate::get('Mã bảo mật')?>:</label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'verifyCode')->widget(MTQCaptcha::className(), [
                                    'options' =>['class' => 'form-control text-uppercase input-size', 'maxlength' => 3],
                                    'template' => '<div class="row"><div class="col-sm-5 col-xs-7 pdr5">{input}</div><div class="col-sm-7 col-xs-5 pdl5 form-verify-image">{image}</div></div>',
                                ])->label(false) ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="text-center" id="btn-payment">
            <div class="col-sm-2"></div>
            <div class="col-sm-10 text-center">
                <div class="col-sm-5 col-md-3 col-xs-5">
                </div>
                <button class="col-sm-5 col-md-4 col-xs-5 btn" type="submit" id="pay-button" data-loading-text="<i class='fa fa-spinner fa-spin'></i>">
                    <?=Translate::get('TIẾP TỤC')?>
                </button>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
</div>
