/**
 *
 */

var checkout_order_backup = {};
checkout_order_backup.changePaymentMethod = function () {
    var payment_method_id = $('#payment_method_id').val();
    var url = $('#payment_method_id').attr('data-url');

    $.get(url, {payment_method_id: payment_method_id}, function (data) {
        if (data) {
            $('#partner_payment_id').html(data);
        }
    });
};

