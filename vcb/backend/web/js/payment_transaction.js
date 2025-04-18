/**
 * .
 */

$(document).ready(function () {
    $('#edit_cUser').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#edit_mReceipt').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});

var payment_transaction = {};

// Thay đổi người tạo
payment_transaction.viewEditcUser = function (form,id,user_created) {
    $('#edit_cUser').modal('show');
    form.find('#editpaymenttransactionform-id').val(id);
    form.find('#editpaymenttransactionform-user_create').val(user_created);
};

// Sửa mã tham chiếu
payment_transaction.viewEditmReceipt = function (form,id,partner_payment_method_receipt) {
    $('#edit_mReceipt').modal('show');
    form.find('#editpaymenttransactionform-id').val(id);
    form.find('#editpaymenttransactionform-partner_payment_method_receipt').val(partner_payment_method_receipt);
};