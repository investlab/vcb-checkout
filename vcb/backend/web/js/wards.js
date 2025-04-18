var wards = {};
// reset các form khi đóng modal
$(document).ready(function () {

    $('#Add').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    $('#Edit').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

});


wards.getDistrictByCityID = function (city_id, district_id, form) {
    var cityId = form.find('#city_id').val();
    var districtId = form.find('#district_id').val();
    if (districtId == 0) {
        districtId = district_id;
    }
    if (cityId == 0) {
        cityId = city_id;
    }
    var url = $('#url-district-ajax').html();
    $.get(url, {city_id: cityId, district_id: districtId}, function (data) {
        form.find('#district_id').html(data);
    });
    return false;
}

wards.getDistrictByCityIDSearch = function () {
    var cityId = $('#city_id_search').val();
    var url = $('#url-district-search-ajax').html();
    $.get(url, {city_id: cityId}, function (data) {
        $('#district_id_search').html(data);
    });
    return false;

};

// Show modal sửa
wards.viewEdit = function (id, url) {
    $.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        url: url,
        data: 'id=' + id,
        dataType: "json",
        success: function (msg) {
            data = JSON.parse(msg);
            var $form = $('#edit-wards-form');

            $.each(data, function (key, value) {
                if (key == 'remote') {
                    $form.find('select[name="WardsUpdateForm[remote]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'parent_id') {
                    $form.find('select[name="WardsUpdateForm[parent_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                if (key == 'city_id') {
                    $form.find('select[name="WardsUpdateForm[city_id]"] option[value=' + value + ']').attr("selected", "selected");
                }
                $form.find('input[name="WardsUpdateForm[' + key + ']"]').val(value);
            });
            $('#Edit').modal('show');
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }

    });
};


// Kích hoạt
wards.modalActive = function (id, name) {
    $('#Active').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('activeBNumber').innerHTML = name;
};
wards.submitActive = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#active-wards-form").attr("action", $("#active-wards-form").attr("action") + '?' + params);
    }
    $("#active-wards-form").submit();
};

// Khóa
wards.modalLock = function (id, name) {
    $('#Lock').modal('show');
    $('input[name=id]').val(id);
    document.getElementById('lockBNumber').innerHTML = name;
};
wards.submitLock = function () {
    var href = window.location.href;
    var params = href.split('?')[1];
    if (typeof params !== "undefined") {
        $("#lock-wards-form").attr("action", $("#lock-wards-form").attr("action") + '?' + params);
    }
    $("#lock-wards-form").submit();
};


