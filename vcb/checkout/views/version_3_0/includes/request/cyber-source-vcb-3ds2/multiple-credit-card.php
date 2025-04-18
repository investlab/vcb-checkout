<?php

use common\components\utils\Translate;
use yii\bootstrap4\ActiveForm;

/** @var Object $model */
?>
<?php require_once(__DIR__ . '/../../../_header-card.php') ?>
<div class="card-body">
    <?php
    $form = ActiveForm::begin([
        'id' => 'form-checkout',
        'action' => $model->getRequestActionForm(),
        'options' => ['class' => 'active credit-card']
    ]);
    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false); ?>
    <div class="form-row">
        <div class="col-md-6">
            <div class="form-group">
                <?php if ($model->error_message != '') : ?>
                    <div class="alert alert-danger"><?= Translate::get($model->error_message) ?></div>
                <?php endif; ?>
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
            <div class="form-group">
                <label for="" class="col-xs-12 control-label"><?= Translate::get('Số thẻ') ?>
                    :</label>
                <div class="col-xs-12">
                    <?= $form->field($model, 'card_number')->input('text', [
                        'class' => 'form-control input-numeric input-size card-number-input credit-card-mask',
                        'id' => 'card_number',
                        'maxlength' => 23,
                    ])->label(false); ?>
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-xs-12 control-label"><?= Translate::get('Họ của chủ thẻ') ?>
                    :</label>
                <div class="col-xs-12">
                    <?= $form->field($model, 'card_first_name')->input('text', array(
                        'class' => 'form-control  input-size card-fullname-input',
                        'id' => 'card_first_name',
                        'maxlength' => 255
                    ))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="" class="col-xs-12 control-label"><?= Translate::get('Tên của chủ thẻ') ?>
                    :</label>
                <div class="col-xs-12">
                    <?= $form->field($model, 'card_last_name')->input('text', array(
                        'class' => 'form-control  input-size card-fullname-input',
                        'id' => 'card_last_name',
                        'maxlength' => 255
                    ))->label(false); ?>
                    <div class="row" style="display: none">
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
                    <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array(
                        'class' => 'form-control input-size card-expired-input',
                        'id' => 'expYear'
                    ))->label(false); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="" class="col-sm-12 control-label"><?= Translate::get('Mã CVV/CVC2') ?>:</label>
                    <div class="col-sm-12">
                        <?= $form->field($model, 'card_cvv')->input('password', array(
                            'class' => 'form-control input-numeric input-size',
                            'id' => 'cvv2',
                            'maxlength' => 4
                        ))->label(false); ?>
                    </div>
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
        <div class="col-md-6">
            <div class="box-card box-card-custom">
                <img src="#" alt="">
                <p class="card-number">
                    <span>xxxx</span>
                    <span>xxxx</span>
                    <span>xxxx</span>
                    <span>xxxx</span>
                </p>
                <p class="card-name"><?= Translate::get('Chủ tài khoản') ?><br/>
                    <span class="card-fullname text-uppercase">Nguyễn Văn A</span></p>
                <p class="card-date"><?= Translate::get('Hết hạn') ?><br/><span>XX/XX</span></p>
            </div>
            <p class="text-rules">
                <label class="stylecheck"><?= Translate::get('Tôi đồng ý với') ?> <a
                            href="#" class="text-primary"><?= Translate::get('điều khoản') ?></a>
                    <?= Translate::get('của Cổng thanh toán') ?>
                    <input type="checkbox" checked="checked">
                    <span class="checkmark"></span>
                </label>
            </p>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . '/../../../_footer-card.php') ?>
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
            console.log('check-change');

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
