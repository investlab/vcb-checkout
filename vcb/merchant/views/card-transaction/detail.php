<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CardLogFull;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\models\db\CardLog;

$this->title = Translate::get('Thông tin thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?= Translate::get('Thông tin thẻ cào') ?></h1>

    <div class="row">
        <div class="col-md-9">
            <div class="form-horizontal pdtop2 mform" role="form">
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label bold"> <?= Translate::get('Mã giao dịch')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static bold">
                            <?= @$card_log['id'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Loại thẻ')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= @$card_log['card_type_info']['name'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Mã thẻ')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= @$card_log['card_code'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Số serial')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= @$card_log['card_serial'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Mệnh giá')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static text-primary">
                            <?= ObjInput::makeCurrency(@$card_log['card_price']) ?>
                            &nbsp;<?= @$card_log['currency'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Phí')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static text-danger">
                            <?= @$card_log['percent_fee'] ?> VND
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Thực nhận')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static text-primary">
                            <?= ObjInput::makeCurrency(@$card_log['card_amount']) ?>
                            &nbsp;<?= @$card_log['currency'] ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mrgb0">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Mã tham chiếu')?>:</label>
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <?= @$card_log['merchant_refer_code'] ?>
                        </p>
                    </div>
                </div>
                <div class="hide-for-xs">
                    <hr>
                </div>
                <div class="form-group mrgb0">
                    <label for="inputPassword3" class="col-sm-4 control-label "> <?= Translate::get('Thời gian tạo')?>:</label>
                    <div class="col-sm-8 pdr5">
                        <p class="form-control-static">
                            <?php if (intval($card_log['time_created']) > 0): ?>
                                <?= date('H:i, d/m/Y', $card_log['time_created']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="form-group mline">
                    <label for="" class="col-sm-4 control-label"> <?= Translate::get('Trạng thái thẻ')?>:</label>
                    <div class="col-sm-8 pdr5 mgtop5">
                        <p class="form-control-static">
                            <?php if ($card_log['card_status'] == CardLogFull::CARD_STATUS_FAIL) { ?>
                                <span class="label label-danger"><?= Translate::get('Thẻ sai') ?></span>
                            <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_TIMEOUT) { ?>
                                <span class="label label-warning"><?= Translate::get('Timeout') ?></span>
                            <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_NO_SUCCESS) { ?>
                                <span class="label label-default"><?= Translate::get('Thẻ chưa bị gạch') ?></span>
                            <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_SUCCESS) { ?>
                                <span class="label label-success"><?= Translate::get('Thành công') ?></span>
                            <?php } ?>
                        </p>
                    </div>
                </div>

            </div>
            <!--begin button-->
            <div class="pdtop">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('card-transaction/search')?>" class="btn btn-danger"> <?= Translate::get('Quay lại')?></a>
            </div>
        </div>
    </div>

</div>
