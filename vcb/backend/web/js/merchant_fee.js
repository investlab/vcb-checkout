/**
 * 
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
