textUtils = {};

textUtils.formatTime = function (time, format) {
    var months = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
    time = parseFloat(time);
    var a = new Date(time);
    var year = a.getFullYear();
    var month = months[a.getMonth()];
    var day = a.getDate();
    var hour = a.getHours();
    var minute = a.getMinutes();
    var second = a.getSeconds();
    var time = "";
    if (format === 'day') {
        time = day + "/" + month + "/" + year;
    } else if (format === 'hour') {
        time = hour + ":" + minute + " " + day + "/" + month + "/" + year;
    } else if (format === 'time') {
        time = day + "/" + month + "/" + year + " " + hour + ":" + minute + ":" + second;
    } else if (format === 'hourfirst') {
        time = hour + ":" + minute + ":" + second + " " + day + "/" + month + "/" + year;
    }
    return time;
};

Number.prototype.toMoney = function (decimals, decimal_sep, thousands_sep) {
    var n = this,
        c = isNaN(decimals) ? 2 : Math.abs(decimals), //if decimal is zero we must take it, it means user does not want to show any decimal
        d = decimal_sep || '.', //if no decimal separator is passed we use the dot as default decimal separator (we MUST use a decimal separator)

        t = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, //if you don't want to use a thousands separator you can pass empty string as thousands_sep value

        sign = (n < 0) ? '-' : '',
    //extracting the absolute value of the integer part of the number and converting to string
        i = parseInt(n = Math.abs(n).toFixed(c)) + '',
        j = ((j = i.length) > 3) ? j % 3 : 0;
    return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
};

textUtils.inputNumberFormat = function (cssClass) {
    $.each($('input.' + cssClass), function () {
        var num = this;
        $(this).on("keydown", function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 116]) !== -1 ||
                    // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            } else {
                e.preventDefault();
            }
        });
        $(this).on("keyup", function (e) {
            var val = $(num).val().replace(/\./g, '');
            if (val != '' && !isNaN(val)) {
                $(num).val(parseFloat(val).toMoney(0, ',', '.'));
            } else {
                $(num).val(0);
            }
        });

    });
};

textUtils.numberFormat = function (cssClass) {
    $.each($('input.' + cssClass), function () {
        var num = this;
        $(this).on("keydown", function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 116, 109, 189]) !== -1 ||
                    // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            } else {
                e.preventDefault();
            }
        });
        $(this).on("keyup", function (e) {
            var val = $(num).val().replace(/\./g, '');
            if (val != '') {
                if (val.charAt(0) == '-') {
                    //console.log('sdsdsd2323');
                    var res = val.split("-");
                    if (typeof res[1] != "undefined" && res[1] != '') {
                        var str = '-' + parseFloat(res[1]).toMoney(0, ',', '.');
                        $(num).val(str);
                    }
                } else {
                    //console.log('sdsd');
                    $(num).val(parseFloat(val).toMoney(0, ',', '.'));
                }
            }
        });

    });
};

textUtils.moneyFormat = function (cssClass) {
    $.each($('input.' + cssClass), function () {
        var num = this;
        $(this).on("keydown", function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 116, 109, 189]) !== -1 ||
                    // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            } else {
                e.preventDefault();
            }
        });
        $(this).on("keyup", function (e) {
            var val = $(num).val().replace(/\,/g, '');
            if (val != '') {
                if (val.charAt(0) == '-') {
                    //console.log('sdsdsd2323');
                    var res = val.split("-");
                    if (typeof res[1] != "undefined" && res[1] != '') {
                        var str = '-' + parseFloat(res[1]).toMoney(0, '.', ',');
                        $(num).val(str);
                    }
                } else {
                    //console.log('sdsd');
                    $(num).val(parseFloat(val).toMoney(0, '.', ','));
                }
            }
        });

    });
};
