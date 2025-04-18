$(document).ready(function () {
    var merchant_id = $('#merchantform-id').val();
    var bank_code_first = $('select#bank-code:first-child').val();
    showConfig(merchant_id, bank_code_first);

    $('select#bank-code').change(function () {
        var bank_code = $(this).children("option:selected").val();
        $('.cycle-bank').prop('checked', false);
        $('.card-bank').prop('checked', false);

        showConfig(merchant_id, bank_code);
    });

    function showConfig(merchant_id, bank_code) {
        $.ajax({
            type: 'post',
            url: get_info_config_url,
            data: {
                merchant_id: merchant_id,
                bank_code: bank_code,
            }, success: function (res) {
                var config_bank = JSON.parse(res);
                if (config_bank.cycle_bank) {
                    config_bank.cycle_bank.forEach(function (item) {
                        $('#month' + item).prop('checked', true);
                    });
                }
                if (config_bank.card_bank) {
                    config_bank.card_bank.forEach(function (item) {
                        $('#card' + item).prop('checked', true);
                    });
                }
            }
        });
    }
});