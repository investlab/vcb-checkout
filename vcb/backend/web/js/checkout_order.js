/**
 * 
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

