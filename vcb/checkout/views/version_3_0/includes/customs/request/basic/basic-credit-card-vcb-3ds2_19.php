<?php
$card_type = [
    'VISA' => '001',
    'JCB' => '007',
    'MASTERCARD' => '002',
    'AMEX' => '003',
];

use common\components\utils\ObjInput;
use common\models\db\Merchant;
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

</style>
<div class="panel-heading rlv">
    <div class="logo-method">
        <img src="<?= ROOT_URL . '/vi/checkout/images/' . str_replace('-', '_', strtolower($model->info['method_code'])) . '.png' ?>"
             alt="loading...">
    </div>
    <h4 class="panel-title color-vcb">
        <strong><?= Translate::get('Thanh toán qua thẻ Visa / MasterCard / JCB') ?></strong></h4>
</div>
<?php
if (!isset($form)) {
    $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active credit-card']]);
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
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Loại thẻ') ?>:</label>
            <div class="col-sm-7">
                <div class="bankwrap clearfix"><i class="<?= $model->config['class'] ?>"></i>
                    <input type="hidden" id="merchant_id" value="<?= $checkout_order['merchant_id'] ?>"/>
                    <input type="hidden" id="cardType" value="<?= $card_type[$model->config['class']] ?>"/>
                    <input type="hidden" id="token_code" value="<?= $checkout_order['token_code'] ?>"/>
                    <input type="hidden" id="payment_method_code" value="<?= $model->info['code'] ?>"/>
                    <input type="hidden" id="url_failure"
                           value="<?php Yii::$app->urlManager->createAbsoluteUrl([Yii::$app->controller->id . '/failure', 'token_code' => $checkout_order['token_code']], HTTP_CODE) ?>"/>
                    <div class="cardInfo">
                        <p class="hidden-xs"><?= Translate::get($model->info['name']) ?></p>
                    </div>
                </div>
            </div>
            <?= $form->field($model, 'ProcessorTransactionId')->input('text', array('class' => 'hidden', 'id' => 'ProcessorTransactionId'))->label(false); ?>
            <?= $form->field($model, 'jwt_back')->input('text', array('class' => 'hidden', 'id' => 'jwt_back'))->label(false); ?>
        </div>
        <!--        <input required>-->
        <div class="form-group">
            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label"><?= Translate::get('Số thẻ') ?>:</label>
            <div class="col-sm-7">
                <?= $form->field($model, 'card_number')->input('text', array('class' => 'form-control input-size', 'onkeypress' => 'return checkDigit(event)', 'id' => 'card_number', 'maxlength' => 23))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Ngày hết hạn') ?>:</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), array('class' => 'form-control input-size', 'id' => 'expMonth'))->label(false); ?>
            </div>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control input-size', 'id' => 'expYear'))->label(false); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label"><?= Translate::get('Mã CVV/CVC2') ?>:</label>
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($model, 'card_cvv')->input('password', array('class' => 'form-control input-numeric input-size', 'id' => 'cvv2', 'maxlength' => 4))->label(false); ?>
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
<div class="text-center" style="width: 100%">
    <p style="color: #4e9a3e;font-style: italic;display: flex;flex-direction: row; padding: 0px 10px">
        <span style="min-width: 50px; text-align: left"><ins>Lưu ý</ins>:</span>
        <span style="text-align: justify">
        Để đảm bảo an toàn, thông tin thẻ của Quý khách sẽ được mã hoá sau khi nhập. Do vậy FWD Việt Nam không trực tiếp lưu thông tin thẻ của Quý khách. Thông tin này chỉ được lưu bởi Tổ chức xử lý thanh toán được cấp phép.<br>
        Ngân Lượng cam kết tuân thủ theo chuẩn bảo mật của Hội đồng Tiêu chuẩn Bảo mật, đồng thời Ngân Lượng cũng đạt level 1 chứng chỉ PCI DSS.
        </span>
    </p>
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
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
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
<script>
    $('#pay-button').attr('disabled', 'disabled');
    function hideSpin() {
        $('#cover-spin').hide(0);
    }

    function showSpin() {
        $('#cover-spin').show(0);
    }

    const token_code = $("#token_code").val();
    const payment_method_code = $("#payment_method_code").val();



    let sessionID = null;
    let enable_submit = false;
    let check_state = false;

    const card_number = $("#card_number");
    const card_month = $("#expMonth");
    const card_year = $("#expYear");
    const cvv_code = $("#cvv2");
    const card_type = $("#cardType");

    let payment_info;

    card_number.add(card_month).add(card_year).add(cvv_code).change(function () {
        if (card_number.val() !== "" && card_month.val() !== "" && card_year.val() !== "" && cvv_code.val() !== "") {
            payment_info = {
                card_number: card_number.val(),
                expiration_month: card_month.val(),
                expiration_year: card_year.val(),
                card_code: cvv_code.val(),
                card_type: card_type.val(),
            }
            PASetup();
        }
    })

    function PASetup() {
        showSpin();
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/setup-author' ?>',
            // async: false,
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                custommer_info: payment_info,
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
                    payment_info.referenceID = res.auth_info.referenceID;
                    $("#cardinal_collection_form_input").val(res.auth_info.accessToken)
                    $("#cardinal_collection_form").attr("action",res.auth_info.deviceDataCollectionURL)
                    document.getElementById('cardinal_collection_form').submit()
                    sessionID = res.auth_info.referenceID

                    Cardinal.on('payments.setupComplete', function (setupCompleteData) {
                        cardinalSetupDone = true;
                        if (res.time_process < 3) {
                            setTimeout(function () {
                                hideSpin();
                                $('#pay-button').removeAttr('disabled');
                            }, 3000 - res.time_process)
                        } else {
                            hideSpin();
                            $('#pay-button').removeAttr('disabled');
                        }
                    });
                    $("#form-checkout").on('beforeSubmit', function (e) {
                        if (!enable_submit) {
                            $('#cover-spin').show(0);
                            $(".btn").attr("disabled", "disabled")
                            enrollment()
                        }
                        return enable_submit;
                    });
                } else if(res.redirect){
                    window.location.href = res.redirect
                }
            }
        });
    }


    function enrollment() {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                custommer_info: payment_info,
                token_code: token_code,
                payment_method_code: payment_method_code,
            },
            success: function (res) {
                hideSpin();
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

                        Cardinal.trigger('bin.process', payment_info.card_number).then(function (results) {
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
                                    enable_submit = true;
                                    $('#ProcessorTransactionId').val(processTrans);
                                    $("#form-checkout").submit();
                                } else {
                                    $.ajax({
                                        url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
                                        // async: false,
                                        type: 'post',
                                        data: {
                                            _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                                            custommer_info: payment_info,
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
                                    enable_submit = true;
                                    $("#form-checkout").submit();
                                } else {
                                    $.ajax({
                                        url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/check-enroll' ?>',
                                        // async: false,
                                        type: 'post',
                                        data: {
                                            _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                                            custommer_info: payment_info,
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
                        enable_submit = true;
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
<script>
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



