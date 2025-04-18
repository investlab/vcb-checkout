$(document).ready(function () {

    $('#AddGroup').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

var usergroup = {};
// Khóa
usergroup.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
usergroup.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-user-group-form").attr("action", $("#lock-user-group-form").attr("action") + '?' + params);
    }
    $("#lock-user-group-form").submit();
};

// Mở khóa
usergroup.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
usergroup.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-user-group-form").attr("action", $("#unlock-user-group-form").attr("action") + '?' + params);
    }
    $("#unlock-user-group-form").submit();
};


usergroup.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-user-group-form');

            $.each(data, function (key, value) {
                if (key == 'parent_id') {
                    if (value != null) {
                        $form.find('select[name="UserGroup[' + key + ']"]').val(value);
                    } else {
                        $form.find('select[name="UserGroup[' + key + ']"]').val('0');
                    }

                }
                $form.find('input[name="UserGroup[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};