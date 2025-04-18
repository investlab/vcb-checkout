var city = {};

// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

// Kích hoạt
city.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
city.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-city-form").attr("action", $("#active-city-form").attr("action") + '?' + params);
    }
    $("#active-city-form").submit();
};

// Khóa
city.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
city.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-city-form").attr("action", $("#lock-city-form").attr("action") + '?' + params);
    }
    $("#lock-city-form").submit();
};

// Show modal sửa
city.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-city-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="ZoneUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="ZoneUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};
