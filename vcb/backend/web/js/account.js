/**
 * .
 */

$(document).ready(function () {

    // khi modal edit đóng thì tất cả input ở modal add reset
    $('#Edit_Account').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Add_Account').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});
var account = {};

account.submitForm = function (form) {
    //var $form = $('#add-account-form');
    var balance = form.find('input[name="AddAccountForm[balance]"]').val();
    if ((typeof balance == 'undefined') || balance.trim() == "") {
        balance = 0;
    } else {
        balance = balance.replace(/\./g, '');
    }

    form.find('input[name="AddAccountForm[balance]"]').val(balance);

    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};

// Nạp tiền
account.modalAddBalance = function (id, name) {
    $('#AddBalance').modal('show');
    $('input[name="AddAccountForm[id]"]').val(id);
    $('input[name="AddAccountForm[name]"]').val(name);
    document.getElementById('addAccName').innerHTML = name;
};

// Khóa tài khoản
account.modalLockAcc = function (id, name) {
    $('#LockAcc').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockAccName').innerHTML = name;
};

account.submitLockAcc = function () {
    var href = window.location.href;
    var form = $("#lock-acc-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};

// Mở khóa tài khoản
account.modalUnLockAcc = function (id, name) {
    $('#UnlockAcc').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockAccName').innerHTML = name;
};

account.submitUnLockAcc = function () {
    var href = window.location.href;
    var form = $("#unlock-acc-form");
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        form.attr("action", form.attr("action") + '?' + params);
    }
    form.submit();
};

// Show modal sửa tài khoản
account.viewEditAccount = function (id, url) {

    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-account-form');

            $.each(data, function (key, value) {
                $form.find('select[name="AddAccountForm[' + key + ']"]').val(value);
                $form.find('input[name="AddAccountForm[' + key + ']"]').val(value);
            });
            $('#Edit_Account').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};