if (typeof alepayRequest !== 'undefined') {
    $.LoadingOverlaySetup({
        image: alepayRequest.assetUrl + 'images/loading.gif',
    });
}

function showMessageModal(message) {
    $('#message-modal .message-text').html(message);
    $('#message-modal').modal('show');
}

function toggleSubmitButton(btnSubmit, disable) {
    btnSubmit.attr('disabled', disable);
    btnSubmit.toggleClass('disabled');
    btnSubmit.find('.las').toggleClass('d-none');
    btnSubmit.find('.spinner-border').toggleClass('d-none');
}

function formatCurrency(value) {
    if (value && value > 0) {
        value = value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        return value;
    }
    return 0;
}

function checkTerm() {
    if ($('#text-rule').is(':visible')) {
        if ($('#check-terms').length) {
            if ($('#check-terms').prop('checked') == false) {
                $('.text-agree').fadeIn();
                return false;
            } else {
                $('.text-agree').fadeOut();
            }
        }
    }

    if ($('#footer-mobile').is(':visible')) {
        if ($('#agree-mobile').length) {
            if ($('#agree-mobile').prop('checked') == false) {
                showMessageModal(alepayRequest.agreeText);
                return false;
            }
        }
    }

    return true;
}

function checkExpireDate(expiredInput) {
    var monthYear = getCurrentMonthYear();
    var currentMonth = monthYear[0];
    var currentYear = monthYear[1];
    var arrExpired = expiredInput.split('/');

    if (arrExpired[1] < currentYear) {
        $('.date-hint').html(alepayRequest.expiredInvalidYear);
        return false;
    } else if (arrExpired[1] == currentYear) {
        if (arrExpired[0] < currentMonth) {
            $('.date-hint').html(alepayRequest.expiredInvalidMonth);
            return false;
        }
    }

    return true;
}

function getCurrentMonthYear() {
    var currentMonth = (new Date).getMonth() + 1;
    var currentYear = (new Date).getFullYear();
    currentYear = currentYear.toString().substring(2);

    return new Array(currentMonth, currentYear);
}

$(document).ready(function () {
    console.log('checkout.js');
    var cardType;

    function showMessagOTPModal(message) {
        $('#message-modal1 .message-text').html(message);
        $('#message-modal1').modal('show');
    }

    function getFeeCardNumber(cardNumber) {
        $.ajax({
            url: alepayRequest.baseUrl + 'card/calculate-fee/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf, cardNumber: cardNumber
            }, dataType: "json", success: function (response) {
                var token_url = $('#token_url').val();

                if (response.status == 'success') {
                    $('#card-form').find('button[type="submit"]').prop('disabled', 0);

                    if (response.priceAmount) {
                        $('.order-price-amount').html(response.priceAmount);
                    }

                    if (response.totalFee) {
                        $('.order-total-fee').html(response.totalFee);
                    }

                    if (response.totalAmount) {
                        $('.order-total-amount').html(response.totalAmount);
                    }

                    if (response.data.pgUserCode) {
                        $('#key_cyber').val(response.data.pgUserCode);
                        $('#card-form').find('button[type="submit"]').prop('disabled', 1);

                        $.ajax({
                            url: token_url, type: 'post', data: {
                                _csrf: alepayRequest.csrf,
                                key_cyber: response.data.pgUserCode,
                                jti: alepayRequest.csrf + '' + cardNumber,
                                amount: response.data.amountFinal
                            }, dataType: "json", beforeSend: function () {
                                $.LoadingOverlay("show");
                            }, success: function (responseToken) {
                                if (responseToken) {
                                    if (responseToken.error_code == '00') {
                                        $('#jwtcontainer').val(responseToken.data);
                                        Cardinal.configure({
                                            logging: {
                                                level: "off"
                                            }
                                        });

                                        var sessionId;
                                        // Step 4.  Listen for Events
                                        Cardinal.on('payments.setupComplete', function (data) {
                                            $.LoadingOverlay("hide");

                                            if (data.sessionId) {
                                                $('#sessionId').val(data.sessionId);
                                                $('#card-form').find('button[type="submit"]').prop('disabled', 0);
                                            }
                                        });

                                        Cardinal.setup("init", {
                                            jwt: $('#jwtcontainer').val()
                                        });
                                    } else {
                                        $.LoadingOverlay("hide");
                                        showMessageModal(alepayRequest.jwtFail);
                                    }
                                } else {
                                    $.LoadingOverlay("hide");
                                    showMessageModal(alepayRequest.jwtFail);
                                }
                            }
                        });
                    }

                    if (response.data.type == 'INTERNATIONAL') {
                        $('.zipcodeRow').fadeIn();
                    }
                } else {
                    $('.field-cardrequest-cardnumber').find('.card-hint').html(response.message);
                    $('#card-form').find('button[type="submit"]').prop('disabled', 1);
                }
            }
        });
    }

    if ($('#cardrequest-country').length) {
        $('#cardrequest-country').select2();
    }

    $('body').on('click', '.icon-toggle-show', function () {
        $(".tab-all").show('slow');
        $(".icon-toggle-show").hide('slow');
        $(".icon-toggle-hide").show('slow');
    });

    $('body').on('click', '.icon-toggle-hide', function () {
        $(".tab-all").hide('slow');
        $(".icon-toggle-show").show('slow');
        $(".icon-toggle-hide").hide('slow');
    });

    $(".search-bank").on("keyup", function () {
        var body = $(this).closest('.card-body');
        var search = $(this).val();

        if (search.length > 0) {
            body.find(".bank-item").hide();
            $("#" + body.attr('id') + " [data-name*='" + search + "' i]").show();
        } else {
            body.find(".bank-item").show();
        }
    });

    if ($('.countdown-expired').length) {
        $('.countdown-expired').countdown(alepayRequest.expiredTime, function (event) {
            $(this).html(event.strftime('%I:%M:%S'));
        }).on('finish.countdown', function (event) {
            if (event.elapsed) {
                $(this).html(alepayRequest.expiredText);
            }
        });
    }

    if ($('.request-form .card-number').length) {
        var id = $('.request-form').find('.card-number').eq(0).prop('id');

        var visaCardNumber = new Cleave('.card-number', {
            creditCard: true, onCreditCardTypeChanged: function (type) {
                var icon = '';
                cardType = type;

                if (type !== 'unknown') {
                    if (type == 'amex' || type == 'visa' || type == 'jcb' || type == 'mastercard') {
                        icon = '<img src="' + alepayRequest.assetUrl + 'images/banks/' + type + '.png">';
                        document.querySelector('.icon-card').innerHTML = icon;

                        var logoBank = document.querySelector('.box-logobank');
                        logoBank.setAttribute('src', alepayRequest.assetUrl + 'images/banks/' + type + '.png');

                        if (type == 'amex') {
                            logoBank.setAttribute('style', 'left: 10px;');
                        } else {
                            logoBank.setAttribute('style', 'left: 0;');
                        }
                    }
                }
            }, onValueChanged: function (e) {


                if (id == 'installmentrequest-cardnumber') {
                    if (cardType == 'amex' || cardType == 'uatp') {
                        $('#' + id).attr('maxlength', 17);

                        if (e.target.rawValue.length == 15) {
                            getPeriods(e.target.rawValue);
                        } else {
                            $('.installment-hint').html(alepayRequest.cardNumberRequired);
                        }
                    } else {
                        $('#' + id).attr('maxlength', 19);

                        if (e.target.rawValue.length == 16) {
                            getPeriods(e.target.rawValue);
                        } else {
                            $('.installment-hint').html(alepayRequest.cardNumberRequired);
                        }
                    }
                }

                if (id == 'installmentrequest2-cardnumber') {
                    if (cardType == 'amex' || cardType == 'uatp') {
                        $('#' + id).attr('maxlength', 17);

                        if (e.target.rawValue.length == 15) {
                            validateInstallmentCard(e.target.rawValue);
                        } else {
                            $('.installment-hint').html(alepayRequest.cardNumberRequired);
                        }
                    } else {
                        $('#' + id).attr('maxlength', 19);

                        if (e.target.rawValue.length == 16) {
                            validateInstallmentCard(e.target.rawValue);
                        } else {
                            $('.installment-hint').html(alepayRequest.cardNumberRequired);
                        }
                    }
                }

                if (id == 'cardrequest-cardnumber') {
                    if (cardType == 'amex' || cardType == 'uatp') {
                        $('#' + id).attr('maxlength', 17);

                        if (e.target.rawValue.length == 15) {
                            $('.card-hint').html('');
                            getFeeCardNumber(e.target.rawValue);
                        } else {
                            $('.card-hint').html(alepayRequest.cardNumberRequired);
                        }
                    } else {
                        $('#' + id).attr('maxlength', 19);

                        if (e.target.rawValue.length == 16) {
                            $('.card-hint').html('');
                            getFeeCardNumber(e.target.rawValue);
                        } else {
                            $('.card-hint').html(alepayRequest.cardNumberRequired);
                        }
                    }
                }
            }
        });

        $('.request-form .card-number').on("keyup", function (e) {
            var str = $(this).val();
            $('.card-number-val').text(str.substring(0, 7) + "xx xxxx " + str.substring(str.length - 4));
        });
    }

    if ($('.request-form .atm-card-number').length) {
        var atmCardNumber = new Cleave('.atm-card-number', {
            blocks: [4, 4, 4, 7],
        });

        $('.request-form .atm-card-number').on("keyup", function (e) {
            var str = $(this).val();

            if (str.length == 22) {
                $('.card-number-val').text(str.substring(0, 7) + "xx xxxx xxx" + str.substring(str.length - 4));
            } else {
                $('.card-number-val').text(str.substring(0, 7) + "xx xxxx " + str.substring(str.length - 4));
            }
        });
    }


    // TinBT
    if ($('.form-request .card-number-input').length) {
        console.log(123);
        $(this).on("keyup", function (e) {
            console.log(456);
            return;
            var str = $(this).val();

            if (str.length == 22) {
                $('.card-number-val').text(str.substring(0, 7) + "xx xxxx xxx" + str.substring(str.length - 4));
            } else {
                $('.card-number-val').text(str.substring(0, 7) + "xx xxxx " + str.substring(str.length - 4));
            }
        });
    }

    if ($('.request-form .card-date').length) {
        var cardDate = new Cleave('.card-date', {
            date: true, delimiter: '/', datePattern: ['m', 'y'], onValueChanged: function (e) {
                var monthYear = getCurrentMonthYear();
                var currentMonth = monthYear[0];
                var currentYear = monthYear[1];

                if (e.target.name == "Atm_onRequest[releaseCard]") {
                    if (e.target.rawValue.length > 0 && e.target.rawValue.length < 4) {
                        $('.date-hint').html(alepayRequest.releaseDateRequired);
                    } else {
                        $('.date-hint').html('');
                        var arrRelease = e.target.value.split('/');

                        if (arrRelease[1] > currentYear) {
                            $('.date-hint').html(alepayRequest.releaseInvalidYear);
                        } else if (arrRelease[1] == currentYear) {
                            if (arrRelease[0] > currentMonth) {
                                $('.date-hint').html(alepayRequest.releaseInvalidMonth);
                            }
                        }
                    }
                } else {
                    if (e.target.rawValue.length > 0 && e.target.rawValue.length < 4) {
                        $('.date-hint').html(alepayRequest.cardDateRequired);
                    } else {
                        $('.date-hint').html('');
                        var arrExpired = e.target.value.split('/');

                        if (arrExpired[1] < currentYear) {
                            $('.date-hint').html(alepayRequest.expiredInvalidYear);
                        } else if (arrExpired[1] == currentYear) {
                            if (arrExpired[0] < currentMonth) {
                                $('.date-hint').html(alepayRequest.expiredInvalidMonth);
                            }
                        }
                    }
                }
            }
        });

        $('.request-form .card-date').on("keyup", function (e) {
            $('.card-date-val').text($(this).val());
        });
    }

    if ($('.request-form .card-cvc').length) {
        $('.request-form .card-cvc').on("keyup", function (e) {
            if ($(this).val().length > 0 && $(this).val().length < 3) {
                $('.cvc-hint').html(alepayRequest.cvcRequired);
            } else {
                $('.cvc-hint').html('');
            }
        });
    }

    $('.request-form input[type="text"]').on("keyup", function (e) {
        $(this).closest('.form-group').find('.help-block').html('');
    });

    $('.request-form input[type="password"]').on("keyup", function (e) {
        $(this).closest('.form-group').find('.help-block').html('');
    });

    $('.request-form .card-name').on("keyup", function (e) {
        $('.card-name-val').text($(this).val().toUpperCase());
    });

    $(document).on('click', '.box-installment .bi-detail', function (e) {
        var $this = $(this);
        var month = $this.find('input[type="radio"]').val();

        $('#installmentrequest-periods').val(month);
        $('.order-total-amount').html($this.find('.installment-final').html());
        $('.order-total-fee').html($this.find('.installment-fee').html());
    });

    $('body').on('click', '#installmentTab a[data-toggle="tab"]', function () {
        var month = $(this).data('month');
        $('#installmentrequest-periods').val(month);
        $('.order-total-amount').html($('#tab' + month).find('.installment-final').html());
        $('.order-total-fee').html($('#tab' + month).find('.installment-fee').html());
    });

    $('#card-form').on("beforeSubmit", function () {
        var form = $(this);
        var btnSubmit = form.find('[type=submit]');

        if (!checkExpireDate(form.find('.card-date').val())) {
            return false;
        }

        if (!checkTerm()) {
            return false;
        }

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json', beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            }, success: function (response) {

                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton(btnSubmit, false);
                } else if (response.status == 'is3D') {
                    if (response.is_mc_cbs_2 == true) {
                        var continueData = {
                            AcsUrl: response.data.termUrl, Payload: response.data.paReq, challengeWindowSize: 5
                        }
                        var orderObjectV2 = {
                            OrderDetails: {
                                TransactionId: response.data.authenticationTransactionID
                            }
                        }


                        var pan = document.getElementById('cardrequest-cardnumber').value
                        Cardinal.trigger('bin.process', pan).then(function (results) {
                            Cardinal.continue('cca', continueData, orderObjectV2);

                        }).catch(function (error) {
                            showMessageModal(error);
                            toggleSubmitButton(btnSubmit, false);
                        });
                        Cardinal.on("payments.validated", function (data, jwt) {
                            var errorDescription = 'There was an error in processing';
                            if (data.ErrorDescription == 'Success') {
                                var form3d = $('#form3d');
                                form3d.attr('action', response.linkCallback3D);
                                form3d.find('input[name="PaReq"]').val(response.data.paReq);
                                form3d.find('input[name="TransactionId"]').val(data.Payment.ProcessorTransactionId);
                                form3d.submit();
                            } else {
                                if (response.lang == 'vi') {
                                    errorDescription = 'Có lỗi trong quá trình xử lý'
                                }
                                showMessageModal(errorDescription);
                                toggleSubmitButton(btnSubmit, false);
                            }
                        });
                    } else {
                        var form3d = $('#form3d');
                        form3d.attr('action', response.data.termUrl);
                        form3d.find('input[name="PaReq"]').val(response.data.paReq);
                        form3d.find('input[name="MD"]').val(response.data.md);
                        form3d.find('input[name="TermUrl"]').val(response.linkCallback3D);
                        form3d.submit();
                    }


                    // Step 12. Validation Service


                } else if (response.status == 'review') {
                    window.location.href = response.reviewUrl;
                } else if (response.status == 'is2D') {
                    window.location.href = response.card2dUrl;
                } else if (response.status == 'migs') {
                    window.location.href = response.migsUrl;
                }
            }
        });

        return false; // prevent default submit
    });

    $('#installment-form').on("beforeSubmit", function () {
        var form = $(this);
        var btnSubmit = form.find('[type=submit]');

        if (!checkExpireDate(form.find('.card-date').val())) {
            return false;
        }

        if (!checkTerm()) {
            return false;
        }

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json', beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton(btnSubmit, false);
                } else if (response.status == 'is3D') {
                    if (response.is_mc_cbs_2 == true) {
                        var continueData = {
                            AcsUrl: response.data.termUrl, Payload: response.data.paReq, challengeWindowSize: 5
                        }
                        var orderObjectV2 = {
                            OrderDetails: {
                                TransactionId: response.data.authenticationTransactionID
                            }
                        }


                        var type = $('#installment-form').attr('data-type');
                        if (type == 1) {
                            pan = document.getElementById('installmentrequest2-cardnumber').value;
                        } else {
                            pan = document.getElementById('installmentrequest-cardnumber').value;

                        }
                        Cardinal.trigger('bin.process', pan).then(function (results) {
                            Cardinal.continue('cca', continueData, orderObjectV2);

                        }).catch(function (error) {
                            showMessageModal(error);
                            toggleSubmitButton(btnSubmit, false);
                        });
                        Cardinal.on("payments.validated", function (data, jwt) {

                            if (data.ErrorDescription == 'Success') {


                                var form3d = $('#form3d');
                                form3d.attr('action', response.linkCallback3D);
                                form3d.find('input[name="PaReq"]').val(response.data.paReq);
                                form3d.find('input[name="TransactionId"]').val(data.Payment.ProcessorTransactionId);
                                form3d.submit();
                            } else {
                                showMessageModal(data.ErrorDescription);
                                toggleSubmitButton(btnSubmit, false);
                            }
                        });
                    } else {
                        var form3d = $('#form3d');
                        form3d.attr('action', response.data.termUrl);
                        form3d.find('input[name="PaReq"]').val(response.data.paReq);
                        form3d.find('input[name="MD"]').val(response.data.md);
                        form3d.find('input[name="TermUrl"]').val(response.linkCallback3D);
                        form3d.submit();
                    }
                } else if (response.status == 'review') {
                    window.location.href = response.reviewUrl;
                } else if (response.status == 'is2D') {
                    window.location.href = response.card2dUrl;
                }
            }
        });

        return false; // prevent default submit
    });

    $('#atm_on-form').on("beforeSubmit", function () {
        var form = $(this);
        var btnSubmit = form.find('[type=submit]');
        var bankName = $('#atm_onrequest-bankname');
        var bankAccount = $('#atm_onrequest-bankaccount');
        var releaseCard = $('#atm_onrequest-releasecard');
        var expiredCard = $('#atm_onrequest-expiredcard');
        var hasError;
        var monthYear = getCurrentMonthYear();
        var currentMonth = monthYear[0];
        var currentYear = monthYear[1];

        if (bankName.length) {
            if (!bankName.val()) {
                bankName.closest('.form-group').find('.help-block').html(alepayRequest.bankNameRequired);
                hasError = true;
            }
        }

        if (bankAccount.length) {
            if (!bankAccount.val()) {
                bankAccount.closest('.form-group').find('.help-block').html(alepayRequest.bankAccountRequired);
                hasError = true;
            }
        }

        if (releaseCard.length) {
            if (!releaseCard.val()) {
                releaseCard.closest('.form-group').find('.help-block').html(alepayRequest.releaseCardRequired);
                hasError = true;
            } else {
                var arrRelease = releaseCard.val().split('/');

                if (arrRelease[1] > currentYear) {
                    $('.date-hint').html('');
                    releaseCard.closest('.form-group').find('.help-block').html(alepayRequest.releaseInvalidYear);
                    hasError = true;
                } else if (arrRelease[1] == currentYear) {
                    if (arrRelease[0] > currentMonth) {
                        $('.date-hint').html('');
                        releaseCard.closest('.form-group').find('.help-block').html(alepayRequest.releaseInvalidMonth);
                        hasError = true;
                    }
                }
            }
        }

        if (expiredCard.length) {
            if (!expiredCard.val()) {
                expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.expiredCardRequired);
                hasError = true;
            } else {
                var arrExpired = expiredCard.val().split('/');

                if (arrExpired[1] < currentYear) {
                    $('.date-hint').html('');
                    expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.expiredInvalidYear);
                    hasError = true;
                } else if (arrExpired[1] == currentYear) {
                    if (arrExpired[0] < currentMonth) {
                        $('.date-hint').html('');
                        expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.expiredInvalidMonth);
                        hasError = true;
                    }
                }
            }
        }

        if (hasError) {
            return false;
        }

        if (!checkTerm()) {
            return false;
        }

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json', beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton(btnSubmit, false);
                    $('#re-select').css('display', 'none');
                } else if (response.status == 'success') {
                    if (response.data.errorCode == '00') {
                        if (response.data.authSite == 'BANK') {
                            if (response.data.authUrl) {
                                window.location.href = response.data.authUrl;
                            }
                        } else if (response.data.authSite == 'NL' || response.data.authSite == 'ONEPAY') {
                            $('#auth-url').val(response.data.authUrl);
                            $('#pay-otp').modal('show');
                        }
                    } else {
                        if (response.redirectUrl) {
                            $('#message-modal .btn-malert').attr('href', response.redirectUrl).removeAttr('data-dismiss');
                        }

                        showMessageModal(response.errorMessage);
                        toggleSubmitButton(btnSubmit, false);
                    }
                }
            }
        });

        return false; // prevent default submit
    });

    $('#btn-otp-accept').click(function (e) {
        e.preventDefault();
        var otp = $('#otp-input').val();
        var authUrl = $('#auth-url').val();
        var $this = $(this);

        if (!otp) {
            $('#otp-help-block').html(alepayRequest.otpText);
        } else {
            $.ajax({
                url: alepayRequest.baseUrl + 'atm/submit-otp-atm/' + alepayRequest.token, type: 'post', data: {
                    _csrf: alepayRequest.csrf, otp: otp, authUrl: authUrl, bankCode: alepayRequest.bankCode,
                }, dataType: "json", beforeSend: function () {
                    toggleSubmitButton($this, true);
                }, success: function (response) {
                    if (response.status == 'fail') {
                        $('#pay-otp').modal('hide');
                        showMessagOTPModal(response.message);
                        // toggleSubmitButton($this, false);
                    } else if (response.status == 'success') {
                        $('#pay-otp').modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').css('padding', 0);
                        $('#app-body').html(response.html);
                        $('#app-body').trigger('change');
                    }
                }
            });
        }
    });

    $('.btn-cancel-order').click(function (e) {
        e.preventDefault();
        var $this = $(this);

        $.ajax({
            url: alepayRequest.baseUrl + 'cancel-request/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf
            }, dataType: "json", beforeSend: function () {
                toggleSubmitButton($this, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton($this, false);
                } else if (response.status == 'success') {
                    $('#message-modal .btn-malert').attr('href', response.returnUrl).removeAttr('data-dismiss');
                    $('#pay-cancel').modal('hide');
                    showMessageModal(response.message);
                }
            }
        });
    });

    $('#installmentrequest-cardnumber').focusout(function (e) {
        if (!$(this).val()) {
            $('.installment-hint').html('');
        }
    });

    $('#cardrequest-cardnumber').focusout(function (e) {
        if (!$(this).val()) {
            $('.card-hint').html('');
        }
    });

    $('.bank-item').click(function (e) {
        var cardBody = $(this).closest('.card-body');
        $('.card-header').css('background', 'white');
        cardBody.css('background', 'white');
        cardBody.find('.payment-loading').removeClass('d-none');
        cardBody.find('ul').addClass('d-none');
    });

    function getPeriods(cardNumber) {
        $.ajax({
            url: alepayRequest.baseUrl + 'installment/get-periods/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf, cardNumber: cardNumber, bankCode: alepayRequest.bankCode,
            }, dataType: "json", beforeSend: function () {
                $('.request-loading').fadeIn();
            }, success: function (response) {
                $('.request-loading').fadeOut();
                $('.installment-wr').html(response.html).fadeIn();
                $('.field-installmentrequest-cardnumber').find('.help-block').html('');

                if (response.status == 'success') {
                    $('.field-installmentrequest-cardnumber').find('.installment-hint').html('');
                    $('#installment-form').find('button[type="submit"]').prop('disabled', 0);

                    if (response.month) {
                        $('#installmentrequest-periods').val(response.month);
                    }

                    if (response.firstAmountFinal) {
                        $('.order-total-amount').html(response.firstAmountFinal);
                    }

                    if (response.firstAmountFee) {
                        $('.order-total-fee').html(response.firstAmountFee);
                    }
                    if (response.jwtToken) {
                        $('.jwtcontainer').val(response.jwtToken);
                    }
                    if (response.pgUserCode) {
                        $('#key_cyber').val(response.pgUserCode);
                        $('#installment-form').find('button[type="submit"]').attr('disabled', 0);

                        Cardinal.configure({
                            logging: {
                                level: "off"
                            }
                        });
                        var sessionId;
                        // Step 4.  Listen for Events
                        Cardinal.on('payments.setupComplete', function (data) {
                            sessionId = data.sessionId
                            $('#sessionInstallmentId').val(sessionId);
                            $('#installment-form').find('button[type="submit"]').removeAttr('disabled', 0);

                        });

                        Cardinal.setup("init", {
                            jwt: response.jwtToken
                        });
                    }


                } else {
                    $('.field-installmentrequest-cardnumber').find('.installment-hint').html(response.message);
                    $('#installment-form').find('button[type="submit"]').prop('disabled', 1);
                }
            }
        });
    }

    function validateInstallmentCard(cardNumber) {
        $.ajax({
            url: alepayRequest.baseUrl + 'installment/validate-installment-card/' + alepayRequest.token,
            type: 'post',
            data: {
                _csrf: alepayRequest.csrf, cardNumber: cardNumber, bankCode: alepayRequest.bankCode,
            },
            dataType: "json",
            success: function (response) {
                if (response.status == 'success') {
                    $('.field-installmentrequest2-cardnumber').find('.installment-hint').html('');
                    $('#installment-form').find('button[type="submit"]').prop('disabled', 0);
                    if (response.jwtToken) {
                        $('.jwtcontainer').val(response.jwtToken);
                    }
                    if (response.pgUserCode) {
                        $('#key_cyber').val(response.pgUserCode);
                    }
                    Cardinal.configure({
                        logging: {
                            level: "off"
                        }
                    });
                    var sessionId;
                    // Step 4.  Listen for Events
                    Cardinal.on('payments.setupComplete', function (data) {
                        sessionId = data.sessionId
                        $('#sessionInstallmentId').val(sessionId);
                    });

                    Cardinal.setup("init", {
                        jwt: response.jwtToken
                    });
                } else {
                    $('.field-installmentrequest2-cardnumber').find('.installment-hint').html(response.message);
                    $('#installment-form').find('button[type="submit"]').prop('disabled', 1);
                }
            }
        });
    }

    function validateCitibankCard(cardNumber) {
        $.ajax({
            url: alepayRequest.baseUrl + 'installment/validate-citibank-card/' + alepayRequest.token,
            type: 'post',
            data: {
                _csrf: alepayRequest.csrf, cardNumber: cardNumber,
            },
            dataType: "json",
            success: function (response) {
                if (response.status == 'success') {
                    $('#citiform-step4 .installment-hint').html('');
                    $('#submit-citi-step4').prop('disabled', 0);
                } else {
                    $('#citiform-step4 .installment-hint').html(response.message);
                    $('#submit-citi-step4').prop('disabled', 1);
                }
            }
        });
    }

    if ($('#citistep1-cardNumber').length) {
        var citiCardnumber = new Cleave('.citi-cardnumber', {
            numericOnly: true, blocks: [4],
        });

        $('#citistep1-cardNumber').on("keyup", function (e) {
            if ($(this).val().length > 0 && $(this).val().length < 4) {
                $('.citi-cardnumber-hint').html(alepayRequest.citiCardNumberInvalid);
            } else {
                $('.citi-cardnumber-hint').html('');
            }
        });
    }

    if ($('#citistep1-phoneNumber').length) {
        $('#citistep1-phoneNumber').focusout(function (e) {
            var currentPhone = $(this).val();

            if (currentPhone) {
                if (currentPhone.substring(0, 3) != '+84') {
                    $(this).val('+84' + currentPhone.substring(1));
                }
            }
        });
    }

    $('#submit-citi-step1').click(function (e) {
        e.preventDefault();
        var form = $('#citiform-step1');
        var cardNumber = $('#citistep1-cardNumber');
        var phoneNumber = $('#citistep1-phoneNumber');
        var btnSubmit = $(this);
        var hasError;

        if (cardNumber.length) {
            if (!cardNumber.val()) {
                cardNumber.closest('.form-group').find('.help-block').html(alepayRequest.citiCardNumberRequired);
                hasError = true;
            }
        }

        if (phoneNumber.length) {
            if (!phoneNumber.val()) {
                phoneNumber.closest('.form-group').find('.help-block').html(alepayRequest.citiPhoneNumberRequired);
                hasError = true;
            }
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: alepayRequest.baseUrl + 'citibank/step1/' + alepayRequest.token,
            type: 'post',
            data: form.serialize(),
            dataType: "json",
            beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            },
            success: function (response) {
                if (response.status == 'success') {
                    $('#citi-step1').fadeOut();
                    $('#citi-step1').remove();
                    $('#controlFlowId').val(response.controlFlowId);
                    $('#citistep2-phone').html(response.phone);
                    $('#citi-otp-modal').modal('show');
                } else {
                    toggleSubmitButton(btnSubmit, false);
                    showMessageModal(response.message);
                }
            }
        });
    });

    $('#citistep2-resend-otp').click(function (e) {
        e.preventDefault();
        var btnSubmit = $(this);
        $('.citi-otpstep2-hint').html('');

        $.ajax({
            url: alepayRequest.baseUrl + 'citibank/resend-otp/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf,
            }, dataType: "json", beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            }, success: function (response) {
                if (response.status == 'success') {
                    $('#controlFlowId').val(response.controlFlowId);
                    toggleSubmitButton(btnSubmit, false);
                    $('.citi-otpstep2-hint').html(response.message);
                } else {
                    toggleSubmitButton(btnSubmit, false);
                    $('.citi-otpstep2-hint').html(response.message);
                }
            }
        });
    });

    $('#submit-citi-step2').click(function (e) {
        e.preventDefault();
        var form = $('#citiform-step2');
        var otpStep2 = form.find('input[name="otpStep2"]');
        var btnSubmit = $(this);
        var hasError;

        if (otpStep2.length) {
            if (!otpStep2.val()) {
                form.find('.help-block').html(alepayRequest.citiOtpStep2Required);
                hasError = true;
            }
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: alepayRequest.baseUrl + 'citibank/step2/' + alepayRequest.token,
            type: 'post',
            data: form.serialize(),
            dataType: "json",
            beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            },
            success: function (response) {
                if (response.status == 'success') {
                    $('#citi-otp-modal').modal('hide');
                    $('#citiform-step2').remove();
                    $('#citi-step3').fadeIn();

                    if (response.periods.status == 'success') {
                        $('.step3-installment').html(response.periods.html).fadeIn();

                        if (response.periods.month) {
                            $('#step3-month').val(response.periods.month);
                        }

                        if (response.periods.firstAmountFinal) {
                            $('.order-total-amount').html(response.periods.firstAmountFinal);
                        }

                        if (response.periods.firstAmountFee) {
                            $('.order-total-fee').html(response.periods.firstAmountFee);
                        }
                    }
                } else if (response.status == 'cancel') {
                    $('#citi-otp-modal').modal('hide');
                    $('#message-modal .btn-malert').attr('href', response.returnUrl).removeAttr('data-dismiss');
                    showMessageModal(response.message);
                } else {
                    toggleSubmitButton(btnSubmit, false);
                    $('.citi-otpstep2-hint').html(response.message);
                }
            }
        });
    });

    $(document).on('click', '#citiBoxInstallment .bi-detail', function (e) {
        var $this = $(this);
        var month = $this.find('input[type="radio"]').val();

        $('#step3-month').val(month);
        $('.order-total-amount').html($this.find('.installment-final').html());
        $('.order-total-fee').html($this.find('.installment-fee').html());
    });

    $('body').on('click', '#citibankTab a[data-toggle="tab"]', function () {
        var month = $(this).data('month');
        var amount = $('#tab' + month).find('.installment-final').html();

        $('#step3-month').val(month);
        $('.order-total-amount').html(amount);
        $('.order-total-fee').html($('#tab' + month).find('.installment-fee').html());
    });

    $('#submit-citi-step3').click(function (e) {
        e.preventDefault();
        var form = $('#citiform-step3');
        var btnSubmit = $(this);

        $.ajax({
            url: alepayRequest.baseUrl + 'citibank/step3/' + alepayRequest.token,
            type: 'post',
            data: form.serialize(),
            dataType: "json",
            beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            },
            success: function (response) {
                if (response.status == 'notselected') {
                    $('#citistep4-paymentMethod').html(response.paymentMethod);
                    $('#citistep4-month').html(response.month);
                    $('#citistep4-payPerMonth').html(response.payPerMonth);
                    $('#citi-step3').fadeOut().remove();
                    $('#citi-step4').fadeIn();
                } else if (response.status == 'selected') {
                    $('#citi-step3').fadeOut().remove();
                    $('#citi-step4').fadeIn();
                } else {
                    toggleSubmitButton(btnSubmit, false);
                    showMessageModal(response.message);
                }
            }
        });
    });

    if ($('#citistep4-cardHolderName').length) {
        $('#citistep4-cardHolderName').on("keyup", function (e) {
            $('.card-name-val').text($(this).val().toUpperCase());
        });
    }

    if ($('#citistep4-cardNumber').length) {
        var citiCardNumber = new Cleave('#citistep4-cardNumber', {
            creditCard: true, onCreditCardTypeChanged: function (type) {
                cardType = type;
            }, onValueChanged: function (e) {
                if (cardType == 'amex' || cardType == 'uatp') {
                    $('#citistep4-cardNumber').attr('maxlength', 17);

                    if (e.target.rawValue.length == 15) {
                        validateCitibankCard(e.target.rawValue);
                    } else {
                        $('.installment-hint').html(alepayRequest.cardNumberRequired);
                    }
                } else {
                    $('#citistep4-cardNumber').attr('maxlength', 19);

                    if (e.target.rawValue.length == 16) {
                        validateCitibankCard(e.target.rawValue);
                    } else {
                        $('.installment-hint').html(alepayRequest.cardNumberRequired);
                    }
                }
            }
        });

        $('#citistep4-cardNumber').on("keyup", function (e) {
            var str = $(this).val();
            $('.card-number-val').text(str.substring(0, 7) + "xx xxxx " + str.substring(str.length - 4));
        });

    }

    if ($('#citistep4-expiredCard').length) {
        var citiCardDate = new Cleave('#citistep4-expiredCard', {
            date: true, delimiter: '/', datePattern: ['m', 'y'], onValueChanged: function (e) {
                if (e.target.rawValue.length > 0 && e.target.rawValue.length < 4) {
                    $('.date-hint').html(alepayRequest.cardDateRequired);
                } else {
                    $('.date-hint').html('');
                    var monthYear = getCurrentMonthYear();
                    var currentMonth = monthYear[0];
                    var currentYear = monthYear[1];
                    var arrExpired = e.target.value.split('/');

                    if (arrExpired[1] < currentYear) {
                        $('.date-hint').html(alepayRequest.expiredInvalidYear);
                    } else if (arrExpired[1] == currentYear) {
                        if (arrExpired[0] < currentMonth) {
                            $('.date-hint').html(alepayRequest.expiredInvalidMonth);
                        }
                    }
                }
            }
        });

        $('#citistep4-expiredCard').on("keyup", function (e) {
            $('.card-date-val').text($(this).val());
        });
    }

    if ($('#citistep4-cvcNumber').length) {
        $('#citistep4-cvcNumber').on("keyup", function (e) {
            if ($(this).val().length > 0 && $(this).val().length < 3) {
                $('.cvc-hint').html(alepayRequest.cvcRequired);
            } else {
                $('.cvc-hint').html('');
            }
        });
    }

    $('#submit-citi-step4').click(function (e) {
        e.preventDefault();
        var form = $('#citiform-step4');
        var btnSubmit = $(this);
        var cardName = $('#citistep4-cardHolderName');
        var cardNumber = $('#citistep4-cardNumber');
        var expiredCard = $('#citistep4-expiredCard');
        var cvcNumber = $('#citistep4-cvcNumber');
        var hasError;

        if (cardName.length) {
            if (!cardName.val()) {
                cardName.closest('.form-group').find('.help-block').html(alepayRequest.citiCardNameRequired);
                hasError = true;
            }
        }

        if (cardNumber.length) {
            if (!cardNumber.val()) {
                cardNumber.closest('.form-group').find('.help-block').html(alepayRequest.citiCardNumber2Required);
                hasError = true;
            }
        }

        if (expiredCard.length) {
            if (!expiredCard.val()) {
                expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.citiExpiredCardRequired);
                hasError = true;
            } else {
                var monthYear = getCurrentMonthYear();
                var currentMonth = monthYear[0];
                var currentYear = monthYear[1];
                var arrExpired = expiredCard.val().split('/');

                if (arrExpired[1] < currentYear) {
                    $('.date-hint').html('');
                    expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.expiredInvalidYear);
                    hasError = true;
                } else if (arrExpired[1] == currentYear) {
                    if (arrExpired[0] < currentMonth) {
                        $('.date-hint').html('');
                        expiredCard.closest('.form-group').find('.help-block').html(alepayRequest.expiredInvalidMonth);
                        hasError = true;
                    }
                }
            }
        }

        if (cvcNumber.length) {
            if (!cvcNumber.val()) {
                cvcNumber.closest('.form-group').find('.help-block').html(alepayRequest.citiCvcNumberRequired);
                hasError = true;
            }
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: alepayRequest.baseUrl + 'citibank/step4/' + alepayRequest.token,
            type: 'post',
            data: form.serialize(),
            dataType: "json",
            beforeSend: function () {
                toggleSubmitButton(btnSubmit, true);
            },
            success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton(btnSubmit, false);
                } else if (response.status == 'is3D') {
                    var form3d = $('#form3d');
                    form3d.attr('action', response.data.termUrl);
                    form3d.find('input[name="PaReq"]').val(response.data.paReq);
                    form3d.find('input[name="MD"]').val(response.data.md);
                    form3d.find('input[name="TermUrl"]').val(response.linkCallback3D);
                    form3d.submit();
                } else if (response.status == 'review') {
                    window.location.href = response.reviewUrl;
                } else if (response.status == 'is2D') {
                    window.location.href = response.card2dUrl;
                }
            }
        });
    });

    $('#btn-cancel-transaction').click(function (e) {
        e.preventDefault();
        var $this = $(this);

        $.ajax({
            url: alepayRequest.baseUrl + 'cancel-transaction/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf
            }, dataType: "json", beforeSend: function () {
                toggleSubmitButton($this, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton($this, false);
                } else if (response.status == 'success') {
                    $('#message-modal .btn-malert').attr('href', response.returnUrl).removeAttr('data-dismiss');
                    showMessageModal(response.message);
                }
            }
        });
    });

    $('.btn-review-upload').click(function (e) {
        e.preventDefault();
        $(this).parents('.review-img').find('input[type="file"]')[0].click();
    });

    function readUrlImage(input, img) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var file = input.files[0];
            var urlInput = $(input).parents('.review-img').find('input[type="hidden"]');
            var reviewError = $(input).parents('.review-row').find('.review-image-error');
            reviewError.html('');

            reader.onload = function (e) {
                if (file.type.match('image/jpeg') || file.type.match('image/png')) {
                    $(img).attr('src', e.target.result).show();
                    var formData = new FormData();
                    formData.append('image', file);
                    formData.append('tag', 'REVIEW_INFO');

                    $.ajax({
                        url: 'https://alepay.vn/appimage/api/image/upload',
                        type: 'post',
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        dataType: "json",
                        success: function (response) {
                            if (response.data.url) {
                                urlInput.val(response.data.url);
                            } else {
                                reviewError.html(alepayRequest.reviewImageError);
                            }
                        }
                    });
                } else {
                    $(img).hide();
                    reviewError.html(alepayRequest.reviewImageError);
                }
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $('.review-img input[type="file"]').change(function () {
        readUrlImage(this, $(this).next());
    });

    if ($('.review-input-text').length) {
        var reviewNumber = new Cleave('.review-input-text1', {
            numericOnly: true, blocks: [6],
        });

        var reviewNumber2 = new Cleave('.review-input-text2', {
            numericOnly: true, blocks: [4],
        });

        $('.review-input-text').on("keyup", function (e) {
            $('.review-error').html('');
        });
    }

    $('#submit-bank-review').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        var hasError = false;
        var sixNum = $('#six_num').val();
        var fourNum = $('#four_num').val();
        var bankStatementImage = $('#bankStatementImage').val();

        $('.review-error').html('');
        $('.review-image-error').html('');

        if (!sixNum) {
            $('.review-error').append('<span>' + alepayRequest.reviewSixNumberRequired + '</span>');
            hasError = true;
        }

        if (!fourNum) {
            $('.review-error').append('<span>' + alepayRequest.citiCardNumberInvalid + '</span>');
            hasError = true;
        }

        if (!bankStatementImage) {
            $('.review-image-error0').append('<span>' + alepayRequest.reviewImageRequired + '</span>');
            hasError = true;
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: alepayRequest.baseUrl + 'card/bank-review/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf, bankStatementImage: bankStatementImage, sixNum: sixNum, fourNum: fourNum,
            }, dataType: "json", beforeSend: function () {
                toggleSubmitButton($this, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton($this, false);
                } else if (response.status == 'success') {
                    $('#pay-sercu').modal('hide');
                    $('#message-modal .btn-malert').attr('href', $('.reviewReturnUrl').attr('href')).removeAttr('data-dismiss');
                    showMessageModal(response.message);
                }
            }
        });
    });

    $('#submit-cmnd-review').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        var hasError = false;
        var identityCardFrontImage = $('#identityCardFrontImage').val();
        var identityCardBackImage = $('#identityCardBackImage').val();
        var cardImage = $('#cardImage').val();

        $('.review-image-error').html('');

        if (!identityCardFrontImage) {
            $('.review-img-error1').append('<span>' + alepayRequest.reviewImageRequired + '</span>');
            hasError = true;
        }

        if (!identityCardBackImage) {
            $('.review-img-error2').append('<span>' + alepayRequest.reviewImageRequired + '</span>');
            hasError = true;
        }

        if (!cardImage) {
            $('.review-img-error3').append('<span>' + alepayRequest.reviewImageRequired + '</span>');
            hasError = true;
        }

        if (hasError) {
            return false;
        }

        $.ajax({
            url: alepayRequest.baseUrl + 'card/cmnd-review/' + alepayRequest.token, type: 'post', data: {
                _csrf: alepayRequest.csrf,
                cardImage: cardImage,
                identityCardFrontImage: identityCardFrontImage,
                identityCardBackImage: identityCardBackImage,
            }, dataType: "json", beforeSend: function () {
                toggleSubmitButton($this, true);
            }, success: function (response) {
                if (response.status == 'fail') {
                    showMessageModal(response.message);
                    toggleSubmitButton($this, false);
                } else if (response.status == 'success') {
                    $('#pay-sercu').modal('hide');
                    $('#message-modal .btn-malert').attr('href', $('.reviewReturnUrl').attr('href')).removeAttr('data-dismiss');
                    showMessageModal(response.message);
                }
            }
        });
    });
});
$(document).on('click', '[data-href]', function (event) {
    // Kiểm tra xem phần tử có thuộc tính data-href không
    var hrefValue = $(this).attr('data-href');

    if (hrefValue) {
        console.log('data-href:', hrefValue); // Hiển thị giá trị của data-href

        // Nếu muốn làm thêm hành động khi nhấn vào, ví dụ chuyển hướng
        window.location.href = hrefValue;
    } else {
        console.error('Phần tử không có thuộc tính data-href');
    }
});
$(document).ready(function () {


    console.log("ready!");
    var label = document.getElementById("label_timer");
    var hidden_time_created = $("#hidden-time-created").attr('value');
    var cancel_url = $("#cancel_url").attr('value');
    // console.log(label);
    // return;
    label.innerHTML = $("#text_count_down").attr('value') + ':';

    var sec = hidden_time_created, countDiv = document.getElementById("timer"), secpass,
        countDown = setInterval(function () {
            'use strict';
            secpass();
        }, 1000);

    function secpass() {
        'use strict';
        var hours = Math.floor(sec / 3600);
        var min = Math.floor((sec % 3600) / 60);
        var remSec = sec % 60;

        // Định dạng giây, phút và giờ cho hiển thị dạng 2 chữ số
        if (hours < 10) {
            hours = '0' + hours;
        }
        if (min < 10) {
            min = '0' + min;
        }
        if (remSec < 10) {
            remSec = '0' + remSec;
        }

        // Hiển thị thời gian dạng giờ:phút:giây
        countDiv.innerHTML = hours + ":" + min + ":" + remSec;

        if (sec > 0) {
            sec = sec - 1;
        } else {
            clearInterval(countDown);
            countDiv.innerHTML = 'Hết thời hạn thanh toán!';
            let text_count_down = $('#text_count_down');
            let token = text_count_down.data('token');
            let url = text_count_down.data('url-detroy');
            console.log('Hết thời hạn thanh toán!');
            console.log('token: ', token)
            console.log('url: ', url)
            transactionDestroy(token, url);
            var cancel_url = $("#cancel_url").attr('value');

            // ko redirect trang Cancel
            window.location.href = cancel_url;
        }
    }

    function transactionDestroy(token, url) {
        var cancel_url = $("#cancel_url").attr('value');

        $.ajax({
            url: url, // Đường dẫn đến API của bạn
            method: 'post', data: {token_code: token}, dataType: 'json', // Định dạng dữ liệu mong muốn (có thể là json, text, html, v.v.)
            success: function (response) {
                // Xử lý kết quả trả về từ server
                console.log('Success:', response);
                console.log(123)
                console.log(cancel_url);
                window.location.href = cancel_url;
            }, error: function (xhr, status, error) {
                // Xử lý lỗi
                console.log('Error:', error);
            }
        });
    }
})

function setItemWithExpiry(key, value, ttl) {
    const now = new Date();

    // `ttl` là thời gian tồn tại tính bằng mili-giây
    const item = {
        value: value, expiry: now.getTime() + ttl,
    };

    localStorage.setItem(key, JSON.stringify(item));
    console.log('setItemWithExpiry: ' + key);
}

function getItemWithExpiry(key) {
    const itemStr = localStorage.getItem(key);

    // Trả về null nếu không tìm thấy biến
    if (!itemStr) {
        return null;
    }

    const item = JSON.parse(itemStr);
    const now = new Date();

    // So sánh thời gian hết hạn với thời gian hiện tại
    if (now.getTime() > item.expiry) {
        // Xóa biến khỏi `localStorage` nếu đã hết hạn
        localStorage.removeItem(key);
        return null;
    }

    return item.value;
}
