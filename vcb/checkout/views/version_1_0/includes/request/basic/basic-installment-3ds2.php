<?php

if ($checkout_order['customer_field'] != '') {
    $customer_field = json_decode($checkout_order['customer_field'], true);
    if (isset($customer_field['set-installment']) && $customer_field['set-installment']) {
        require_once('installment-payment-link.php');
        return;
    }
}
$card_type = [
    'VISA' => '001',
    'JCB' => '007',
    'MASTERCARD' => '002',
    'AMEX' => '003',
];

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

?>
<style>
    #cover-spin {
        position: fixed;
        width: 100%;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: none;
    }

    @-webkit-keyframes spin {
        from {
            -webkit-transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    #cover-spin::after {
        content: '';
        display: block;
        position: absolute;
        left: 48%;
        top: 40%;
        width: 40px;
        height: 40px;
        border-style: solid;
        border-color: #4e9a3e;
        border-top-color: transparent;
        border-width: 4px;
        border-radius: 50%;
        -webkit-animation: spin .8s linear infinite;
        animation: spin .8s linear infinite;
    }


    .radio-toolbar {
        margin: 10px;
    }

    .radio-toolbar input[type="radio"] {
        opacity: 0;
        position: fixed;
        width: 0;
    }

    .radio-toolbar label {
        display: inline-block;
        background-color: #fff;
        padding: 5px;
        font-family: sans-serif, Arial;
        font-size: 16px;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
    }

    .radio-toolbar label:hover {
        background-color: #dfd;
    }

    .radio-toolbar input[type="radio"]:focus + label {
        border: 2px dashed #444;
    }

    .radio-toolbar input[type="radio"]:checked + label {
        background-color: #FFF;
        border-color: #4c4;
    }

    ul.cardList.clearfix .card-bank.selected {
        border: 2px solid #4c4;
    }

    #col-card-name .field-ProcessorTransactionId, #col-card-name .field-jwt_back {
        height: 0px;
    }

    #id-horizontal .form-group.field-merchant_id {
        height: 0px;
        margin-bottom: 0px;
    }

    @media screen and (max-width: 480px) {
        #form-bank {
            display: flex !important;
        }
    }

    #cdtg-7 .field-card_info {
        height: 0;
        margin: 0px;
    }

</style>

<div class="panel-heading rlv">
    <div class="logo-method">
        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-', '_', strtolower($model->info['method_code'])) . '.png' ?>"
             alt="loading...">
    </div>
    <h4 class="panel-title color-vcb"><strong><?= Translate::get('Thanh toán trả góp') ?></strong></h4>
</div>

<?php
$form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active credit-card']]);
echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
?>
<?php //if (!empty($model->fields)):?>
<div class="row">
    <div id="cover-spin"></div>

    <div class="form-horizontal" id="id-horizontal">
        <?= $form->field($model, 'merchant_id')->input('text', array('class' => 'hidden', 'id' => 'merchant_id', 'value' => $checkout_order['merchant_id']))->label(false); ?>
        <div class="form-group" style="margin-bottom: 0px">
            <div class="col-sm-10 col-sm-offset-1">
                <?php if ($model->error_message != '') : ?>
                    <div class="alert alert-danger"
                         id="error_message"><?= $model->error_message ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group" id="form-bank">
            <label for="" class="col-sm-3 control-label"><?= Translate::get('Ngân hàng') ?>:</label>
            <div class="col-sm-7">
                <div class="bankwrap clearfix"><i class="<?= $model->config['class'] ?>"></i>
                    <div class="cardInfo">
                        <p class="hidden-xs"><?= Translate::get($model->info['name']) ?></p>
                        <input type="hidden" name="bank" class="bank" id="bank" value="<?= $model->config['class'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="" class="col-sm-3 control-label"><?= Translate::get('Loại thẻ') ?>:</label>
            <div class="col-sm-7" id="cdtg-7">
                <ul class="cardList clearfix">
                    <?php if ($model->getBankCard($model->payment_method_code) != null){
                    foreach ($model->getBankCard($model->payment_method_code) as $item => $value){ ?>
                    <li>
                        <div class="radio-toolbar boxWrap card-bank" id="card-bank  <?= $value ?>" style=""
                        ">
                        <input type="hidden" name="card_info" value="<?= $card_type[$value] ?>" id="<?= $value ?>"
                               class="card_info">
                        <i class="<?= $value ?> card-bank-i" style="top: 0"></i>
            </div>
            </li>
            <?php }
            } ?>

            </ul>
            <?= $form->field($model, 'card_info')->input('text', array('class' => 'hidden', 'id' => 'card_info', 'value' => ""))->label(false); ?>
            <input type="hidden" name="card_type" value="" class="card_type" id="card_type">
            <input type="hidden" name="info_card" value="" class="info_card" id="info_card">
            <input type="hidden" id="token_code" value="<?= $checkout_order['token_code'] ?>"/>
            <input type="hidden" id="payment_method_code" value="<?= $model->info['code'] ?>"/>


        </div>
    </div>
    <div class="form-group form-cycle" id="form-cycle" style="display: none">
        <label id="label" class="col-sm-3 control-label" style="top: 13px;"><?= Translate::get('Kỳ hạn trả góp') ?>
            :</label>
        <div class="col-sm-7" style="display: block; ">
            <div style="display:flex">
                <?php if ($model->getBankCycle($model->payment_method_code) != null) {
                    foreach ($model->getBankCycle($model->payment_method_code) as $item => $value) {
                        ?>
                        <div class="radio-toolbar list-cycle list-cycle-<?php echo $value['card_type']; ?>"
                             id="kyhan cycle" style="" hidden>
                            <input id="<?= $value['card_type'] . ' ' . $value['cycle'] ?>" type="radio"
                                   name="card_cycle" value="<?= $value['cycle'] ?>" class="cycle"
                                   onclick="getCycle(<?= $value['fee'] ?>,<?= $value['cycle'] ?>)">
                            <label for="<?= $value['card_type'] . ' ' . $value['cycle'] ?>"
                                   style="margin-bottom: 0px"><?= $value['cycle'] . ' ' . Translate::get('Tháng') ?></label>
                        </div>
                    <?php }
                } ?>
                <input id="card_fee" type="hidden" name="card_fee" value="" class="card_fee">
                <input id="card_fee_bearer" type="hidden" name="card_fee_bearer"
                       value="<?= $model->getFeeBearer($model->payment_method_code) != null ? $model->getFeeBearer($model->payment_method_code) : '' ?>"
                       class="card_fee_bearer">
                <input id="amount_fee" type="hidden" name="amount_fee" value="" class="amount_fee">
            </div>
            <div>
                <?= $form->field($model, 'cycle_installment')->input('text', array('class' => 'hidden', 'id' => 'cycle_installment', 'value' => ""))->label(false); ?>
            </div>
        </div>
    </div>
    <div class="form-card" id="form-card" style="display:none;">
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Số thẻ') ?>:</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'card_number')->input('text', array('class' => 'form-control input-size', 'onkeypress' => 'return checkDigit(event)', 'id' => 'card_number', 'maxlength' => 23))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Tên chủ thẻ') ?>
                :</label>
            <div class="col-sm-7" id="col-card-name">
                <?= $form->field($model, 'card_fullname')->input('text', array('class' => 'form-control text-uppercase input-size', 'id' => 'card_name', 'maxlength' => 255))->label(false); ?>
                <?= $form->field($model, 'ProcessorTransactionId')->input('text', array('class' => 'hidden', 'id' => 'ProcessorTransactionId'))->label(false); ?>
                <?= $form->field($model, 'jwt_back')->input('text', array('class' => 'hidden', 'id' => 'jwt_back'))->label(false); ?>
                <input type="hidden" name="name_card" value="" id="name_card" class="name_card">
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Ngày hết hạn') ?>
                :</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control input-size', 'id' => 'expMonth'))->label(false); ?>
            </div>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control input-size', 'id' => 'expYear'))->label(false); ?>
            </div>
        </div>
        <div class="form-group" id="cvv-block">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Mã CVV/CVC2') ?>
                :</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_cvv')->input('password', array('class' => 'form-control input-numeric input-size', 'id' => 'cvv', 'maxlength' => 4))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <?php
            if (!empty($model["jwt"])) {
                ?>
                <input type="hidden" id="JWTContainer" value="<?= $model["jwt"] ?>"/>
                <input type="hidden" id="OrderNumber" value="<?= $model['order']['OrderDetails']['OrderNumber'] ?>"/>
                <input type="hidden" id="url"
                       value="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/validate-jwt"]) ?>"/>
            <?php } ?>
        </div>
    </div>
</div>
<div class="hide-for-xs hidden-mobile">
    <hr>
</div>
<div class="row">
    <div class="form-horizontal mform0">
        <?php if ($model->getPayerFee() != 0 || \common\models\db\Merchant::hasViewFeeFree($checkout_order['merchant_info'])): ?>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?= Translate::get('Giá trị đơn hàng') ?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <strong><?= ObjInput::makeCurrency($checkout_order['amount']) ?></strong> <?= $checkout_order['currency'] ?>
                    </p>
                </div>
            </div>
            <div class="form-group mrgb0 mline hidden-mobile">
                <label for="" class="col-sm-4 control-label"><?= Translate::get('Phí thanh toán') ?>:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <?php if ($model->getPayerFee() != 0) : ?>
                            <strong><?= ObjInput::makeCurrency($model->getPayerFee()) ?></strong> <?= $model->merchant_fee_info['currency'] ?>
                        <?php else: ?>
                            <strong><?= Translate::get('Miễn phí') ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group mrgb0 mline hidden-mobile">
            <label for="" class="col-sm-4 col-xs-6 control-label"><?= Translate::get('Tổng tiền') ?>:</label>
            <div class="col-sm-8 col-xs-6">
                <p class="form-control-static fontS14 bold text-danger">
                    <strong><?= ObjInput::makeCurrency($model->getPaymentAmount()) ?> <?= $checkout_order['currency'] ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>
<iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10" style="display: none;"></iframe>
<form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
    <input id="cardinal_collection_form_input" type="hidden" name="JWT"
           value="">
</form>
<?php
if (APP_ENV == 'prod') {
    ?>
    <script src="https://songbird.cardinalcommerce.com/edge/v1/songbird.js"></script>
    <?php
} else {
    ?>
    <script src="https://songbirdstag.cardinalcommerce.com/cardinalcruise/v1/songbird.js"></script>
    <?php
}
?>
<script>
    let enable_sumbit = false;

    let type_card = '';
    let type_card_id = '';
    let cycle = '';
    let amount_fee;
    $('.card-bank').on('click', function (e) {
        let sel = $(this).children(".card_info").attr("id");
        $(".list-cycle").hide();
        $(".list-cycle-" + sel).show();
        if (type_card != $(this).children(".card_info").val()) {
            type_card = $(this).children(".card_info").val();
            type_card_id = $(this).children(".card_info").attr("id");
            let id_card_bank = 'div#card-bank\\ ' + type_card;
            $(".card-bank.selected").removeClass("selected");
            $(id_card_bank).addClass('selected');
            document.getElementById('card_info').value = type_card;
            document.getElementById('card_type').value = type_card_id;
            document.getElementById('card_number').value = '';
            document.getElementById('card_name').value = '';
            switch (type_card_id) {
                case 'VISA':
                    $('.card-bank-i').css('border', '0');
                    $('.VISA').css('border', '2px solid #4c4');
                    break;
                case 'JCB':
                    $('.card-bank-i').css('border', '0');
                    $('.JCB').css('border', '2px solid #4c4');
                    break;
                case 'MASTERCARD':
                    $('.card-bank-i').css('border', '0');
                    $('.MASTERCARD').css('border', '2px solid #4c4');
                    break;
                case 'AMEX':
                    $('.card-bank-i').css('border', '0');
                    $('.AMEX').css('border', '2px solid #4c4');
                default:
            }
        }
        $('#form-cycle').css('display', 'block')
        document.getElementById('cycle_installment').value = '';
    })
    function getCycle(fee, cycle) {
        document.getElementById('cycle_installment').value = cycle;
        $('#form-card').css('display', 'block');
        //số tiền nm phải chịu
        card_fee_bearer = $('#card_fee_bearer').val();
        document.getElementById('card_fee').value = fee;
        let partner_payment_fee = <?= $model['merchant_fee_info']['sender_flat_fee'] ?>;
        let amount_order = <?= $model["order"]['OrderDetails']['Amount'] ?>;
        if (card_fee_bearer == 3) {
            amount_fee = amount_order + (amount_order * ((fee / 2) / 100)) + partner_payment_fee;
        } else if (card_fee_bearer == 2) {
            amount_fee = amount_order + (amount_order * fee) / 100 + partner_payment_fee;
        } else if (card_fee_bearer == 1) {
            amount_fee = amount_order + partner_payment_fee;
        }
        document.getElementById('amount_fee').value = amount_fee;
    }
    function updateStatus(msg) {
        $('#status').html(msg);
        $('#st').removeClass('disabled');
    }
    function cc_format(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
        var matches = v.match(/\d{4,19}/g);
        var match = matches && matches[0] || ''
        var parts = []
        for (i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4))
        }
        if (parts.length) {
            return parts.join(' ')
        } else {
            return value
        }
    }
    onload = function () {
        document.getElementById('card_number').oninput = function () {
            this.value = cc_format(this.value)
        }
    }
    function checkDigit(event) {
        var code = (event.which) ? event.which : event.keyCode;
        if ((code < 48 || code > 57) && (code > 31)) {
            return false;
        }
        return true;
    }
    $(document).ready(function () {
        $('#card_number').change(function () {
            var pan = document.getElementById('card_number').value
            getPartnerPayment();
            Cardinal.trigger('accountNumber.update', pan)
                .then(function (results) {
                    console.log(results)
                    writeLog(results, $('#OrderNumber').val());
                })
        });
    });
    $(document).ready(function () {
        $('#card_name').change(function () {
            document.getElementById('name_card').value = $('#card_name').val();
        })
    })
    $(document).ready(function () {
        $('#pay-button').attr('disabled', 'disabled');
        const d = new Date();
        $('#expYear').on('click', function (e) {
            if (d.getFullYear() === $('#expYear').val() && (d.getMonth() + 1) > $('#expMonth').val()) {
                alert('Thời gian không hợp lệ vui lòng chọn lại!')
                document.getElementById("expMonth").value = '';
                document.getElementById("expYear").value = '';
            }
        })
    })
    function writeLog(ErrorDescription, OrderNumber) {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/ajax-write-log/write-log-cbs3ds2'?>',
            type: 'get',
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            cache: false,
            data: {
                ErrorDescription: ErrorDescription,
                OrderNumber: OrderNumber,
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>'
            },
            success: function (result) {
                console.log(result);
            }
        });
    }
    function getPartnerPayment() {
        var bank = $('.bank').val();
        var card_number = $('#card_number').val();
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/get-check-excluded-date' ?>',
            type: 'get',
            data: {
                type_card: type_card,
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                bank: bank,
                card_number: card_number,
            },
            async: true,
            dataType: "json",
            contentType: "application/json",
            success: function (result) {
                console.log(result)
                if (result.method == true) {
                    if (result.message_error == '') {
                        alert('Ngày giao dịch bị từ chối vui lòng sử dụng thẻ khác!');
                    } else {
                        alert(result.message_error);
                    }
                    document.getElementById('card_number').value = '';
                } else {
                    if (result.message_error != '') {
                        alert(result.message_error)
                        document.getElementById('card_number').value = '';
                    }
                }
            },
            error: function (xhr) {
                console.log(xhr);
            }
        });
    }


//     QuangNT
    const card_name = $('#card_name');
    const card_number = $('#card_number');
    const cvv = $('#cvv');
    const exp_month = $('#expMonth');
    const exp_year = $('#expYear');

    card_name.change(function () {
        checkChange()
    });
    card_number.change(function () {
        checkChange()
    });
    cvv.change(function () {
        checkChange()
    });
    exp_month.change(function () {
        checkChange()
    });
    exp_year.change(function () {
        checkChange()
    });

    function checkChange() {
        if (card_name.val() !== "" && card_number.val() !== "" && cvv.val() !== "" && exp_month.val() !== "" && exp_year.val() !== "") {


            let customer_info = {
                card_number: card_number.val(),
                expiration_month: exp_month.val(),
                expiration_year: exp_year.val(),
                name_on_account: card_name.val(),
                card_code: cvv.val(),
                card_type: type_card,
            }
            //
            let token_code = $("#token_code").val();
            let payment_method_code = $("#payment_method_code").val();

                $('#cover-spin').show(0);
                _setup(customer_info, token_code, payment_method_code)
        }
    }


    function _setup(custommer_info, token_code, payment_method_code) {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/setup-author' ?>',
            // async: false,
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                custommer_info: custommer_info,
                token_code: token_code,
                payment_method_code: payment_method_code,
            },
            success: function (res) {
                if (res.status) {
                    $('#cover-spin').hide(0);

                    Cardinal.configure({
                        logging: {
                            level: 'on'
                        }
                    });

                    Cardinal.setup("init", {
                        jwt: res.auth_info.accessToken
                    });

                    $("#cardinal_collection_form_input").val(res.auth_info.accessToken)
                    $("#cardinal_collection_form").attr("action",res.auth_info.deviceDataCollectionURL)

                    document.getElementById('cardinal_collection_form').submit()

                    sessionID = res.auth_info.referenceID

                    Cardinal.on('payments.setupComplete', function (setupCompleteData) {
                        // console.log(setupCompleteData);
                        cardinalSetupDone = true;
                        if (res.time_process < 3) {
                            setTimeout(function () {
                                // console.log(54);
                                $('#pay-button').removeAttr('disabled');
                            }, 3000 - res.time_process)
                        } else {
                            $('#pay-button').removeAttr('disabled');
                        }
                        // if (documentReady) {
                        //     $('#pay-button').removeAttr('disabled');
                        // }
                    });


                    $("#form-checkout").on('beforeSubmit', function (e) {

                        if (!enable_sumbit) {

                            $('#cover-spin').show(0);
                            $(".btn").attr("disabled", "disabled")
                            let card_number_val = card_number.val();
                            card_number_val = card_number_val.split(" ").join("");
                            let customer_info = {
                                card_number: card_number_val,
                                expiration_month: exp_month.val(),
                                expiration_year: exp_year.val(),
                                name_on_account: card_name.val(),
                                card_code: cvv.val(),
                                card_type: type_card,
                                referenceID: res.auth_info.referenceID,

                                billing_address: $("#billing_address").val() != "" ? $("#billing_address").val() : false,
                                zip_or_portal_code: $("#zip_or_portal_code").val() != "" ? $("#zip_or_portal_code").val() : false,
                                city: $("#city").val() != "" ? $("#city").val() : false,
                                country: $("#country").val() != "" ? $("#country").val() : false,
                                state: $("#state").val() != "" ? $("#state").val() : false,
                            }
                            let token_code = $("#token_code").val();
                            let payment_method_code = $("#payment_method_code").val();
                            checkEnroll(customer_info, token_code, payment_method_code)
                        }
                        return enable_sumbit;
                    });
                } else if(res.redirect){
                    window.location.href = res.redirect
                }
            }
        });
    }

    function checkEnroll(custommer_info, token_code, payment_method_code) {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
            // async: false,
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                custommer_info: custommer_info,
                token_code: token_code,
                payment_method_code: payment_method_code,
            },
            success: function (res) {
                $('#cover-spin').hide(0);
                if (res.status) {
                    if (res.valid) {
                        let processTrans = res.auth_info.authenticationTransactionID;
                        let {auth_info} = res;
                        const continueData = {
                            AcsUrl: auth_info.acsURL,
                            Payload: auth_info.paReq,
                            challengeWindowSize: 10
                        };
                        const orderObjectV2 = {
                            OrderDetails: {
                                TransactionId: auth_info.authenticationTransactionID
                            }
                        };

                        Cardinal.trigger('bin.process', custommer_info.card_number).then(function (results) {
                            Cardinal.continue('cca', continueData, orderObjectV2);
                        }).catch(function (error) {
                            console.log(error);
                        });

                        Cardinal.on("payments.validated", function (data, jwt) {
                            let {PAResStatus} = data.Payment.ExtendedData;
                            let {ECIFlag} = data.Payment.ExtendedData;
                            writeLog(data, $('#OrderNumber').val());
                            if (["N", "R", "U"].includes(PAResStatus) && (ECIFlag === undefined || ['00', '07'].includes(ECIFlag))) {
                                if (false && $("#merchant_id").val() == "91") {
                                    enable_sumbit = true;
                                    $('#ProcessorTransactionId').val(processTrans);
                                    $("#form-checkout").submit();
                                } else {
                                    $.ajax({
                                        url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
                                        // async: false,
                                        type: 'post',
                                        data: {
                                            _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                                            custommer_info: custommer_info,
                                            token_code: token_code,
                                            payment_method_code: payment_method_code,
                                            enrrol_checked: true,
                                        },
                                        success: function (res) {
                                            if (!res.status && res.redirect !== undefined) {
                                                window.location.href = res.redirect
                                            }
                                        }
                                    })
                                }
                            } else {
                                const ErrorDescription = data.ErrorDescription;
                                if (ErrorDescription === 'Success') { // Buyer enrolled in 3DS and successfully authenticated
                                    $('#ProcessorTransactionId').val(data.Payment.ProcessorTransactionId);
                                    $('#jwt_back').val(jwt);
                                    writeLog(data, $('#OrderNumber').val());
                                    enable_sumbit = true;
                                    $("#form-checkout").submit();
                                } else {
                                    $.ajax({
                                        url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
                                        // async: false,
                                        type: 'post',
                                        data: {
                                            _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                                            custommer_info: custommer_info,
                                            token_code: token_code,
                                            payment_method_code: payment_method_code,
                                            enrrol_checked: true,
                                        },
                                        success: function (res) {
                                            if (!res.status && res.redirect !== undefined) {
                                                window.location.href = res.redirect
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $('#ProcessorTransactionId').val(res.auth_info.authenticationTransactionID);
                        enable_sumbit = true;
                        $("#form-checkout").submit();
                    }
                } else if (res.redirect) {
                    window.location.href = res.redirect
                } else {
                    alert(res.error_message);
                    window.history.back()
                }

            }
        });
    }





</script>



