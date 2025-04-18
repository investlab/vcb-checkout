$(function () {
    textUtils.numberFormat('input_number');
    $(".datepicker").datepicker({changeMonth: true, changeYear: true, dateFormat: 'dd-mm-yy', yearRange: '-70y:c+nn'});

    $(".datepickercontract").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-mm-yy',
        maxDate: '0',
        yearRange: '-70y:c+nn'
    });


    $("#daterangepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-mm-yy',
        yearRange: '-70y:c+nn'
    });
    $("#datepickerwithhours").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd/mm/yyyy-hh:ii',
        yearRange: '-70y:c+nn'
    });

    $(".datetimepaid").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd-mm-yyyy hh:ii',
        maxDate: '0',
        yearRange: '-70y:c+nn'
    });

    $("#begindate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd-mm-yyyy hh:ii',
        yearRange: '-70y:c+nn'
    });

    $(".begindate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        startDate: new Date(),
        format: 'dd-mm-yyyy hh:ii',
        yearRange: '-70y:c+nn'
    });

    $("#enddate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd/mm/yyyy-hh:ii',
        yearRange: '-70y:c+nn'
    });

    $(".editdiscountdate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd/mm/yyyy-hh:ii',
        yearRange: '-70y:c+nn'
    });
});
function getDistrictByZoneId(zone_id, district_id) {
    var zoneId = $('#cus_zone_id').val();
    var districtId = $('#cus_district_id').val();
    if(districtId == 0){
        districtId = district_id;
    }
    var url = $('#url-district-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        $('#cus_district_id').html(data);
    });
    return false;
};
function getDistrictByZoneIdWithForm(form) {

    var zoneId = form.find('#zone_id').val();
    var districtId = form.find('#district_id').val();
    var url = $('#url-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        //console.log(data);
        form.find('#district_id').html(data);
    });

    return false;

};

function showLoading() {

    var loadingDiv = $('#loadingDiv');
    loadingDiv.modal();

};
function hideLoading() {
    var loadingDiv = $('#loadingDiv');
    loadingDiv.modal('hide');


};

function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

// Lấy kho hàng theo NCC
function getInventoryBySupplier($supplier_id, $supplier_inventory_id,$bill_id) {
    var supplier_id = $('#supplier_id').val();
    var supplier_inventory_id = $('#supplier_inventory_id').val();
    var bill_id = $('#bill_id').val()
    var url = $('#url-get-inventory-ajax').html();
    var $form = $('#update-inventory-form');
    $.get(url, {supplier_id: supplier_id, supplier_inventory_id: supplier_inventory_id,bill_id : bill_id}, function (data) {
      var data = JSON.parse(data);
        $('#supplier_inventory_id').html(data.option);
        $('#infor-inventory').html(null);

         var price_buy = data.price_buy != '' ? 'Giá nhập : ' + makeCurrency(data.price_buy) + ' đ' : '' ;

        $form.find('span[id="up-inventory-buy-price"]').text(price_buy);

        var time_begin = data.time_begin != '' ? 'TG AD : ' + data.time_begin : '';
        $form.find('span[id="up-inventory-time-begin"]').text(time_begin);

        var discount = data.discount != '' ? 'Chiết khấu : ' + data.discount + ' %' : '' ;
        $form.find('span[id="up-inventory-discount"]').text(discount);
    });
    return false;
};

// Lấy thông tin kho hàng
function getInforInventory($supplier_inventory_id){
    var supplier_inventory_id = $('#supplier_inventory_id').val();
    var url = $('#url-get-infor-inventory-ajax').html();
    $.get(url, {supplier_inventory_id: supplier_inventory_id}, function (data) {
        $('#infor-inventory').html(data);
    });
    return false;
};
// Lấy kì trả góp theo ngân hàng
function getInstallmentBankPeriod($installment_bank_id){
    var installment_bank_id = $('#installment_bank_id').val();
    var url = $('#url-get-installment-period-ajax').html();
    $.get(url, {installment_bank_id: installment_bank_id}, function (data) {
        $('#installment_period').html(data);
    });
    return false;
};

// Lấy thông tin kho hàng
function getInfoProduct(){
    var $form = $('#add-supplier-product');
    var supplier_id = $('#supplier_id').val();

    var product_code = $form.find('input[name="SupplierProductPriceForm[product_code]"]').val();

    var url = $('#url-get-info-product-ajax').html();
    $.get(url, {supplier_id : supplier_id ,product_code: product_code}, function (data) {
        $('#info-product').html(data);
    });
    return false;
};

// Lấy doanh nghiệp theo  khách hàng
function getOrganizationByCustomer(){
    var $form = $('#create-preorder-form');
    var customer_id = $('#customer_id').val();

    var url = $('#url-get-organization-by-customer-ajax').html();
    $.get(url, {customer_id : customer_id}, function (data) {
        $('#organization_list').html(data);
    });
    return false;
};

function makeCurrency(value) {
    value = ''+value;
    var result = '';
    var index = 1;
    var start = value.length - index * 3;
    if (start > 0) {
        while (start > 0) {
            result = ',' + value.substring(start, start + 3) + result;
            index++;
            start = value.length - index * 3;
        }
        result = value.substring(start, start + 3) + result;
    } else {
        result = value;
    }
    return result;
}
