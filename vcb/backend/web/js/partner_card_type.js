/**
 
 */
var partner_card_type = {};

partner_card_type.modalLock = function (id,name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
partner_card_type.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-partner-card-type-form").attr("action", $("#lock-partner-card-type-form").attr("action") + '?' + params);
    }
    $("#lock-partner-card-type-form").submit();
};


partner_card_type.modalActive = function (id,name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
partner_card_type.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-partner-card-type-form").attr("action", $("#active-partner-card-type-form").attr("action") + '?' + params);
    }
    $("#active-partner-card-type-form").submit();
};


