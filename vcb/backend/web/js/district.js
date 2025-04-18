var district = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
// Show modal sửa
district.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-district-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="ZoneUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'parent_id') {
                    $form.find('select[name="ZoneUpdateForm[parent_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="ZoneUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};


// Kích hoạt
district.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
district.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-district-form").attr("action", $("#active-district-form").attr("action") + '?' + params);
    }
    $("#active-district-form").submit();
};

// Khóa
district.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
district.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-district-form").attr("action", $("#lock-district-form").attr("action") + '?' + params);
    }
    $("#lock-district-form").submit();
};
