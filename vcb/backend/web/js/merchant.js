var merchant = {};

merchant.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            // data = JSON.parse(msg);
            var $form = $('#config-credit-account-form');

            $.each(msg, function (key, value) {
                $form.find('input[name="CreditAccountForm[' + key + ']"]').val(value);
            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

$(document).on("hidden.bs.modal", "#ConfigCreditAccount", function () {
    $('#config-credit-account-form').trigger("reset");
    $('#config-credit-account-form .help-block-error').html('');
});
