/**
 * .
 */

var bank = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add_Bank').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit_Bank').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

// Show modal sửa ngân hàng Alego
bank.viewEditAlegoBank = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-alego-bank-form');

            $.each(data, function (key, value) {
                if (key == 'bank_id') {
                    $form.find('select[name="AlegoBankForm[' + key + ']"] option[value=' + value + ']').attr("selected", "selected");
                }

                $form.find('input[name="AlegoBankForm[' + key + ']"]').val(value);
            });
            $('#Edit_Alego_Bank').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

// Show modal sửa ngân hàng
bank.viewEditBank = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-bank-form');

            $.each(data, function (key, value) {
                if (key == 'description') {
                    $form.find('textarea[name="AddBankForm[' + key + ']"]').val(value);
                }
                $form.find('input[name="AddBankForm[' + key + ']"]').val(value);
            });
            $('#Edit_Bank').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};

// Khóa ngân hàng
bank.modalLockBank = function (id, name) {
    $('#LockBank').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
bank.submitLockBank = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-bank-form").attr("action", $("#lock-bank-form").attr("action") + '?' + params);
    }
    $("#lock-bank-form").submit();
};

// Mở khóa ngân hàng
bank.modalUnLockBank = function (id, name) {
    $('#UnlockBank').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
bank.submitUnLockBank = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-bank-form").attr("action", $("#unlock-bank-form").attr("action") + '?' + params);
    }
    $("#unlock-bank-form").submit();
};

