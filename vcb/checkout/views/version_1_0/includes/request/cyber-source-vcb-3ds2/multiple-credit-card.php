<?php

use common\components\utils\Translate;
use yii\bootstrap\ActiveForm;

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

</style>
<style>
    .input-group-merge .input-group-text:first-child {
        border-right: 0;
    }

    .input-group-merge .input-group-text:last-child {
        border-left: 0;
    }

    .input-group-merge .form-control:not(:first-child) {
        padding-left: 0;
        border-left: 0;
    }

    .input-group-merge .form-control:not(:last-child) {
        padding-right: 0;
        border-right: 0;
    }

    /*.input-group-text {*/
    /*    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;*/
    /*}*/

    .on-focus-input-card-number {
        border-color: #66afe9;
    }

    .on-validate-card-number-success {
        border-color: #3c763d;
    }

    .on-validate-card-number-error {
        border-color: #a94442;
    }

    @media screen and (max-width: 480px) {
        .form-horizontal .form-group {
            margin-bottom: 0px;
        }
    }


</style>

<div class="panel-heading rlv">
    <div class="logo-method">
        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-', '_', strtolower($model->info['method_code'])) . '.png' ?>"
             alt="loading...">
    </div>
    <h4 class="panel-title color-vcb">
        <strong><?= Translate::get('Thanh toán qua thẻ Visa / MasterCard / JCB / Amex') ?></strong></h4>
</div>
<?php
if (!isset($form)) {
    $form = ActiveForm::begin([
        'id' => 'form-checkout',
        'action' => $model->getRequestActionForm(),
        'options' => ['class' => 'active credit-card']
    ]);
    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
}
?>

<div class="row">
    <div id="cover-spin"></div>
    <div id="st" class="form-group col-sm-10 col-sm-offset-1 hidden">
        <div><strong>Status:</strong> <span id="status"></span></div>
    </div>
    <div class="form-horizontal">
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-1">
                <?php if ($model->error_message != '') : ?>
                    <div class="alert alert-danger"><?= Translate::get($model->error_message) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" id="merchant_id" value="<?= $checkout_order['merchant_id'] ?>"/>
            <input type="hidden" id="token_code" value="<?= $checkout_order['token_code'] ?>"/>
            <input type="hidden" id="payment_method_code" value="<?= $model->info['code'] ?>"/>
            <input type="hidden" id="url_failure"
                   value="<?php Yii::$app->urlManager->createAbsoluteUrl([
                       Yii::$app->controller->id . '/failure',
                       'token_code' => $checkout_order['token_code']
                   ], HTTP_CODE) ?>"/>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Số thẻ') ?>:</label>
            <div class="col-sm-7">
                <div class="input-group input-group-merge" id="input-group-card-number">
                    <!--                    <input type="text" class="form-control credit-card-mask input-group-merge" style="box-shadow: none;">-->
                    <?= $form->field($model, 'card_number')->input('text', array(
                        'class' => 'form-control credit-card-mask',
//                    'onkeypress' => 'return checkDigit(event)',
                        'id' => 'card_number',
                        'maxlength' => 23,
                        'style' => "box-shadow: none;"
                    ))->label(false); ?>
                    <span class="input-group-addon input-group-text cursor-pointer p-1" id="card-type-info"
                          style="background-color: #ffffff; border-left: none;"><span
                                class="card-type"></span></span>
                </div>

            </div>
        </div>

        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Họ của chủ thẻ') ?>
                :</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'card_first_name')->input('text', array(
                    'class' => 'form-control  input-size',
                    'id' => 'card_first_name',
                    'maxlength' => 255
                ))->label(false); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Tên của chủ thẻ') ?>
                :</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'card_last_name')->input('text', array(
                    'class' => 'form-control  input-size',
                    'id' => 'card_last_name',
                    'maxlength' => 255
                ))->label(false); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'ProcessorTransactionId')->input('text', array(
                            'class' => 'hidden',
                            'id' => 'ProcessorTransactionId'
                        ))->label(false); ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'jwt_back')->input('text', array(
                            'class' => 'hidden',
                            'id' => 'jwt_back'
                        ))->label(false); ?>
                    </div>
                </div>

            </div>
        </div>

        <?php
        if (in_array($checkout_order['merchant_id'], [
//                '7',
            '91'
        ])) {
            ?>
            <div class="form-group">
                <label for=""
                       class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Địa chỉ thanh toán') ?>
                    :</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'billing_address')->input('text', array(
                        'class' => 'form-control input-size',
                        'id' => 'billing_address'
                    ))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Thành phố') ?>
                    :</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'city')->input('text', array(
                        'class' => 'form-control input-size',
                        'id' => 'city'
                    ))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Quốc gia') ?>
                    :</label>
                <div class="col-sm-3 col-xs-6">
                    <?= $form->field($model, 'country')->dropDownList($model->getCountry(), array(
                        'class' => 'form-control input-size',
                        'id' => 'country'
                    ))->label(false); ?>
                </div>

            </div>
            <div class="form-group" id="div-state" style="display: none;">
                <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('State') ?>
                    :</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'state')->input('text', array(
                        'class' => 'form-control input-size',
                        'id' => 'state'
                    ))->label(false); ?>
                </div>
            </div>
            <div class="form-group" id="div-portal-code" style="display: none;">
                <label for=""
                       class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Zip or Portal Code') ?>
                    :</label>
                <div class="col-sm-7">
                    <?= $form->field($model, 'zip_or_portal_code')->input('text', array(
                        'class' => 'form-control input-size',
                        'id' => 'zip_or_portal_code',
                        'placeholder' => Translate::get('Ví dụ: Zip Code của khu vực Alberta, thành phố Airdrie là T4A và T4B')
                    ))->label(false); ?>
                </div>
            </div>
        <?php }
        ?>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Ngày hết hạn') ?>
                :</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array(
                    'class' => 'form-control input-size',
                    'id' => 'expMonth'
                ))->label(false); ?>
            </div>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array(
                    'class' => 'form-control input-size',
                    'id' => 'expYear'
                ))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Mã CVV/CVC2') ?>
                :</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_cvv')->input('password', array(
                    'class' => 'form-control input-numeric input-size',
                    'id' => 'cvv2',
                    'maxlength' => 4
                ))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <?php
            if (!empty($model["jwt"])) {
                ?>
                <input type="hidden" id="JWTContainer" value="<?= $model["jwt"] ?>"/>
                <input type="hidden" id="OrderNumber"
                       value="<?= $model['order']['OrderDetails']['OrderNumber'] ?>"/>
                <input type="hidden" id="url"
                       value="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/validate-jwt"]) ?>"/>
            <?php } ?>
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>

<iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10"
        style="display: none;"></iframe>
<form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
    <input id="cardinal_collection_form_input" type="hidden" name="JWT"
           value="">
</form>
<?php $this->registerJsFile("@web/js/cleave.js"); ?>
<?php $this->registerJsFile("@web/js/cleave-phone.js"); ?>
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
    $(".credit-card-mask").focus(function () {
        $("#card-type-info").addClass("on-focus-input-card-number")
    })
    $("#form-checkout").on("afterValidate", function (event, messages, errorAttributes) {
        $("#card-type-info").removeClass("on-focus-input-card-number")
        if (messages['card_number'].length > 0) {
            $("#input-group-card-number").removeClass("input-group");
            $("#input-group-card-number").removeClass("input-group-merge");
            $("#card-type-info").removeClass("on-validate-card-number-success")
            $("#card-type-info").addClass("hidden")
        } else {
            $("#card-type-info").addClass("on-validate-card-number-success")
            $("#card-type-info").removeClass("on-validate-card-number-error")
        }
    });

    $(function () {
        const creditCardMask = document.querySelector('.credit-card-mask');
        if (creditCardMask) {
            new Cleave(creditCardMask, {
                creditCard: true,
                onCreditCardTypeChanged: function (type) {
                    $("#input-group-card-number").addClass("input-group");
                    $("#input-group-card-number").addClass("input-group-merge");
                    $(".field-card_number").removeClass("has-error");
                    $(".field-card_number").removeClass("has-success");
                    $("#card-type-info").addClass("on-focus-input-card-number")
                    $("#card-type-info").removeClass("hidden");
                    $("#card-type-info").addClass("on-focus-input-card-number")
                    if (type != '' && type != 'unknown') {
                        if ($.inArray(type, ["visa", "jcb", "mastercard", "amex"]) !== -1) {
                            $(".field-card_number").children("p").text("")
                            document.querySelector('.card-type').innerHTML =
                                '<img src="' + '<?php Yii::$app->request->baseUrl ?>' + 'images/credit-payments/' + type + '-cc.png" height="22"/>';
                        }
                    } else {
                        document.querySelector('.card-type').innerHTML = '';
                    }
                }
            });
        } else {
            console.log(444);
        }
    })
</script>
<script>

    var sessionID = null;
    let enable_sumbit = false;
    let check_state = false;
    const payment_method_code = $("#payment_method_code").val();
    const type_card = payment_method_code.split("-")[0];


    $(document).ready(function () {
        $('#pay-button').attr('disabled', 'disabled');
        $('#card_number').change(function () {
            checkChange()
        });
        $('#cvv2').change(function () {
            checkChange()
        });
        $('#card_first_name').change(function () {
            checkChange()
        });
        $('#card_last_name').change(function () {
            checkChange()
        });
        $('#expYear').change(function () {
            checkChange()
        });
        $('#expMonth').change(function () {
            checkChange()
        });
        $('#billing_address').change(function () {
            checkChange()
        });
        $('#zip_or_portal_code').change(function () {
            checkChange()
        });
        $('#city').change(function () {
            checkChange()
        });
    })


    function checkChange() {
        if ($('#card_number').val() != "" && $('#expMonth').val() != "" && $('#expMonth').val() != null && $('#expYear').val() != "" && $('#expYear').val() != null && $('#card_first_name').val() != "" && $('#card_last_name').val() != "" && $('#cvv2').val() != "") {
            let card_number = $('#card_number').val();
            let card_name = $('#card_first_name').val() + ' ' + $('#card_last_name').val();
            // console.log(card_name);

            // validateCreditCardNumber();
            // console.log(21);

            let customer_info = {
                card_number: card_number,
                expiration_month: $('#expMonth').val(),
                expiration_year: $('#expYear').val(),
                name_on_account: card_name,
                card_code: $('#cvv2').val(),
                card_type: $('#cardType').val(),

                billing_address: $("#billing_address").val() != "" ? $("#billing_address").val() : false,
                zip_or_portal_code: $("#zip_or_portal_code").val() != "" ? $("#zip_or_portal_code").val() : false,
                city: $("#city").val() != "" ? $("#city").val() : false,
                country: $("#country").val() != "" ? $("#country").val() : false,
                state: $("#state").val() != "" ? $("#state").val() : false,
            }

            let token_code = $("#token_code").val();

            if ($("#billing_address").length == 1 && $("#zip_or_portal_code").length == 1) {
                if ($("#billing_address").val() !== "" &&
                    // $("#zip_or_portal_code").val() !== "" &&
                    $("#city").val() !== "" &&
                    $("#country").val() !== ""
                    // $("#state").val() !== ""
                ) {
                    // console.log(check_state);
                    if (check_state) {
                        if ($("#zip_or_portal_code").val() !== "" && $("#state").val() !== "") {
                            $('#cover-spin').show(0);
                            _setup(customer_info, token_code, payment_method_code)
                        }
                    } else {
                        $('#cover-spin').show(0);
                        _setup(customer_info, token_code, payment_method_code)
                    }

                }
            } else {
                $('#cover-spin').show(0);
                _setup(customer_info, token_code, payment_method_code)
            }
        }
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
                    $("#cardinal_collection_form").attr("action", res.auth_info.deviceDataCollectionURL)

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
                            let card_name = $('#card_first_name').val() + ' ' + $('#card_last_name').val();

                            $('#cover-spin').show(0);
                            $(".btn").attr("disabled", "disabled")
                            let card_number = $('#card_number').val();
                            card_number = card_number.split(" ").join("");
                            let customer_info = {
                                card_number: card_number,
                                expiration_month: $('#expMonth').val(),
                                expiration_year: $('#expYear').val(),
                                name_on_account: card_name,
                                card_code: $('#cvv2').val(),
                                card_type: $('#cardType').val(),
                                referenceID: res.auth_info.referenceID,

                                billing_address: $("#billing_address").val() != "" ? $("#billing_address").val() : false,
                                zip_or_portal_code: $("#zip_or_portal_code").val() != "" ? $("#zip_or_portal_code").val() : false,
                                city: $("#city").val() != "" ? $("#city").val() : false,
                                state: $("#state").val() != "" ? $("#state").val() : false,
                            }
                            let token_code = $("#token_code").val();
                            let payment_method_code = $("#payment_method_code").val();
                            checkEnroll(customer_info, token_code, payment_method_code)
                        }
                        return enable_sumbit;
                    });
                } else if (res.redirect) {
                    window.location.href = res.redirect
                } else {
                    alert(res.error_message);
                    $('#cover-spin').hide(0);
                }
            }
        });
    }


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


</script>
