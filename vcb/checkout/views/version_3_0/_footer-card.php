<?php

use common\components\utils\ObjInput;
use common\components\utils\Translate;
use yii\helpers\Url;

/** @var Object $model */
/** @var array $checkout_order */
$action = Yii::$app->controller->action->id;


if (strpos($model['payment_method_code'], 'QR') !== false && !empty($action) && in_array($action, ['verify'])) {
    $btn_submit = '<button type="submit" class="btn-primary" onclick="window.location.reload();" >'
        . Translate::get('Kiểm tra đơn hàng')
        . '<i class="las la-check-circle"></i></button>';
} else {
    $btn_submit = '<button type="submit" id="pay-button" class="btn-primary">'
        . Translate::get('Thanh toán')
        . '<i class="las la-arrow-right"></i></button>';
}

$is_installment = false;
if(strpos($model['payment_method_code'], 'TRA-GOP') !== false){
    $is_installment = true;
}
$countdown_timer = (@$checkout_order['time_limit'] - @$checkout_order['time_created']) - (time() - @$checkout_order['time_created']);
//$countdown_timer = 0; // BAT LEN NEU CAN DEBUG


?>
<div class="card-footer form-row">
    <div class="cf-left col-sm-6">
        <ul>
            <li class="cfl-left">
                <!-- TIMER-->
                <div id="cancel_url" value="<?= $checkout_order['cancel_url'] ?>"></div>
                <p id="label_timer" style="font-size: 15px !important;" class="text-primary"></p>
                <div class="count text-left">
                    <strong> <span id="timer" style="color: red; font-size: 1rem"> </span></strong>
                </div>
                <div hidden id="text_count_down"
                     value="<?= Translate::get('Thời hạn thanh toán còn lại') ?>"
                     data-token="<?= $checkout_order['token_code'] ?>"
                     data-url-detroy="<?= Url::to(['version_3_0/transaction-destroy-v2']) ?>"

                ></div>
                <div hidden id="hidden-time-created"
                     value="<?= $countdown_timer ?>"><?= $countdown_timer ?></div>
                <!--  END TIMER-->
            </li>

            <li class="cfl-right">
                <?= Translate::get('Tổng tiền') ?> <i class="las la-angle-right"></i>
                <b class="text-primary" >
                    <span id="total_amount_display">
                        <?= ObjInput::makeCurrency($checkout_order['amount'] + $checkout_order['sender_fee']) ?>
                    </span>
                    <?= $checkout_order['currency'] ?>
                </b>

            </li>
        </ul>
    </div>
    <div class="cf-right col-sm-6">
        <!-- <button type="button" disabled="">Thanh toán <i class="las la-arrow-right"></i></button> -->
        <?= $btn_submit ?>
    </div>

</div>