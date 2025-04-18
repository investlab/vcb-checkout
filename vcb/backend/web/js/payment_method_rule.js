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
var payment_method_rule = {};
// Khóa
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

//Mở khóa
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

// Show modal sửa
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
