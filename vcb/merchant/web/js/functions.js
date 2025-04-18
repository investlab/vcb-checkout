
$(function () {
    textUtils.numberFormat('input_number');
    textUtils.moneyFormat('input_amount');

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

    var now = new Date();
    var a = new Date(now.getTime() + (3600 * 1000));

    $(".close-menu").click(function () {
        $(".leftMenu").css("display", 'none');
        return false;
    });
});
function showMMobile() {
    if ($(".leftMenu").css("display") == 'none')
        $(".leftMenu").css("display", 'block');
    else if ($(".leftMenu").css("display") == 'block')
        $(".leftMenu").css("display", 'none');
    return false;
}
function getDistrictByZoneId(zone_id, district_id) {
    var zoneId = $('#cus_zone_id').val();
    var districtId = $('#cus_district_id').val();
    if (districtId == 0) {
        districtId = district_id;
    }
    if (zoneId == 0) {
        zoneId = zone_id;
    }
    var url = $('#url-district-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        $('#cus_district_id').html(data);
        $('#cus_wards_id').val('00');
    });
    return false;
}
;
function getDistrictByZoneIdInForm(zone_id, district_id, form) {
    var zoneId = form.find('#cus_zone_id').val();
    var districtId = form.find('#cus_district_id').val();
    if (districtId == 0) {
        districtId = district_id;
    }
    if (zoneId == 0) {
        zoneId = zone_id;
    }
    var url = $('#url-district-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        form.find('#cus_district_id').html(data);
        form.find('#cus_wards_id').val('00');
    });
    return false;
}
;
function getWardsByDistrictId(wards_id, district_id) {
    var wardsId = $('#cus_wards_id').val();
    var districtId = $('#cus_district_id').val();
    if (wardsId == 0) {
        wardsId = wards_id;
    }
    if (districtId == 0) {
        districtId = district_id;
    }
    var url = $('#url-wards-ajax').html();
    $.get(url, {wards_id: wardsId, district_id: districtId}, function (data) {
        $('#cus_wards_id').html(data);
    });
    return false;
}
;
function getWardsByDistrictIdInForm(wards_id, district_id, form) {
    var wardsId = form.find('#cus_wards_id').val();
    var districtId = form.find('#cus_district_id').val();
    if (wardsId == 0) {
        wardsId = wards_id;
    }
    if (districtId == 0) {
        districtId = district_id;
    }
    var url = $('#url-wards-ajax').html();
    $.get(url, {wards_id: wardsId, district_id: districtId}, function (data) {
        form.find('#cus_wards_id').html(data);
    });
    return false;
}
;
function getDistrictByZoneIdWithForm(form) {

    var zoneId = form.find('#zone_id').val();
    var districtId = form.find('#district_id').val();
    var url = $('#url-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        //console.log(data);
        form.find('#district_id').html(data);
    });

    return false;

}
;

function showLoading() {

    var loadingDiv = $('#loadingDiv');
    loadingDiv.modal();

}
;
function hideLoading() {
    var loadingDiv = $('#loadingDiv');
    loadingDiv.modal('hide');


}
;

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
}
;



function getInfoStatus() {
    var $form = $('#update-access-status-form');
    var status = $('#status').val();

    var url = $('#url-get-info-access-status').html();
    $.get(url, {status: status}, function (data) {
        $('#info-access-status').html(data);
    });
    return false;
}
;

function makeCurrency(value) {
    value = '' + value;
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

function getSearchDistrictByZoneId(zone_id, district_id) {
    var zoneId = $('#customer_zone_id').val();
    var districtId = $('#customer_district_id').val();
    if (districtId == 0) {
        districtId = district_id;
    }
    var url = $('#url-search-district-ajax').html();
    $.get(url, {zone_id: zoneId, district_id: districtId}, function (data) {
        $('#customer_district_id').html(data);
    });
    return false;
}
;


function setTableCheckAll() {
    $(document).ready(function () {
        $('.table-check-all th input:checkbox').change(function () {
            $('.table-check-all td input:checkbox').prop('checked', this.checked);
        });
        $('.table-check-all td input:checkbox').change(function () {
            if (this.checked) {
                if (!$('.table-check-all td input:checkbox:not(:checked)').length) {
                    $('.table-check-all th input:checkbox').prop('checked', this.checked);
                }
            } else {
                $('.table-check-all th input:checkbox').prop('checked', this.checked);
            }
        });
        $('a.operator-check-all').click(function (event) {
            if ($('.table-check-all').length) {
                if (!$('.table-check-all td input:checkbox:checked').length) {
                    alert('You don\'t select the processing to line');
                    event.stopImmediatePropagation();
                } else {
                    var url = $(this).attr('href');
                    var title = $(this).html();
                    $('#confirm-dialog .title').html(title);
                    $('#confirm-dialog .alert').html($(this).attr('confirm'));
                    $('#confirm-dialog').modal('show');
                    $('#confirm-dialog .btn-accept').click(function () {
                        $('#form_list').attr('action', url);
                        $('#form_list').submit();
                    });
                }
            }
            return false;
        });
    });
}
setTableCheckAll();
function setMultiSelect() {
    $(document).ready(function () {
        //var select = $('select:not(.noStyle)');
        var select = $('select.selectpicker');
        if (select.length > 0) {
            select.selectpicker();
        }
    });

}
setMultiSelect();
