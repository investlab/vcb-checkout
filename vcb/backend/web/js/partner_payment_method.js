
$(document).ready(function () {

    // khi modal edit đóng thì tất cả input ở modal add reset
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
// Khóa
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

//Mở khóa
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

// Show modal sửa
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
