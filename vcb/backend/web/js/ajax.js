var function_init = null;


function setAjax() {
    $(document).ready(function () {
        setAjaxLink();
        setAjaxSelectBox();
    });
}
function setAjaxTarget() {
    if ($('.ajax-target').length == 0) {
        var html = "<div id=\"\" class=\"ajax-target modal fade\" role=\"dialog\"><div class=\"modal-dialog modal-lg\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button><h4 class=\"modal-title ajax-title\"></h4></div><div class=\"modal-body ajax-body\"></div></div></div></div>";
        $('body').append(html);
    }
}

function setAjaxLink() {
    $('a.ajax-link, a.ajax-modal-link').click(function () {
        setAjaxTarget();
        var url = $(this).attr('href') + '&rand=' + Math.random();
        //alert(url);
        if (typeof($(this).attr('data-init')) != 'undefined') {
            function_init = $(this).attr('data-init');
        }
        var $that = $(this);
        if ($(this).attr('confirm')) {
            if (confirm($(this).attr('confirm'))) {
                $.get(url, function (data) {
                    if (data) {
                        setAjaxResult(data, $that);
                        $('.ajax-target').modal('show');

                    }
                });
            }
        } else {
            $.get(url, function (data) {
                if (data) {
                    setAjaxResult(data, $that);
                    $('.ajax-target').modal('show');
                }
            });
        }
        return false;
    });
}
function setAjaxResult(data, $obj) {
    var $body = $(data).find('#ajax-result .ajax-body');
    if ($body.length) {
        $('.ajax-target .ajax-body').html($body.html());
    }
    var $title = $(data).find('#ajax-result .ajax-title');
    if ($title.length) {
        $('.ajax-target .ajax-title').html($title.html());
    }
    var $form = $('.ajax-target .ajax-body #ajax-form');
    if ($form.length) {
        setAjaxForm($form);
    }
    setAjaxSelectBox();
    if (function_init != null) {
        var callback = new Function('$this', function_init)
        callback($obj);
    }
}
function setAjaxForm($form) {
    $form.submit(function () {
        setAjaxTarget();
        /*if ($(this).find('.has-error').length != 0) {
         var $obj = $(this).find('.has-error:eq(0)');
         if (!$obj.hasClass('begindate') && !$obj.hasClass('datetimepaid')) {
         return false;
         }
         }*/
        if (typeof($(this).attr('method')) != 'undefined' && $(this).attr('method') == 'get') {
            $.get($form.attr('action'), $form.serialize(), function (data) {
                if (data) {
                    setAjaxResult(data, $form);
                }
            });
        } else {
            $.post($form.attr('action'), $form.serialize(), function (data) {
                if (data) {
                    setAjaxResult(data, $form);
                }
            });
        }
        return false;
    });
    setAjaxFormDatePicker($form);
    setAjaxFormValidation($form);
}
function setAjaxFormValidation($form) {
    var $inputs = $form.find('input.form-control, select.form-control, textarea.form-control');
    if ($inputs.length) {
        $inputs.each(function (index) {
            $(this).bind('focus', function () {
                $(this).next('.help-block').html('');
                $(this).parent('.form-group').removeClass('has-error');
            });
        });
    }
}
function setAjaxFormDatePicker($form) {
    textUtils.moneyFormat('input_amount');
    $form.find(".begindate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        startDate: new Date(),
        format: 'dd-mm-yyyy hh:ii',
        yearRange: '-70y:c+nn',
        pickerPosition: 'top-right'
    });
    $form.find(".datetimepaid").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd-mm-yyyy hh:ii',
        maxDate: '0',
        yearRange: '-70y:c+nn',
        pickerPosition: 'top-right'
    });
    $form.find(".cpdbegindate").datetimepicker({
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        format: 'dd/mm/yyyy-hh:ii',
        yearRange: '-70y:c+nn'
    });
}
function setAjaxSelectBox() {
    $('.ajax-selectbox').change(function () {
        setAjaxSelectBoxChange($(this), $(this).val());
    });
}
function setAjaxSelectBoxChange($select, value) {
    var url = $select.attr('url');
    var target = $select.attr('target');
    $select.attr('disabled', true);
    $.get(url + '&' + $select.attr('name') + '=' + value, function (data) {
        $select.attr('disabled', false);
        if (data) {
            var $result = $(data).find(target);
            if ($result.length) {
                var $target = $(target);
                if ($target.length) {
                    $target.html($result.html());
                    if ($target.hasClass('ajax-selectbox')) {
                        setAjaxSelectBoxChange($target, $target.val())
                    } else {
                        if ($target.find('.ajax-selectbox').length) {
                            $target.find('.ajax-selectbox').change(function () {
                                setAjaxSelectBoxChange($(this), $(this).val());
                            });
                        }
                    }
                }
            }
        }
    });
}
setAjax();