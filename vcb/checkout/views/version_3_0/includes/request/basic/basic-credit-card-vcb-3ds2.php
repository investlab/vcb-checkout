<?php
$card_type = [
    'VISA' => '001',
    'JCB' => '007',
    'MASTERCARD' => '002',
    'AMEX' => '003',
];

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use common\components\utils\Translate;

if (file_exists(Yii::getAlias('@app/views/') . Yii::$app->controller->id . '/includes/customs/request/basic/basic-credit-card-vcb-3ds2_' . $checkout_order["merchant_id"] . '.php')) {
    include(Yii::getAlias('@app/views/') . Yii::$app->controller->id . '/includes/customs/request/basic/basic-credit-card-vcb-3ds2_' . $checkout_order["merchant_id"] . '.php');
} else {
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
                    <input type="hidden" id="cardType" value="<?= $card_type[$model->config['class']] ?>"/>
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
                            'enableClientValidation' => false,
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
                <?php require_once(__DIR__ . '/../../../_card-number.php') ?>
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
    <script>
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
    </script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
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
    <?php
//if (in_array($checkout_order['merchant_id'], ['91', '78'])) {
    if (false) {
        ?>
        <script></script>
    <?php } else {
        ?>
        <script>
            var sessionID = null;
            let enable_sumbit = false;
            let check_state = false;
            let check_expired = false;
            const payment_method_code = $("#payment_method_code").val();
            const type_card = payment_method_code.split("-")[0];
            console.log('type_card: ' + type_card);

            let card_type = {
                'VISA': '001',
                'JCB': '007',
                'MASTERCARD': '002',
                'AMEX': '003'
            }; // new

            $('#cardType').val(card_type[type_card]);
            $(document).ready(function () {
                $('#pay-button').attr('disabled', 'disabled');
                console.log('pay-btn');
                $('#card_number').change(function () {
                    checkChange()
                    validateCreditCardNumber()
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
                    validateExpirationDate();
                    checkChange()
                });
                $('#expMonth').change(function () {
                    validateExpirationDate();
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

                $('#accept-terms').change(function () {
                    $('#accept-terms').removeClass('is-invalid');
                    checkChange()
                });
                // $('#country').select2();
                $('#country').change(function () {
                    if ($(this).val() === "US" || $(this).val() === "CA") {
                        check_state = true;
                        $("#state").val("")
                        $("#zip_or_portal_code").val("")
                        $("#div-portal-code").show();
                        $("#div-state").show();
                    } else {
                        check_state = false;
                        $("#state").val("false")
                        $("#zip_or_portal_code").val("false")
                        $("#div-portal-code").hide();
                        $("#div-state").hide();
                    }
                    checkChange()
                }).select2();
                $('#state').change(function () {
                    checkChange()
                });
            })


            function checkChange() {
                if ($('#card_number').val() != "" && $('#expMonth').val() != "" && $('#expMonth').val() != null && $('#expYear').val() != "" && $('#expYear').val() != null && $('#card_first_name').val() != "" && $('#card_last_name').val() != "" && $('#cvv2').val() != "" && check_expired === false) {
                    <?php if(in_array($checkout_order["merchant_id"],
                    array_merge($GLOBALS['MERCHANT_BCA'], $GLOBALS['MERCHANT_XNC']))): ?>
                    if (!$('#accept-terms').is(':checked')) {
                        $('#accept-terms').addClass('is-invalid');
                        // Focus vào checkbox
                        $('#accept-terms').focus();
                        return false;
                    }
                    <?php endif; ?>


                    let card_number = $('#card_number').val();
                    let card_name = $('#card_first_name').val() + ' ' + $('#card_last_name').val();
                    // console.log(card_name);

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
                    let payment_method_code = $("#payment_method_code").val();
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
                                    if ($('#enable-merchant-confirm').val() != true) {
                                        $('#cover-spin').show(0);
                                    }
                                    _setup(customer_info, token_code, payment_method_code)
                                }
                            } else {
                                if ($('#enable-merchant-confirm').val() != true) {
                                    $('#cover-spin').show(0);
                                }
                                _setup(customer_info, token_code, payment_method_code)
                            }

                        }
                    } else {
                        if ($('#enable-merchant-confirm').val() != true) {
                            $('#cover-spin').show(0);
                        }
                        _setup(customer_info, token_code, payment_method_code)
                    }
                }
            }

            function checkEnroll(custommer_info, token_code, payment_method_code) {
                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/version_3_0/check-enroll' ?>',
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
                                        $.ajax({
                                            url: '<?php echo Yii::$app->request->baseUrl . '/version_3_0/check-enroll' ?>',
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
                $(document).ready(function () {
                    // $('#modal-confirm').show();
                    // check MC tại đây
                    // ...
                    if ($('#enable-merchant-confirm').val() == true) {
                        $("#modal-confirm").modal("show");
                        $('#btn-confirm').click(function () {
                            // $('#form-checkout').submit();
                            $("#modal-confirm").modal("hide");
                            $('#cover-spin').show(0);
                            // XỬ LÝ SETUP
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
                                            cardinalSetupDone = true;
                                            if (res.time_process < 3) {
                                                setTimeout(function () {
                                                    // console.log(54);
                                                    $('#cover-spin').hide(0);
                                                    $('#pay-button').removeAttr('disabled');
                                                }, 3000 - res.time_process)
                                            } else {
                                                $('#cover-spin').hide(0);
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
                                                    country: $("#country").val() != "" ? $("#country").val() : false,
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
                                    }
                                }
                            });
                            // END XỬ LÝ SETUP
                        })
                    } else {
                        // XỬ LÝ SETUP
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
                                        cardinalSetupDone = true;
                                        if (res.time_process < 3) {
                                            setTimeout(function () {
                                                // console.log(54);
                                                $('#cover-spin').hide(0);
                                                $('#pay-button').removeAttr('disabled');
                                            }, 3000 - res.time_process)
                                        } else {
                                            $('#cover-spin').hide(0);
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
                                                country: $("#country").val() != "" ? $("#country").val() : false,
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
                                    window.location.reload();
                                }
                            }
                        });
                        // END XỬ LÝ SETUP
                    }
                });
            }

        </script>


        <?php
    }
    ?>

    <script>
        function validateCreditCardNumber() {
            console.log('validateCreditCardNumber');
            var ccNum = document.getElementById("card_number").value.replace(/\s/g, '');
            const visaRegEx = /^(?:4[0-9]{12}(?:[0-9]{3})?)$/;
            const mastercardRegEx = /^(?:5[1-5][0-9]{14})$/;
            const amexpRegEx = /^(?:3[47][0-9]{13})$/;
            const jcbRegEx = /^(?:2131|1800|35\d{3})\d{11}$/;
            const unionPay = /^(62[0-9]{14,17})$/;
            var isValid = false;


            // if (type_card == "VISA")

            if (type_card == "VISA" && visaRegEx.test(ccNum)) {
                isValid = true;
            } else if (type_card == "MASTERCARD" && mastercardRegEx.test(ccNum)) {
                isValid = true;
            } else if (type_card == "AMEX" && amexpRegEx.test(ccNum)) {
                isValid = true;
            } else if (type_card == "JCB" && jcbRegEx.test(ccNum)) {
                isValid = true;
            } else if (type_card == "UPI" && unionPay.test(ccNum)) {
                isValid = true;
            }
            if (!isValid) {
                document.getElementById("card_number").value = ""
                alert("<?= Translate::get('Vui lòng sử dụng đúng loại thẻ bạn đã chọn - ') ?>" + " " + type_card);
                //$("#error_message").text("<?php //= Translate::get('Vui lòng sử dụng đúng loại thẻ bạn đã chọn - ') ?>//" + " " + type_card)
                //$("#modal-notify").modal('show')
            }
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


        function validateExpirationDate() {
            check_expired = false;
            var currentDate = new Date();
            var currentMonth = currentDate.getMonth() + 1; // Tháng bắt đầu từ 0 nên cần cộng 1
            var currentYear = currentDate.getFullYear();
            var selectedMonth = parseInt($('#expMonth').val());
            var selectedYear = parseInt($('#expYear').val());
            $('#expMonth').removeClass('is-invalid-expired');
            if (selectedYear < currentYear || (selectedYear === currentYear && selectedMonth < currentMonth)) {
                alert('Ngày hết hạn hợp lệ.');
                $('#expMonth').addClass('is-invalid-expired');
                var feedbackDiv = $('#expMonth').siblings('.invalid-feedback');
                check_expired = true;
            }
        }


        function validateTermsAccess() {
            checked_terms = false;
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
        }
    </script>


    <?php
}
