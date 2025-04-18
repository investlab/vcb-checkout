function setAjax() {
    $(document).ready(function() {
        setAjaxLink();
        setAjaxSelectBox();
    });
}
function setAjaxLink() {
    $('a.ajax-link').click(function() {
        var url = $(this).attr('href')+'&rand='+Math.random();
        var target = $(this).attr('target');
        $.get(url, function(data) {
            if (data) {
                setAjaxResult(data, target);
            }
        });  
        return false;
    });
}
function setAjaxSelectBox() {
    $('.ajax-selectbox').change(function() {
        setAjaxSelectBoxChange($(this), $(this).val());
    });
}
function setAjaxSelectBoxChange($select, value) {
    var url = $select.attr('url');
    var target = $select.attr('target');
    $select.attr('disabled', true);
    $.get(url + '&'+$select.attr('name')+'='+ value, function(data) {
        $select.attr('disabled', false);
        if (data) {
            setAjaxResult(data, target);
        }
    });
}
function setAjaxResult(data, target) {
    var $result = $(data).find(target);
    if ($result.length) {
        var $target = $(target);
        if ($target.length) {
            $target.html($result.html());
            if ($target.hasClass('ajax-selectbox')) {
                setAjaxSelectBoxChange($target, $target.val())
            } else {
                if ($target.find('.ajax-selectbox').length) {
                    $target.find('.ajax-selectbox').change(function() {
                        setAjaxSelectBoxChange($(this), $(this).val());
                    });
                }
            }
        }
    }
}
setAjax();