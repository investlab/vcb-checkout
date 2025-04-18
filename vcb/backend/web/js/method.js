/**
 * 
 */

var method = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add_Method').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Method').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

// Show modal sửa nhóm phương thức
method.viewEditMethod = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-method-form');

            $.each(data, function (key, value) {
                if (key == 'transaction_type_id') {
                    $form.find('select[name="AddMethodForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'description') {
                    $form.find('textarea[name="AddMethodForm[' + key + ']"]').val(value);
                }
                $form.find('input[name="AddMethodForm[' + key + ']"]').val(value);
            });
            $('#Edit_Method').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

// Khóa nhóm phương thức
method.modalLockMethod = function (id, name) {
    $('#LockMethod').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockMName').innerHTML = name;
};
method.submitLockMethod = function () {
    var href = window.location.href;
    var form = $("#lock-method-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};

// Mở khóa nhóm phương thức
method.modalUnLockMethod = function (id, name) {
    $('#UnlockMethod').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockMName').innerHTML = name;
};
method.submitUnLockMethod = function () {
    var href = window.location.href;
    var form = $("#unlock-method-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};
