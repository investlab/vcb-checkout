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
var payment_method_fee = {};
// Khóa
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

// Gửi duyệt
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

// Kích hoạt
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

// Từ chối
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

// Show modal sửa
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
