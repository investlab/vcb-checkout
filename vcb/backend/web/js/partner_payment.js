/**
 * .
 */

var partner_payment = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add_Partner_Payment').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Partner_Payment').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

});

// Show modal sửa phương thức
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

// Khóa phương thức
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

// Mở khóa phương thức
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

