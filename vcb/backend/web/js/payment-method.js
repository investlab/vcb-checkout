$(document).ready(function () {

    // khi modal edit đóng thì tất cả input ở modal add reset
    $('#Lock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Unlock').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});

var paymentmethod = {};
// Khóa
paymentmethod.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
paymentmethod.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-paymentmethod-form").attr("action", $("#lock-paymentmethod-form").attr("action") + '?' + params);
    }
    $("#lock-paymentmethod-form").submit();
};

//Mở khóa
paymentmethod.modalUnLock = function (id,name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
paymentmethod.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-paymentmethod-form").attr("action", $("#unlock-paymentmethod-form").attr("action") + '?' + params);
    }
    $("#unlock-paymentmethod-form").submit();
};

