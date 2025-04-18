var defaults = {};
$(document).ready(function () {
    $('#change_pass').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
/*account*/
$(document).ready(function () {
    $('#Edit_Account').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Add_Account').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var account = {};
account.submitForm = function (form) {

    var balance = form.find('input[name="AddAccountForm[balance]"]').val();
    if ((typeof balance == 'undefined') || balance.trim() == "") {
        balance = 0;
    } else {
        balance = balance.replace(/\./g, '');
    }

    form.find('input[name="AddAccountForm[balance]"]').val(balance);

    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
account.modalAddBalance = function (id, name) {
    $('#AddBalance').modal('show');
    $('input[name="AddAccountForm[id]"]').val(id);
    $('input[name="AddAccountForm[name]"]').val(name);
    document.getElementById('addAccName').innerHTML = name;
};
account.modalLockAcc = function (id, name) {
    $('#LockAcc').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockAccName').innerHTML = name;
};
account.submitLockAcc = function () {
    var href = window.location.href;
    var form = $("#lock-acc-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
account.modalUnLockAcc = function (id, name) {
    $('#UnlockAcc').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockAccName').innerHTML = name;
};
account.submitUnLockAcc = function () {
    var href = window.location.href;
    var form = $("#unlock-acc-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
account.viewEditAccount = function (id, url) {

    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-account-form');

            $.each(data, function (key, value) {
                $form.find('select[name="AddAccountForm[' + key + ']"]').val(value);
                $form.find('input[name="AddAccountForm[' + key + ']"]').val(value);
            });
            $('#Edit_Account').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * Bank
 */
var bank = {};
$(document).ready(function () {

    $('#Add_Bank').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Bank').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
bank.viewEditAlegoBank = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-alego-bank-form');

            $.each(data, function (key, value) {
                if (key == 'bank_id') {
                    $form.find('select[name="AlegoBankForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }

                $form.find('input[name="AlegoBankForm[' + key + ']"]').val(value);
            });
            $('#Edit_Alego_Bank').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
bank.viewEditBank = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-bank-form');

            $.each(data, function (key, value) {
                if (key == 'description') {
                    $form.find('textarea[name="AddBankForm[' + key + ']"]').val(value);
                }
                $form.find('input[name="AddBankForm[' + key + ']"]').val(value);
            });
            $('#Edit_Bank').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
bank.modalLockBank = function (id, name) {
    $('#LockBank').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
bank.submitLockBank = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-bank-form").attr("action", $("#lock-bank-form").attr("action") + '?' + params);
    }
    $("#lock-bank-form").submit();
};
bank.modalUnLockBank = function (id, name) {
    $('#UnlockBank').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
bank.submitUnLockBank = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-bank-form").attr("action", $("#unlock-bank-form").attr("action") + '?' + params);
    }
    $("#unlock-bank-form").submit();
};
/**
 * Cardtype
 */
var card_type = {};
$(document).ready(function () {
    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Update').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
card_type.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#update-card-type-form');

            $.each(data, function (key, value) {
                $form.find('input[name="CardTypeForm[' + key + ']"]').val(value);
            });
            $('#Update').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
card_type.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
card_type.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-card-type-form").attr("action", $("#lock-card-type-form").attr("action") + '?' + params);
    }
    $("#lock-card-type-form").submit();
};
card_type.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
card_type.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-card-type-form").attr("action", $("#active-card-type-form").attr("action") + '?' + params);
    }
    $("#active-card-type-form").submit();
};
/**
 * cashout
 */
var cashout = {};
cashout.changeMethod = function(obj) {
    var payment_method_id = obj.value;
    var url = $(obj).attr('data-url');
    document.location.href = url + '?payment_method_id=' + payment_method_id;

};
/**
 * checkout_order
 */
var checkout_order = {};
checkout_order.changePaymentMethod = function () {
    var payment_method_id = $('#payment_method_id').val();
    var url = $('#payment_method_id').attr('data-url');

    $.get(url, {payment_method_id: payment_method_id}, function (data) {
        if (data) {
            $('#partner_payment_id').html(data);
        }
    });
};
/**
 * city
 */
var city = {};

$(document).ready(function () {

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
city.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
city.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-city-form").attr("action", $("#active-city-form").attr("action") + '?' + params);
    }
    $("#active-city-form").submit();
};
city.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
city.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-city-form").attr("action", $("#lock-city-form").attr("action") + '?' + params);
    }
    $("#lock-city-form").submit();
};
city.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-city-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="ZoneUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="ZoneUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * district
 */
var district = {};
$(document).ready(function () {
    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
district.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-district-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="ZoneUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'parent_id') {
                    $form.find('select[name="ZoneUpdateForm[parent_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="ZoneUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
district.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
district.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-district-form").attr("action", $("#active-district-form").attr("action") + '?' + params);
    }
    $("#active-district-form").submit();
};
district.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
district.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-district-form").attr("action", $("#lock-district-form").attr("action") + '?' + params);
    }
    $("#lock-district-form").submit();
};
/**
 * Keyword
 */
$(document).ready(function () {
    $('#Edit_Keyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#LockKeyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#UnlockKeyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
var keyword_js = {};
keyword_js.modalLock = function (id, name) {
    $('#LockKeyword').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lock_Keyword').innerHTML = name;
};
keyword_js.modalActive = function (id, name) {
    $('#UnlockKeyword').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unlock_Keyword').innerHTML = name;
};
keyword_js.submitForm = function (from) {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        from.attr("action", from.attr("action") + '?' + params);
    }
    from.submit();
};
keyword_js.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-keyword-form');

            $.each(data, function (key, value) {
                $form.find('input[name="KeywordForm[' + key + ']"]').val(value);
            });
            $('#Edit_Keyword').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * merchant_fee
 */
var merchant_fee = {};
merchant_fee.changeMethod1 = function () {
    var method_id = $('#method_id').val();
    var url = $('#method_id').attr('data-url');

    $.get(url, {method_id: method_id}, function (data) {
        if (data) {
            $('#payment_method_id').html(data);
        }
    });
};
merchant_fee.changeMethod = function () {
    var method_id = $('#method_id').val();
    var url = $('#method_id').attr('data-url');

    $.get(url, {method_id: method_id}, function (data) {
        result = JSON.parse(data);
        $.each(result, function (key, value) {
            if (key == 'option') {
                $('#payment_method_id').html(value);
            }
            if (key == 'transaction_type_id') {
                if (value == 1) {
                    $("#receiver").removeClass('hidden');
                }
                if (value == 2 || value == 3) {
                    $("#receiver").addClass('hidden');
                }
            }
        })
    });
};
/**
 * method
 */
var method = {};
$(document).ready(function () {

    $('#Add_Method').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Method').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
method.viewEditMethod = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-method-form');

            $.each(data, function (key, value) {
                if (key == 'transaction_type_id') {
                    $form.find('select[name="AddMethodForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'description') {
                    $form.find('textarea[name="AddMethodForm[' + key + ']"]').val(value);
                }
                $form.find('input[name="AddMethodForm[' + key + ']"]').val(value);
            });
            $('#Edit_Method').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
method.modalLockMethod = function (id, name) {
    $('#LockMethod').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockMName').innerHTML = name;
};
method.submitLockMethod = function () {
    var href = window.location.href;
    var form = $("#lock-method-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
method.modalUnLockMethod = function (id, name) {
    $('#UnlockMethod').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockMName').innerHTML = name;
};
method.submitUnLockMethod = function () {
    var href = window.location.href;
    var form = $("#unlock-method-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
/**
 *partner_card
 */
var partner_card = {};
$(document).ready(function () {
    $('#Add').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
    $('#Update').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
    $('#Lock').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
    $('#Active').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

});
partner_card.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#update-partner-card-form');

            $.each(data, function (key, value) {
                if (key == 'bill_type') {
                    $form.find('select[name="PartnerCardForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'config') {
                    $form.find('textarea[name="PartnerCardForm[' + key + ']"]').val(value);
                }

                $form.find('input[name="PartnerCardForm[' + key + ']"]').val(value);
            });
            $('#Update').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
partner_card.modalLock = function (id,name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
partner_card.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-partner-card-form").attr("action", $("#lock-partner-card-form").attr("action") + '?' + params);
    }
    $("#lock-partner-card-form").submit();
};
partner_card.modalActive = function (id,name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
partner_card.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-partner-card-form").attr("action", $("#active-partner-card-form").attr("action") + '?' + params);
    }
    $("#active-partner-card-form").submit();
};
/**
 * partner_card_type
 */
var partner_card_type = {};
partner_card_type.modalLock = function (id,name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
partner_card_type.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-partner-card-type-form").attr("action", $("#lock-partner-card-type-form").attr("action") + '?' + params);
    }
    $("#lock-partner-card-type-form").submit();
};
partner_card_type.modalActive = function (id,name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
partner_card_type.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-partner-card-type-form").attr("action", $("#active-partner-card-type-form").attr("action") + '?' + params);
    }
    $("#active-partner-card-type-form").submit();
};
/**
 * partner_payment
 */
var partner_payment = {};
$(document).ready(function () {

    $('#Add_Partner_Payment').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Partner_Payment').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

});
partner_payment.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-partner-payment-form');

            $.each(data, function (key, value) {
                if (key == 'description') {
                    $form.find('textarea[name="AddPartnerPaymentForm[' + key + ']"]').val(value);
                }
                $form.find('input[name="AddPartnerPaymentForm[' + key + ']"]').val(value);
            });
            $('#Edit_Partner_Payment').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
partner_payment.modalLock = function (id,name) {
    $('#LockPartnerPayment').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockName').innerHTML = name;
};
partner_payment.submitLock = function () {
    var href = window.location.href;
    var form = $("#lock-partner-payment-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
partner_payment.modalUnLock = function (id,name) {
    $('#UnlockPartnerPayment').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockName').innerHTML = name;
};
partner_payment.submitUnLock = function () {
    var href = window.location.href;
    var form = $("#unlock-partner-payment-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
/**
 * partner_payment_method
 */
$(document).ready(function () {
    $('#Lock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Unlock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var partner_payment_method = {};
partner_payment_method.modalLock = function (id,payment_method_id) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    $('input[name=payment_method_id]').val(payment_method_id);
};
partner_payment_method.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-partner-payment-method-form").attr("action", $("#lock-partner-payment-method-form").attr("action") + '?' + params);
    }
    $("#lock-partner-payment-method-form").submit();
};
partner_payment_method.modalUnLock = function (id,payment_method_id) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    $('input[name=payment_method_id]').val(payment_method_id);
};
partner_payment_method.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-partner-payment-method-form").attr("action", $("#unlock-partner-payment-method-form").attr("action") + '?' + params);
    }
    $("#unlock-partner-payment-method-form").submit();
};
partner_payment_method.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-partner-payment-method-form');

            $.each(data, function (key, value) {
                if (key == 'partner_payment_id') {
                    $form.find('select[name="PartnerPaymentMethod[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'enviroment') {
                    $form.find('select[name="PartnerPaymentMethod[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'position') {
                    $form.find('input[name="PartnerPaymentMethod[' + key + ']"]').val(value);
                }
                $form.find('input[name="PartnerPaymentMethod[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * paymentmethod
 */
$(document).ready(function () {
    $('#Lock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Unlock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var paymentmethod = {};
paymentmethod.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    // document.getElementById('lockBNumber').innerHTML = name;
};
paymentmethod.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-paymentmethod-form").attr("action", $("#lock-paymentmethod-form").attr("action") + '?' + params);
    }
    $("#lock-paymentmethod-form").submit();
};
paymentmethod.modalLockAll = function () {
    $('#LockAll').modal('show');
    if ($('#check-all-method').prop('checked')) {
        $('input#arr-method-id').val(0);
    } else {
        var arr_payment_method = [];
        $('input.payment-method-checkbox').each(function () {
            if ($(this).prop('checked')) {
                arr_payment_method.push($(this).val());
            }
        });
        console.log(arr_payment_method);
        $('input#arr-method-id').val(arr_payment_method);
    }
};
paymentmethod.submitLockAll = function() {
    console.log($('#check-all-method').prop('checked'));
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-all-paymentmethod-form").attr("action", $("#lock-all-paymentmethod-form").attr("action") + '?' + params);
    }

    $("#lock-all-paymentmethod-form").submit();
};
$(document).ready(function () {
    $('#check-all-method').change(function () {
        if ($(this).prop('checked')) {
            $('input.payment-method-checkbox').each(function () {
                $(this).prop('checked', true);
            });
        } else {
            $('input.payment-method-checkbox').each(function () {
                $(this).prop('checked', false);
            });
        }
    });
});

paymentmethod.modalUnLock = function (id,name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
paymentmethod.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-paymentmethod-form").attr("action", $("#unlock-paymentmethod-form").attr("action") + '?' + params);
    }
    $("#unlock-paymentmethod-form").submit();
};
/**
 * payment_method_fee
 */
$(document).ready(function () {

    $('#Lock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Unlock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var payment_method_fee = {};
payment_method_fee.modalLock = function (id) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
};
payment_method_fee.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-payment-method-fee-form").attr("action", $("#lock-payment-method-fee-form").attr("action") + '?' + params);
    }
    $("#lock-payment-method-fee-form").submit();
};
payment_method_fee.modalRequest = function (id) {
    $('#Request').modal('show');
    $('input[name=id]').val(id);
};
payment_method_fee.submitRequest = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#request-payment-method-fee-form").attr("action", $("#request-payment-method-fee-form").attr("action") + '?' + params);
    }
    $("#request-payment-method-fee-form").submit();
};
payment_method_fee.modalActive = function (id) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
};
payment_method_fee.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-payment-method-fee-form").attr("action", $("#active-payment-method-fee-form").attr("action") + '?' + params);
    }
    $("#active-payment-method-fee-form").submit();
};
payment_method_fee.modalReject = function (id) {
    $('#Reject').modal('show');
    $('input[name=id]').val(id);
};
payment_method_fee.submitReject = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#reject-payment-method-fee-form").attr("action", $("#reject-payment-method-fee-form").attr("action") + '?' + params);
    }
    $("#reject-payment-method-fee-form").submit();
};
payment_method_fee.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-payment-method-fee-form');

            $.each(data, function (key, value) {
                if (key == 'payment_method_id') {
                    $form.find('select[name="PaymentMethodFeeForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="PaymentMethodFeeForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * payment_method_rule
 */
$(document).ready(function () {
    $('#Lock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Unlock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var payment_method_rule = {};
payment_method_rule.modalLock = function (id) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
};
payment_method_rule.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-payment-method-rule-form").attr("action", $("#lock-payment-method-rule-form").attr("action") + '?' + params);
    }
    $("#lock-payment-method-rule-form").submit();
};
payment_method_rule.modalUnLock = function (id) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
};
payment_method_rule.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-payment-method-rule-form").attr("action", $("#unlock-payment-method-rule-form").attr("action") + '?' + params);
    }
    $("#unlock-payment-method-rule-form").submit();
};
payment_method_rule.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-payment-method-rule-form');

            $.each(data, function (key, value) {
                if (key == 'payment_method_id') {
                    $form.find('select[name="PaymentMethodRuleForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'payment_method_rule_type_id') {
                    $form.find('select[name="PaymentMethodRuleForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'option') {
                    $form.find('select[name="PaymentMethodRuleForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="PaymentMethodRuleForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/**
 * .payment_transaction
 */
$(document).ready(function () {
    $('#edit_cUser').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#edit_mReceipt').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
var payment_transaction = {};
payment_transaction.viewEditcUser = function (form,id,user_created) {
    $('#edit_cUser').modal('show');
    form.find('#editpaymenttransactionform-id').val(id);
    form.find('#editpaymenttransactionform-user_create').val(user_created);
};
payment_transaction.viewEditmReceipt = function (form,id,partner_payment_method_receipt) {
    $('#edit_mReceipt').modal('show');
    form.find('#editpaymenttransactionform-id').val(id);
    form.find('#editpaymenttransactionform-partner_payment_method_receipt').val(partner_payment_method_receipt);
};
/**
 * reason
 */
var reason = {};
reason.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-reason-form');

            $.each(data, function (key, value) {
                if (key == 'description') {
                    $form.find('textarea[name="Reason[' + key + ']"]').val(value);
                }
                if (key == 'type') {
                    $form.find('select[name="Reason[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="Reason[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
reason.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
reason.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-reason-form").attr("action", $("#lock-reason-form").attr("action") + '?' + params);
    }
    $("#lock-reason-form").submit();
};
reason.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
reason.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-reason-form").attr("action", $("#unlock-reason-form").attr("action") + '?' + params);
    }
    $("#unlock-reason-form").submit();
};
/**
 * user
 */
var user = {};
user.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
user.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-user-form").attr("action", $("#lock-user-form").attr("action") + '?' + params);
    }
    $("#lock-user-form").submit();
};
user.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
user.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-user-form").attr("action", $("#unlock-user-form").attr("action") + '?' + params);
    }
    $("#unlock-user-form").submit();
};
user.modalReset = function (id, name) {
    $('#Reset').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('resetBNumber').innerHTML = name;
};
user.submitReset = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#user-reset-form").attr("action", $("#user-reset-form").attr("action") + '?' + params);
    }
    $("#user-reset-form").submit();
};
user.createSales = function (type) {
    var sale_channel_id = $('#sale_channel').val();
    var sale_channel_name = $('#sale_channel_name').val();
    var sale_channel_user_id = $('#sale_channel_user').val();
    var sale_channel_user_name = $('#sale_channel_user_name').val();
    if (typeof sale_channel_user_name == "undefined") {
        sale_channel_user_name = '';
    }
    var obj_name = 'UserAddForm';
    if (type == 'UPDATE') {
        obj_name = 'UserUpdateForm';
    }
    //console.log(obj_name);
    var channel = $('#sale_channel_' + sale_channel_id).val();
    if (typeof channel == 'undefined' || channel == '') {
        var html = '<tr>' +
            '<td>' + sale_channel_name + '<input id="sale_channel_' + sale_channel_id + '" type="hidden" value="' + sale_channel_id + '" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][sale_channel_id]"/>' +
            '</td>' +
            '<td>' + sale_channel_user_name + '<input type="hidden" value="' + sale_channel_user_id + '" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][parent_id]"/>' + '</td>' +
            '<td>' +
            '<input class="noStyle default-radio" id="default_' + sale_channel_id + '" type="radio" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][default]" value="0" onchange="user.changeDefault(' + sale_channel_id + ');" >' +
            '</td>' +
            '<td>' +
            '<button class="btn btn-success" type="button"' +
            'onclick="user.removeChannel(this);">Xóa</button>' +
            '</td>' +
            '</tr>';
        //console.log(n);
        $('#sales_items').append(html);
    }

    $('#ajax-dialog').modal('hide');
    $('.ajax-target').modal('hide');
    //return false;
};
user.removeChannel = function (obj) {
    $(obj).parent().parent().remove();
};
user.changeDefault = function (sale_channel_id) {
    var id_default = 'default_' + sale_channel_id;
    $('.default-radio').each(function () {
        if (this.id != id_default) {
            this.value = 0;
            this.checked = false;
        } else {
            this.value = 1;
            this.checked = true;
        }

    });
    //$('#default_' + sale_channel_id).val(1);
    //$('#default_' + sale_channel_id).attr('checked', true);
};
user.viewUpdateAdminAccount = function (id, group_id, name, status) {
    var $form = $('#update-user-admin-account-form');
    $form.find('input[name="UserAdminAccountForm[name]"]').val(name);
    $form.find('input[name="UserAdminAccountForm[id]"]').val(id);
    $form.find('select[name="UserAdminAccountForm[user_group_id]"]').val(group_id);
    $form.find('select[name="UserAdminAccountForm[status]"]').val(status);
    $('#modal-update-account').modal('show');
};
user.modalDefaultChannel = function (id, user_id, admin_account_id) {
    $('#DefaultChannel').modal('show');
    $('input[name=default_id]').val(id);
    $('input[name=default_user_id]').val(user_id);
    $('input[name=default_admin_account_id]').val(admin_account_id);
};
user.modalDeleteChannel = function (id, user_id, admin_account_id) {
    $('#DeleteChannel').modal('show');
    $('input[name=delete_id]').val(id);
    $('input[name=delete_user_id]').val(user_id);
    $('input[name=delete_admin_account_id]').val(admin_account_id);
};
user.getCreditPartnerBranch = function (cp_id, branch_ids, admin_account_id, view) {
    var form = $('#add-branch-admin-account-form');
    var credit_partner_id = form.find('select[name="credit_partner_id"]').val();
    if (admin_account_id > 0) {
        form.find('input[name="admin_account_id"]').val(admin_account_id);
    }
    if (view && cp_id > 0) {
        credit_partner_id = cp_id;
    }
    var url = $('#url-get-branch-by-partner-id-ajax').html();
    $.get(url, {credit_partner_id: credit_partner_id, branch_ids: branch_ids}, function (data) {
        if (data) {
            form.find('#branch_items').html(data);
        }
    });
};
user.getMtqInventory = function (inventory_ids, admin_account_id) {
    var form = $('#add-inventory-admin-account-form');
    form.find('input[name="admin_account_id"]').val(admin_account_id);
    var url = $('#url-get-mtq-inventory-ajax').html();
    $.get(url, {inventory_ids: inventory_ids}, function (data) {
        if (data) {
            form.find('#mtq_inventory_items').html(data);
        }
    });
};
/*usergroup*/
$(document).ready(function () {

    $('#AddGroup').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var usergroup = {};
usergroup.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
usergroup.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-user-group-form").attr("action", $("#lock-user-group-form").attr("action") + '?' + params);
    }
    $("#lock-user-group-form").submit();
};
usergroup.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
usergroup.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-user-group-form").attr("action", $("#unlock-user-group-form").attr("action") + '?' + params);
    }
    $("#unlock-user-group-form").submit();
};
usergroup.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-user-group-form');

            $.each(data, function (key, value) {
                if (key == 'parent_id') {
                    if (value != null) {
                        $form.find('select[name="UserGroup[' + key + ']"]').val(value);
                    } else {
                        $form.find('select[name="UserGroup[' + key + ']"]').val('0');
                    }

                }
                $form.find('input[name="UserGroup[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
/*wards*/
var wards = {};
$(document).ready(function () {
    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
wards.getDistrictByCityID = function (city_id, district_id, form) {
    var cityId = form.find('#city_id').val();
    var districtId = form.find('#district_id').val();
    if (districtId == 0) {
        districtId = district_id;
    }
    if (cityId == 0) {
        cityId = city_id;
    }
    var url = $('#url-district-ajax').html();
    $.get(url, {city_id: cityId, district_id: districtId}, function (data) {
        form.find('#district_id').html(data);
    });
    return false;
}
wards.getDistrictByCityIDSearch = function () {
    var cityId = $('#city_id_search').val();
    var url = $('#url-district-search-ajax').html();
    $.get(url, {city_id: cityId}, function (data) {
        $('#district_id_search').html(data);
    });
    return false;

};
wards.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-wards-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="WardsUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'parent_id') {
                    $form.find('select[name="WardsUpdateForm[parent_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'city_id') {
                    $form.find('select[name="WardsUpdateForm[city_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="WardsUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
wards.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
wards.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-wards-form").attr("action", $("#active-wards-form").attr("action") + '?' + params);
    }
    $("#active-wards-form").submit();
};
wards.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
wards.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-wards-form").attr("action", $("#lock-wards-form").attr("action") + '?' + params);
    }
    $("#lock-wards-form").submit();
};


