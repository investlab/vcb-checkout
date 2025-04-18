<?php

/** @var $model */
/** @var $checkout_order */
?>
<?php

use common\components\utils\Logs;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
use common\models\db\CheckoutOrder;
use yii\bootstrap4\ActiveForm;

$card_type = [
    'VISA' => '001',
    'JCB' => '007',
    'MASTERCARD' => '002',
    'AMEX' => '003',
];
//if ($model->getPayerFee() != 0) {
//    $total_amount = $model->getPayerFee() + $checkout_order['amount'];
//} else {
//    $total_amount = $checkout_order['amount'];
//}
$total_amount = [];

$feeBearer = $model->getFeeBearer($model->payment_method_code) != null ? $model->getFeeBearer($model->payment_method_code) : '';
$merchant_id = $model['checkout_order']['merchant_info']['id'] ?? null;

?>
<!-- style copy 1 phan tu version1 -->
<?php require_once(__DIR__ . '/../../../_header-card.php') ?>

<div class="card-body">
    <?php
    if (!isset($form)) {
        $form = ActiveForm::begin([
            'id'      => 'form-checkout',
            'action'  => $model->getRequestActionForm(),
            'options' => ['class' => 'active credit-card'],
        ]);
        echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
        echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
    }
    ?>
    <div class="form-row">
        <div class="col-md-6">
            <div class="form-group">
                <?php if (isset($error_message) && $error_message != '') : ?>
                    <div class="alert alert-danger"><?= Translate::get($error_message) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="" class="col-xs-12 control-label"><?= Translate::get('Số thẻ') ?>
                        :</label>
                    <div class="col-xs-12">
                        <?= $form->field($model, 'card_number')->input('text', [
                            'class'     => 'form-control input-numeric input-size card-number-input credit-card-mask',
                            'id'        => 'card_number',
                            'maxlength' => 23,
                        ])->label(false); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for=""
                           class="col-xs-12 control-label"><?= Translate::get('Tên chủ thẻ') ?>
                        :</label>
                    <div class="col-xs-12" id="col-card-name">
                        <?= $form->field($model, 'card_fullname')->textInput([
                            'class'     => 'form-control text-uppercase input-size card-fullname-input-atm',
                            'id'        => 'card_name',
                            'maxlength' => 255,
                        ])->label(false); ?>
                        <?= $form->field($model, 'ProcessorTransactionId')->hiddenInput([
                            'class' => 'hidden',
                            'id'    => 'ProcessorTransactionId',
                        ])->label(false); ?>
                        <?= $form->field($model, 'jwt_back')->hiddenInput([
                            'class' => 'hidden',
                            'id'    => 'jwt_back',
                        ])->label(false); ?>
                        <input type="hidden" name="name_card" value="" id="name_card" class="name_card">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="" class="col-sm-12 control-label"><?= Translate::get('Ngày hết hạn') ?>
                        :</label>
                    <div class="col-sm-6 col-xs-6">
                        <?= $form->field($model, 'card_month')->dropDownList($model->getCardMonths(), [
                            'class' => 'form-control input-size card-expired-input',
                            'id'    => 'expMonth',
                        ])->label(false); ?>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <?= $form->field($model, 'card_year')->dropDownList($model->getExpiredCardYears(), [
                            'class' => 'form-control input-size card-expired-input',
                            'id'    => 'expYear',
                        ])->label(false); ?>
                    </div>
                </div>

                <div class="form-group" id="cvv-block">
                    <label for=""
                           class="col-xs-12 control-label"><?= Translate::get('Mã CVV/CVC2') ?>
                        :</label>
                    <div class="col-xs-12">
                        <?= $form->field($model, 'card_cvv')->input('password', [
                            'class'     => 'form-control input-numeric input-size',
                            'id'        => 'cvv2',
                            'maxlength' => 4,
                        ])->label(false); ?>
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
        <div class="col-md-6">
            <?php require_once(__DIR__ . '/../../../_card-number.php') ?>
        </div>
    </div>
</div>
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
<ul class="box-installment" style="display: flex">
    <li class="bi-title">
        <p><?= Translate::get('Chọn gói trả góp') ?></p>
        <p><?= Translate::get('Tổng tiền trả góp') ?> (<?= $model->merchant_fee_info['currency'] ?>)</p>
        <p><?= Translate::get('Mỗi tháng trả') ?> (<?= $model->merchant_fee_info['currency'] ?>)</p>
        <p><?= Translate::get('Chênh lệch giá gốc') ?> (<?= $model->merchant_fee_info['currency'] ?>)</p>
    </li>

    <?php
    if ($model->getBankCycleVer3($model->payment_method_code) != null) {
        foreach ($model->getBankCycleVer3($model->payment_method_code) as $item => $value) {
            $amount_order = $model["order"]['OrderDetails']['Amount'] ?? 0;
            $sender_flat_fee = $model['merchant_fee_info']['sender_flat_fee'] ?? 0;
            $sender_percent_fee = $model['merchant_fee_info']['sender_percent_fee'] ?? 0;
            $card_owner_percent_fee = $value['card_owner_percent_fee'];
            $card_owner_fixed_fee = $value['card_owner_fixed_fee'];

            $result = CheckoutOrder::getInstallmentFeeVer3(
                $amount_order,
                $sender_flat_fee,
                $sender_percent_fee,
                $card_owner_percent_fee,
                $card_owner_fixed_fee,
            );
            $total_amount[$value['method']][$value['period']] = ceil($result['amount_fee']);
            ?>
            <li class="bi-detail list-cycle list-cycle-<?= strtolower($value['method']); ?>"
                data-value="<?= $value['method'] ?>">
                <div class="middle">
                    <label>
                        <input id="<?= $value['method'] . ' ' . $value['period'] ?>" type="radio"
                               name="card_cycle" value="<?= $value['period'] ?>" class="cycle"
                               data-content="<?= $value['fee'] ?>"
                        >
                        <div class="box">
                            <p><strong><?= $value['period'] . ' ' . Translate::get('tháng') ?></strong></p>
                            <p id="amount-fee-cycle-<?= $value['period'] ?>-type-<?= $value['method'] ?>"><?= ObjInput::makeCurrency(ceil($result['amount_fee'])) ?></p>
                            <p><strong><?php
                                    if (intval($value['period']) > 0) {
                                        echo ObjInput::makeCurrency(ceil($result['amount_fee'] / $value['period']));
                                    } ?>
                                </strong></p>
                            <p><?= ObjInput::makeCurrency(ceil($result['amount_fee'] - $model["order"]['OrderDetails']['Amount'])) ?> </p>
                            <div class="triangle-topright"><i class="las la-check"></i></div>
                        </div>
                    </label>
                </div>
            </li>
        <?php }
    } ?>
</ul>
<?php
//var_dump($total_amount);die();
?>
<?= $form->field($model, 'cycle_installment')->hiddenInput([
    'class' => 'hidden',
    'id'    => 'cycle_installment',
    'value' => "",
])->label(false); ?>
<input id="card_fee" type="hidden" name="card_fee" value="" class="card_fee">
<input type="hidden" name="info_card" value="" class="info_card" id="info_card">
<input id="card_fee_bearer" type="hidden" name="card_fee_bearer"
       value="<?= $model->getFeeBearer($model->payment_method_code) != null ? $model->getFeeBearer($model->payment_method_code) : '' ?>"
       class="card_fee_bearer">
<input id="amount_fee" type="hidden" name="amount_fee" value="" class="amount_fee">

<?php
echo $form->field($model, 'merchant_id')->hiddenInput([
    'id'    => 'merchant_id',
    'value' => $checkout_order['merchant_id'],
])->label(false);
echo $form->field($model, 'card_info')->hiddenInput([
    'id'    => 'card_info',
    'value' => '',
])->label(false);
echo $form->field($model, 'card_type')->hiddenInput([
    'id'    => 'card_type',
    'value' => '',
])->label(false);
echo $form->field($model, 'token_code')->hiddenInput([
    'id'    => 'token_code',
    'value' => $checkout_order['token_code'],
])->label(false);
echo $form->field($model, 'payment_method_code')->hiddenInput([
    'id'    => 'payment_method_code',
    'value' => $model->info['code'],
])->label(false);

?>
<?php require_once(__DIR__ . '/../../../_footer-card.php') ?>

<?php ActiveForm::end() ?>
<iframe id="cardinal_collection_iframe" name="collectionIframe" height="10" width="10" style="display: none;"></iframe>
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
    let enable_sumbit = false;
    let type_card = '';
    let type_card_id = '';
    let cycle = '';
    let amount_fee;
    let card_type = {
        'visa': '001',
        'jcb': '007',
        'mastercard': '002',
        'amex': '003'
    }; // new
    let submit_cycle_value;
    let card_type_code;
    const bankCycleArr = <?= json_encode($model->getBankCycleVer2($model->payment_method_code))?>;

    const payment_method_code = $("#payment_method_code").val();
    // let type_card = payment_method_code.split("-")[0];
    let check_expired = false;

    $(".list-cycle").hide();
    $(function () {
        const creditCardMask = document.querySelector('.credit-card-mask');
        if (creditCardMask) {
            new Cleave(creditCardMask, {
                creditCard: true,
                onCreditCardTypeChanged: function (type) {
                    console.log('change avt');
                    console.log('type:' + type);
                    card_type_code = type;
                    $('#card_type').val(type);
                    $('#card_info').val(card_type[type])
                    type_card = card_type[type];
                    console.log($('#card_type'));
                    console.log('type: ', type)
                    console.log('check type card', type_card)
                    showCycle(type);
                    var img = $('div.box-card.box-card-custom > img');
                    if (img) {
                        console.log('set anh');
                        img.attr('src', '<?php Yii::$app->request->baseUrl ?>' + 'dist/images/credit-payments/' + type + '-cc.png')
                    }
                }
            });
        } else {
            console.log(444);
        }
    })

    function getCycle(fee, cycle, card_type) {
        $('#cycle_installment').val(cycle);
        console.log('cycle_installment', $('#cycle_installment').val());
        $('#form-card').css('display', 'block');
        //số tiền nm phải chịu
        card_fee_bearer = $('#card_fee_bearer').val();
        $('#card_fee').val(fee);

        //===== CAL FEE OLD
        let partner_payment_fee = <?= $model['merchant_fee_info']['sender_flat_fee']??$model['checkout_order']['merchant_fee_info']['sender_flat_fee'] ?>;
        let amount_order = <?= $model["order"]['OrderDetails']['Amount'] ?>;
        //if (card_fee_bearer == 3) {
        //    amount_fee = amount_order + (amount_order * ((fee / 2) / 100)) + partner_payment_fee;
        //} else if (card_fee_bearer == 2) {
        //    amount_fee = amount_order + (amount_order * fee) / 100 + partner_payment_fee;
        //} else if (card_fee_bearer == 1) {
        //    amount_fee = amount_order + partner_payment_fee;
        //}
        //document.getElementById('amount-fee-cycle-' + cycle + '-type-' + card_type).value = amount_fee;
    }

    function showCycle(sel, type_card) {
        $(".list-cycle").hide();
        console.log('.list-cycle-' + sel);
        $(".list-cycle-" + sel).show();
        console.log('showCyle' + sel);
        $('#form-cycle').css('display', 'block')
        //document.getElementById('cycle_installment').value = '';
    }

    const card_name = $('#card_name');
    const card_first_name = $('#card_first_name');
    const card_last_name = $('#card_last_name');
    const card_number = $('#card_number');
    const cvv = $('#cvv2');
    const exp_month = $('#expMonth');
    const exp_year = $('#expYear');
    const cycle_installment = $('#cycle_installment');
    // const check_term = $('#accept-terms');
    card_number.change(function () {
        //getInstallmentPackages()
        checkChange()
    });
    cvv.change(function () {
        checkChange()
    });
    card_first_name.change(function () {
        checkChange()
    });
    card_last_name.change(function () {
        checkChange()
    });
    exp_year.change(function () {
        validateExpirationDate();

        checkChange()
    });
    exp_month.change(function () {
        validateExpirationDate();

        checkChange()
    });
    // check_term.change(function () {
    //     checkChange()
    // });
    $('#accept-terms').change(function () {
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

    $('input[name="card_cycle"]').change(function () {
        console.log('change_card_cycle');
        checkChange()
    });

    $('.list-cycle').click(function () {
    // $('input[name="card_cycle"]').change(function () {
        //=== XỬ LÝ KHI CHỌN KỲ HẠN
        console.log('change_cycle_installment_1');
        $(this).addClass('clicked');
        submit_cycle_value = $(this).find('input.cycle').val();
        submit_card_fee = $(this).find('input.cycle').attr('data-content'); // neu phi la 2% thi card_fee = 2!!!
        // console.log(submit_card_fee);return
        let local_card_type = $(this).attr('data-value');
        $("#cycle_installment").val(submit_cycle_value);

        let total_amount_js = <?= json_encode($total_amount) ?>;

        console.log('total_amount_js', total_amount_js);
        let submit_amount_fee = total_amount_js[local_card_type][submit_cycle_value];
        $("#amount_fee").val(submit_amount_fee);
        $("#total_amount_display").html(formatCurrency(submit_amount_fee));
        $("#amount_order_total").empty();
        $("#amount_order_total").html(formatCurrency(submit_amount_fee) + ' <?= $checkout_order['currency']?>');
        $("#amount_total_cl").empty();
        $("#amount_total_cl").html(formatCurrency(submit_amount_fee) + ' <?= $checkout_order['currency']?>');

        let checkout_order_amount = '<?= $checkout_order['amount'] ?>';
        let payment_sender_amount = '<?= $checkout_order['sender_fee'] ?>';
        let pay_install_fee = submit_amount_fee - checkout_order_amount;
        $("#fee_cl").html(formatCurrency(pay_install_fee) + ' <?= $checkout_order['currency']?>');

        //TODO lay fee
        $('#card_fee').val(submit_card_fee);
        // checkChange()
    })
    cycle_installment.change(function () {
        console.log('change_cycle_installment');
        checkChange()
    });
    $(document).ready(function () {
        $('#pay-button').attr('disabled', 'disabled');
        $('#card_name').change(function () {
            document.getElementById('name_card').value = $('#card_name').val();
        })
        $('#card_number').change(function () {
            isExistCycleBank();

            var pan = document.getElementById('card_number').value
            getPartnerPayment();
            Cardinal.trigger('accountNumber.update', pan)
                .then(function (results) {
                    console.log('RESULTS CARDINAL TRIGGER 1 ', results)
                    writeLog(results, $('#OrderNumber').val());
                })
        });


    })

    function isExistCycleBank() {
        // console.log( 'CARD TYPE CODE', type.toUpperCase());
        // console.log( bankCycleArr);
        // console.log(typeof bankCycleArr);
        if(card_type_code){
            let existsCycleBank = bankCycleArr.some(item => item.card_type === card_type_code.toUpperCase());
            console.log('existsCycleBank: ', existsCycleBank);  // true nếu tồn tại, false nếu không
            if(!existsCycleBank){
                alert('Loại thẻ của bạn chưa được hỗ trợ trả góp theo phương thức hiện tại. Vui lòng chọn thẻ khác để thanh toán');
                location.reload();

            }
        }

    }

    function getInstallmentPackages() {
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_3_0/get-installment-packages' ?>',
            type: 'post',
            dataType: 'json',
            data: {
                card_number: $('#card_number').val(),
                card_info: card_type_code,
                payment_method_info_config: <?=$model->info['config']?>,
            },
            success: function (res) {
                console.log(res);
                if (res.error == true) {
                    alert(res.message);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr)
            }
        });
    }


    function checkChange() {
        console.log("submit_cycle_value ", submit_cycle_value);
        /**
         // DEBUG
         console.log("card_number not empty: ", card_number.val() !== "");
         console.log("exp_month not empty: ", exp_month.val() !== "");
         console.log("exp_month not null: ", exp_month.val() != null);
         console.log("exp_year not empty: ", exp_year.val() !== "");
         console.log("exp_year not null: ", exp_year.val() != null);
         console.log("card_first_name not empty: ", card_first_name.val() !== "");
         console.log("card_last_name not empty: ", card_last_name.val() !== "");
         console.log("cvv not empty: ", cvv.val() !== "");
         console.log("list cycle is clicked: ", $('.list-cycle').hasClass('clicked') === true);
         console.log("check_term is checked: ", check_term.prop('checked') === true);
         console.log('==============================================')
         */

        if (card_number.val() !== "" && exp_month.val() !== "" && exp_month.val() != null && exp_year.val() !== "" && exp_year.val() != null
            && card_first_name.val() !== "" && card_last_name.val() !== "" && cvv.val() !== ""
            && $('.list-cycle').hasClass('clicked') === true
            && $("#cycle_installment").val() !== ''
            && check_expired === false
            // && check_term.prop('checked') === true
        ) {

            <?php if(in_array($checkout_order["merchant_id"], array_merge($GLOBALS['MERCHANT_BCA'], $GLOBALS['MERCHANT_XNC']))): ?>
            if (!$('#accept-terms').is(':checked')) {
                // Nếu MC BCA A08 mà chưa click vào checkbox thì BÁO LỖI
                $('#accept-terms').addClass('is-invalid');
                // Focus vào checkbox
                $('#accept-terms').focus();
                return false;
            }
            <?php endif; ?>

            // let card_number = $('#card_number').val();
            // let card_name = $('#card_name').val();
            console.log('TYPE CARD INFO', type_card)
            let customer_info = {
                card_number: card_number.val(),
                expiration_month: exp_month.val(),
                expiration_year: exp_year.val(),
                name_on_account: card_name.val(),
                card_code: cvv.val(),
                card_type: type_card,
            }
            let token_code = $("#token_code").val();
            $('#cover-spin').show();
            _setup(customer_info, token_code, payment_method_code)
        }
    }

    function checkEnroll(custommer_info, token_code, payment_method_code) {
        console.log('CALL CHECK-ENROLL');
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
                console.log('debug c1');

                $('#cover-spin').hide(0);
                if (res.status) {
                    console.log('debug c2');

                    if (res.valid) {
                        console.log('debug c3');

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
                            console.log('debug c4');

                            let {PAResStatus} = data.Payment.ExtendedData;
                            let {ECIFlag} = data.Payment.ExtendedData;
                            writeLog(data, $('#OrderNumber').val());
                            console.log("Validate Doneeeee")
                            if (["N", "R", "U"].includes(PAResStatus) && (ECIFlag === undefined || ['00', '07'].includes(ECIFlag))) {
                                //TODO Đang ko test đc vào đây!!!
                                console.log('debug c5');

                                if (false && $("#merchant_id").val() == "91") {
                                    console.log('debug c5');

                                    enable_sumbit = true;
                                    $('#ProcessorTransactionId').val(processTrans);
                                    $("#form-checkout").submit();
                                } else {
                                    console.log('debug c6');

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
                                console.log('debug c11');

                                const ErrorDescription = data.ErrorDescription;
                                if (ErrorDescription === 'Success') { // Buyer enrolled in 3DS and successfully authenticated
                                    console.log('debug c12');

                                    console.log("Type Fas" + $("#card_type").val())

                                    $('#ProcessorTransactionId').val(data.Payment.ProcessorTransactionId);
                                    $('#jwt_back').val(jwt);
                                    writeLog(data, $('#OrderNumber').val());
                                    enable_sumbit = true;

                                    // console.log("Type Fas" . $("#card_type").val())
                                    $("#form-checkout").submit();
                                } else {
                                    console.log('debug c12a');

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
                                            console.log('debug c14');

                                            if (!res.status && res.redirect !== undefined) {
                                                window.location.href = res.redirect
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        console.log('debug c15');

                        $('#ProcessorTransactionId').val(res.auth_info.authenticationTransactionID);
                        enable_sumbit = true;
                        $("#form-checkout").submit();
                    }
                } else if (res.redirect) {
                    console.log('debug c10');

                    window.location.href = res.redirect
                } else {
                    console.log('debug c19');

                    alert(res.error_message);
                    window.history.back()
                }

            }
        });
    }


    function _setup(custommer_info, token_code, payment_method_code) {
        console.log('CALL SETUP');
        $.ajax({
            url: '<?php echo Yii::$app->request->baseUrl . '/version_1_0/setup-author' ?>',
            // async: false,
            type: 'post',
            dataType: 'json',
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
                    console.log('debug 1')
                    document.getElementById('cardinal_collection_form').submit()
                    console.log('debug 2')

                    sessionID = res.auth_info.referenceID

                    Cardinal.on('payments.setupComplete', function (setupCompleteData) {
                        console.log(setupCompleteData);
                        cardinalSetupDone = true;
                        if (res.time_process < 3) {
                            setTimeout(function () {
                                console.log(54);
                                $('#pay-button').removeAttr('disabled');
                            }, 3000 - res.time_process)
                        } else {
                            $('#pay-button').removeAttr('disabled');
                        }
                        // if (documentReady) {
                        //     $('#pay-button').removeAttr('disabled');
                        // }
                    });

                    console.log('debug 3')
                    // $('#pay-button').click(function () {
                    //     console.log('submit');
                    //     $('#form-checkout').submit();
                    // })

                    $("#form-checkout").on('beforeSubmit', function (e) {
                        if (!enable_sumbit) {
                            $('#cover-spin').show(0);
                            $(".btn").attr("disabled", "disabled")
                            let card_number_val = $('#card_number').val();
                            card_number_val = card_number_val.split(" ").join("");
                            console.log('type_card=====', type_card);
                            let customer_info = {
                                card_number: card_number_val,
                                expiration_month: $('#expMonth').val(),
                                expiration_year: $('#expYear').val(),
                                name_on_account: $('#card_name').val(),
                                card_code: $('#cvv2').val(),
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
                } else if (res.redirect) {
                    alert(res.error_message);
                    window.location.href = res.redirect
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

    function getPartnerPayment() {
        // var bank = $('.bank').val();
        var bank = '<?= $model->config['class'] ?>';
        var card_number = $('#card_number').val();
        var card_type_text = $('#card_type').val();

        var type_card = card_type[card_type_text];

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
    function checkDigit(event) {

        var code = (event.which) ? event.which : event.keyCode;
        console.log(code);
        if ((code < 48 || code > 57) && (code > 31)) {
            return false;
        }
        return true;
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
</script>
