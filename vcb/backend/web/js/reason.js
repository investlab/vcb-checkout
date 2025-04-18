var reason = {};
// Show modal sửa
reason.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-reason-form');

            $.each(data, function (key, value) {
                if (key == 'description') {
                    $form.find('textarea[name="Reason[' + key + ']"]').val(value);
                }
                if (key == 'type') {
                    $form.find('select[name="Reason[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="Reason[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

// Khóa
reason.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
reason.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-reason-form").attr("action", $("#lock-reason-form").attr("action") + '?' + params);
    }
    $("#lock-reason-form").submit();
};

// Mở khóa
reason.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
reason.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-reason-form").attr("action", $("#unlock-reason-form").attr("action") + '?' + params);
    }
    $("#unlock-reason-form").submit();
};
