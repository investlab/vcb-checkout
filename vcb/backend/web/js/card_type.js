/**
 
 */


var card_type = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Update').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

card_type.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#update-card-type-form');

            $.each(data, function (key, value) {
                $form.find('input[name="CardTypeForm[' + key + ']"]').val(value);
            });
            $('#Update').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

card_type.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
card_type.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-card-type-form").attr("action", $("#lock-card-type-form").attr("action") + '?' + params);
    }
    $("#lock-card-type-form").submit();
};


card_type.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
card_type.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-card-type-form").attr("action", $("#active-card-type-form").attr("action") + '?' + params);
    }
    $("#active-card-type-form").submit();
};


