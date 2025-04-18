<?php

use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;


if (!isset($form)) {
    $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $action, 'options' => ['class' => 'active credit-card']]);
    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
}
?>
<div class="form-group">
    <?php
    if (!empty($model["jwt"])) {
        ?>
        <input type="hidden" id="JWTContainer" value="<?= $data_cyber['accessToken'] ?>"/>
        <input type="hidden" id="token_code" value="<?= $checkout_order['token_code'] ?>"/>
        <input type="hidden" id="OrderNumber" value="<?= $model['order']['OrderDetails']['OrderNumber'] ?>"/>
        <input type="hidden" id="url"
               value="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/validate-jwt"]) ?>"/>
        <input type="hidden" id="ProcessorTransactionId" name="ProcessorTransactionId">
        <input type="hidden" id="referenceID" name="reference_id" value="<?= $data_cyber['referenceID'] ?>">
        <input type="hidden" id="jwt_back" name="jwt_back">
    <?php } ?>
    <?php ActiveForm::end() ?>

    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10"
            style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="<?php echo $data_cyber['deviceDataCollectionURL'] ?>">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT"
               value="<?php echo $data_cyber['accessToken'] ?>">
    </form>


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
    document.getElementById('cardinal_collection_form').submit()

    setTimeout(function () {
        Cardinal.configure({
            logging: {
                level: 'on'
            }
        });
        Cardinal.setup("init", {
            jwt: document.getElementById("JWTContainer").value
        });


        Cardinal.on('payments.setupComplete', function (setupCompleteData) {
            $.ajax({
                url: '<?php echo Yii::$app->request->baseUrl . '/card-token/enrollment/' .$checkout_order['token_code'] ?>',
                // async: false,
                type: 'post',
                data: {
                    _backendCSRF: '<?=Yii::$app->request->getCsrfToken()?>',
                    reference_id: '<?= $data_cyber['referenceID'] ?>'
                },
                success: function (res) {
                    if (res.status) {
                        var {enrollment_info} = res;
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

                            Cardinal.continue('cca', continueData, orderObjectV2)

                            Cardinal.on("payments.validated", function (data, jwt) {
                                const ErrorDescription = data.ErrorDescription;
                                if (ErrorDescription === 'Success') { // Buyer enrolled in 3DS and successfully authenticated
                                    $('#ProcessorTransactionId').val(data.Payment.ProcessorTransactionId);
                                    $('#jwt_back').val(jwt);
                                    enable_submit = true;
                                    $("#form-checkout").submit();
                                } else {
                                    $('#ProcessorTransactionId').val(data.Payment.ProcessorTransactionId);
                                    enable_sumbit = true;
                                    $("#form-checkout").submit();
                                }

                            });
                        } else if(!enrollment_info.challenge) {
                            $('#ProcessorTransactionId').val(enrollment_info.authenticationTransactionID);
                            enable_submit = true;
                            $("#form-checkout").submit();
                        } else {
                            $("#error_message").text(res.error_message)
                            $("#modal-notify").modal('show');
                            close_model_reload_page = true
                        }
                    } else {
                        $("#error_message").text(res.error_message)
                        $("#modal-notify").modal('show');
                        close_model_reload_page = true
                    }

                }
            })

        })


    }, 1000);
</script>

</script>