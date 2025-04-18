<?php

use common\components\libs\MTQCaptcha;
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

    .vcb #btn-payment .btn {
        margin: 0px;
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


<div class="modal fade" id="modal-notify" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;
                </button>
                <h4 class="modal-title"><?= Translate::get('Thông báo') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <div class="alert alert-warning fade in" align="center">
                        <span id="error_message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12 brdRight">
    <div id="cover-spin"></div>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">
        <h4 class=""><?= Translate::get('Nhập thông tin thẻ liên kết') ?></h4>
        <div class="panel-group row" id="accordion">
            <div class="panel-heading rlv">
                <div class="logo-method">
                    <img src="<?= ROOT_URL ?>/frontend/web/images/credit_card.png" alt="loading...">
                </div>
                <h4 class="panel-title color-vcb"><strong><?= Translate::get('Thẻ Visa / MasterCard / JCB / Amex') ?></strong>
                </h4>
            </div>
            <div class="form-horizontal" role=form>
                <?php
                $form = ActiveForm::begin(['id' => 'form-checkout',
                    'enableAjaxValidation' => false,
                    'action' => $index_url,
                    'options' => ['enctype' => 'multipart/form-data', 'class' => "active credit-card"]])
                ?>
                <div class="row">
                    <div class="form-horizontal">
                        <input type="hidden" id="ProcessorTransactionId" name="ProcessorTransactionId">
                        <input type="hidden" id="jwt_back" name="jwt_back">
                        <input type="hidden" id="merchant_id" name="merchant_id" value="<?php echo $merchant_id ?>">
                        <input type="hidden" id="link_card_token" name="link_card_token" value="<?php echo $token ?>">
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-1">
                                <?php
                                if ($model->error_message != '') : ?>
                                    <div class="alert alert-danger"
                                         id="error_message"><?= $model->error_message ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label" id="cardNumber-label">
                                <?= Translate::get('Số thẻ') ?>:
                            </label>
                            <div class="col-sm-7">
                                <?= $form->field($model, 'card_number')->input('text', array('class' => 'form-control', 'maxlength' => 19, 'id' => 'card_number', 'onkeypress' => 'return checkDigit(event)'))->label(false); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 col-xs-12 col-md-3 control-label">
                                <?= Translate::get('Ngày hết hạn') ?>:
                            </label>
                            <div class="col-sm-3 col-xs-6">
                                <?= $form->field($model, 'card_month')->dropDownList($model->getExpiredCardMonths(), array('class' => 'form-control input-size', 'id' => 'card_month'))->label(false); ?>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), array('class' => 'form-control input-size', 'id' => 'card_year'))->label(false); ?>
                            </div>
                        </div>
                        <div id="cvv-block" class="form-group">
                            <label for="" class="col-xs-12 col-sm-4 col-md-3 control-label" id="cvv_code-label">
                                <?= Translate::get('Mã CVV/CVC2') ?>:
                            </label>
                            <div class="col-sm-3">
                                <?= $form->field($model, 'cvv_code')->input('password', array('class' => 'form-control', 'maxlength' => 4, 'id' => 'cvv_code'))->label(false); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($merchant_id == 19): ?>
                <div class="text-center" style="width: 100%">
                    <p style="color: #4e9a3e;font-style: italic;display: flex;flex-direction: row; padding: 0px 10px">
                        <span style="min-width: 50px; text-align: left"><ins>Lưu ý</ins>:</span>
                        <span style="text-align: justify">
        Để đảm bảo an toàn, thông tin thẻ của Quý khách sẽ được mã hoá sau khi nhập. Do vậy FWD Việt Nam không trực tiếp lưu thông tin thẻ của Quý khách. Thông tin này chỉ được lưu bởi Tổ chức xử lý thanh toán được cấp phép.<br>
        Ngân Lượng cam kết tuân thủ theo chuẩn bảo mật của Hội đồng Tiêu chuẩn Bảo mật, đồng thời Ngân Lượng cũng đạt level 1 chứng chỉ PCI DSS.
        </span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center" id="btn-payment">
            <div class="col-sm-1"></div>
            <div class="col-sm-10 text-center text-uppercase">
                <div class="col-md-4"></div>
                <div class="col-sm-12 col-md-4 col-xs-12">
                    <button disabled class="btn form-control" type="submit" id="pay-button"
                            data-loading-text="<i class='fa fa-spinner fa-spin'></i>">
                        <?= Translate::get('TIẾP TỤC') ?>
                    </button>
                </div>
                <div class="col-md-4"></div>
            </div>
            <div class="col-sm-1"></div>
        </div>
        <?php ActiveForm::end() ?>
    </div>

    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10"
            style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT"
               value="">
    </form>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
</div>
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
    var sessionID = null;
    let enable_submit = false;
    const link_card_token = $("#link_card_token").val();
    var close_model_reload_page = false;

    const card_name = $("#card_full_name");
    const card_number = $("#card_number");
    const card_month = $("#card_month");
    const card_year = $("#card_year");
    const cvv_code = $("#cvv_code");

    var payment_info;

    card_name.add(card_number).add(card_month).add(card_year).add(cvv_code).change(function () {
        if (card_name.val() !== "" && card_number.val() !== "" && card_month.val() !== "" && card_year.val() !== "" && cvv_code.val() !== "") {
            payment_info = {
                card_name: card_name.val(),
                card_number: card_number.val(),
                card_month: card_month.val(),
                card_year: card_year.val(),
                cvv_code: cvv_code.val(),
            }
            PASetup();
        }
    })

    function PASetup() {
        showSpin();

        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/link-card/process-card?card_token_id=' . $token ?>',
            // async: false,
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                function: 'setup',
                payment_info: payment_info,
            },
            success: function (res) {
                console.log(res);
                if (res.status) {
                    Cardinal.setup("init", {
                        jwt: res.setup_response.accessToken
                    });

                    $("#cardinal_collection_form_input").val(res.setup_response.accessToken)
                    $("#cardinal_collection_form").attr("action", res.setup_response.deviceDataCollectionURL)

                    document.getElementById('cardinal_collection_form').submit()

                    sessionID = res.setup_response.referenceID

                    Cardinal.on('payments.setupComplete', function (setupCompleteData) {
                        cardinalSetupDone = true;
                        hideSpin();
                        $('#pay-button').removeAttr('disabled');
                    });

                    $("#form-checkout").on('beforeSubmit', function (e) {
                        $(".btn").attr("disabled", "disabled")
                        if (!enable_submit) {
                            $('#cover-spin').show(0);
                            enrollment()
                        }
                        return enable_submit;
                    });
                } else if (res.redirect) {
                    window.location.href = res.redirect
                } else {
                    hideSpin();
                    $("#error_message").text(res.error_message)
                    $("#modal-notify").modal('show');
                    close_model_reload_page = true;
                }
            }
        });
    }

    function enrollment() {
        showSpin();
        $("#pay-button").attr("disabled", "disabled")
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/link-card/process-card?card_token_id=' . $token ?>',
            // async: false,
            type: 'post',
            data: {
                _csrf: '<?=Yii::$app->request->getCsrfToken()?>',
                function: 'enrollment',
                payment_info: payment_info,
                referenceID: sessionID
            },
            success: function (res) {
                hideSpin()
                if (res.status) {
                    var {enrollment_info} = res;
                    console.log(enrollment_info)
                    if (enrollment_info.challenge) {
                        let processTrans = enrollment_info.authenticationTransactionID;
                        const continueData = {
                            AcsUrl: enrollment_info.acsURL,
                            Payload: enrollment_info.paReq,
                            challengeWindowSize: 10
                        };
                        const orderObjectV2 = {
                            OrderDetails: {
                                TransactionId: enrollment_info.authenticationTransactionID
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
                            if (["N", "R", "U"].includes(PAResStatus) && (ECIFlag === undefined || ['00', '07'].includes(ECIFlag))) {
                                $("#error_message").text("Xác thực thẻ thất bại")
                                $("#modal-notify").modal('show');
                                console.log(11)

                            } else {
                                const ErrorDescription = data.ErrorDescription;
                                if (ErrorDescription === 'Success') { // Buyer enrolled in 3DS and successfully authenticated
                                    $('#ProcessorTransactionId').val(data.Payment.ProcessorTransactionId);
                                    $('#jwt_back').val(jwt);
                                    enable_submit = true;
                                    $("#form-checkout").submit();
                                } else {
                                    $("#error_message").text("Xác thực thẻ thất bại")
                                    $("#modal-notify").modal('show');
                                }
                            }
                        });
                    } else if (!enrollment_info.challenge) {
                        $('#ProcessorTransactionId').val(enrollment_info.authenticationTransactionID);
                        enable_submit = true;
                        $("#form-checkout").submit();
                    }
                } else {
                    $("#error_message").text(res.error_message)
                    $("#modal-notify").modal('show');
                    close_model_reload_page = true
                }
            }
        });
    }

    $('#modal-notify').on('hidden.bs.modal', function () {
        if (close_model_reload_page) {
            window.location.reload()

        }
    })


    $("#btn-confirm").click(function () {
        window.location.reload();
    })

    function hideSpin() {
        $('#cover-spin').hide(0);
    }

    function showSpin() {
        $('#cover-spin').show(0);
    }

    function checkDigit(event) {
        var code = (event.which) ? event.which : event.keyCode;
        if ((code < 48 || code > 57) && (code > 31)) {
            return false;
        }
        return true;
    }
</script>
