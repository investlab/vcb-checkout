var user = {};

// Khóa
user.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
user.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-user-form").attr("action", $("#lock-user-form").attr("action") + '?' + params);
    }
    $("#lock-user-form").submit();
};

// Mở khóa
user.modalUnLock = function (id, name) {
    $('#Unlock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('unLockBNumber').innerHTML = name;
};
user.submitUnLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#unlock-user-form").attr("action", $("#unlock-user-form").attr("action") + '?' + params);
    }
    $("#unlock-user-form").submit();
};

user.modalReset = function (id, name) {
    $('#Reset').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('resetBNumber').innerHTML = name;
};
user.submitReset = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#user-reset-form").attr("action", $("#user-reset-form").attr("action") + '?' + params);
    }
    $("#user-reset-form").submit();
};

user.createSales = function (type) {
    var sale_channel_id = $('#sale_channel').val();
    var sale_channel_name = $('#sale_channel_name').val();
    var sale_channel_user_id = $('#sale_channel_user').val();
    var sale_channel_user_name = $('#sale_channel_user_name').val();
    if (typeof sale_channel_user_name == "undefined") {
        sale_channel_user_name = '';
    }
    var obj_name = 'UserAddForm';
    if (type == 'UPDATE') {
        obj_name = 'UserUpdateForm';
    }
    //console.log(obj_name);
    var channel = $('#sale_channel_' + sale_channel_id).val();
    if (typeof channel == 'undefined' || channel == '') {
        var html = '<tr>' +
            '<td>' + sale_channel_name + '<input id="sale_channel_' + sale_channel_id + '" type="hidden" value="' + sale_channel_id + '" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][sale_channel_id]"/>' +
            '</td>' +
            '<td>' + sale_channel_user_name + '<input type="hidden" value="' + sale_channel_user_id + '" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][parent_id]"/>' + '</td>' +
            '<td>' +
            '<input class="noStyle default-radio" id="default_' + sale_channel_id + '" type="radio" name="' + obj_name + '[sale_channel][' + sale_channel_id + '][default]" value="0" onchange="user.changeDefault(' + sale_channel_id + ');" >' +
            '</td>' +
            '<td>' +
            '<button class="btn btn-success" type="button"' +
            'onclick="user.removeChannel(this);">Xóa</button>' +
            '</td>' +
            '</tr>';
        //console.log(n);
        $('#sales_items').append(html);
    }

    $('#ajax-dialog').modal('hide');
    $('.ajax-target').modal('hide');
    //return false;
};

user.removeChannel = function (obj) {
    $(obj).parent().parent().remove();
};

user.changeDefault = function (sale_channel_id) {
    var id_default = 'default_' + sale_channel_id;
    $('.default-radio').each(function () {
        if (this.id != id_default) {
            this.value = 0;
            this.checked = false;
        } else {
            this.value = 1;
            this.checked = true;
        }

    });
    //$('#default_' + sale_channel_id).val(1);
    //$('#default_' + sale_channel_id).attr('checked', true);
};

user.viewUpdateAdminAccount = function (id, group_id, name, status) {
    var $form = $('#update-user-admin-account-form');
    $form.find('input[name="UserAdminAccountForm[name]"]').val(name);
    $form.find('input[name="UserAdminAccountForm[id]"]').val(id);
    $form.find('select[name="UserAdminAccountForm[user_group_id]"]').val(group_id);
    $form.find('select[name="UserAdminAccountForm[status]"]').val(status);
    $('#modal-update-account').modal('show');
};

// Khóa
user.modalDefaultChannel = function (id, user_id, admin_account_id) {
    $('#DefaultChannel').modal('show');
    $('input[name=default_id]').val(id);
    $('input[name=default_user_id]').val(user_id);
    $('input[name=default_admin_account_id]').val(admin_account_id);
};

// Mở khóa
user.modalDeleteChannel = function (id, user_id, admin_account_id) {
    $('#DeleteChannel').modal('show');
    $('input[name=delete_id]').val(id);
    $('input[name=delete_user_id]').val(user_id);
    $('input[name=delete_admin_account_id]').val(admin_account_id);
};

user.getCreditPartnerBranch = function (cp_id, branch_ids, admin_account_id, view) {
    var form = $('#add-branch-admin-account-form');
    var credit_partner_id = form.find('select[name="credit_partner_id"]').val();
    if (admin_account_id > 0) {
        form.find('input[name="admin_account_id"]').val(admin_account_id);
    }
    if (view && cp_id > 0) {
        credit_partner_id = cp_id;
    }
    var url = $('#url-get-branch-by-partner-id-ajax').html();
    $.get(url, {credit_partner_id: credit_partner_id, branch_ids: branch_ids}, function (data) {
        if (data) {
            form.find('#branch_items').html(data);
        }
    });
};

user.getMtqInventory = function (inventory_ids, admin_account_id) {
    var form = $('#add-inventory-admin-account-form');
    form.find('input[name="admin_account_id"]').val(admin_account_id);
    var url = $('#url-get-mtq-inventory-ajax').html();
    $.get(url, {inventory_ids: inventory_ids}, function (data) {
        if (data) {
            form.find('#mtq_inventory_items').html(data);
        }
    });
};