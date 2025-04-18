$(document).ready(function () {
    $('#Edit_Keyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#LockKeyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#UnlockKeyword').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});

var keyword_js = {};

keyword_js.modalLock = function (id, name) {
    $('#LockKeyword').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lock_Keyword').innerHTML = name;
};

keyword_js.modalActive = function (id, name) {
    $('#UnlockKeyword').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unlock_Keyword').innerHTML = name;
};

keyword_js.submitForm = function (from) {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        from.attr("action", from.attr("action") + '?' + params);
    }
    from.submit();
};

// Show modal sửa ngân hàng
keyword_js.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-keyword-form');

            $.each(data, function (key, value) {
                $form.find('input[name="KeywordForm[' + key + ']"]').val(value);
            });
            $('#Edit_Keyword').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};