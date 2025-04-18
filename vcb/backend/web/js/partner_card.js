/**
 
 */

var partner_card = {};
// reset các form khi đóng modal
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

