// JavaScript Document
$(document).ready(function() {
    $('.panel-collapse').on('shown.bs.collapse', function() {
        scrollToTarget('#' + $(this).attr('id'), 50);
    });
    $(".error_message").alert();
    setBankSelect();
    $('.btn-loading').click(function() {
        $(this).button('loading');
    });
    $('.btn-view-order-info').click(function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $('#order-info').addClass('hidden-mobile');
        } else {
            $(this).addClass('active');
            $('#order-info').removeClass('hidden-mobile');
        }
    });

    var height_header = $('.row.mdevice>.col-sm-12:first-child').height();
    var height_main = $(window).height() - height_header;
    $('.row.mdevice .brdRight').css('min-height', height_main + 'px');
    $('#order-info').css('bottom', '-' + height_header + 'px');

    $('.btn-view-order-info').click(function () {
        if ($('div.btn-view-order-info').hasClass('active')) {
            var height_order_info = $('#order-info').height() + 5;
            $('#order-info').css('bottom', '-' + height_order_info + 'px');
        }
    });
        
    $("#pay-button").click(function() {
        var $btn = $(this);
        $btn.button('loading');
        // simulating a timeout
        setTimeout(function () {
            $btn.button('reset');
        }, 1000);
    });
});
function scrollToTarget(target, offset_top) {
    var $target = $(target);
    if ($target.length) {
        $('html,body').animate({scrollTop: $target.offset().top - offset_top}, 'quick');
    }
}
function setBankSelect() {
    $('.form_option').each(function(index) {
        $('.form_option:eq(' + index + ') .cardList i').click(function() {
            $('.form_option:eq(' + index + ') .payment-loading').removeClass('hide');
            $('.form_option:eq(' + index + ') .cardList').addClass('hide');
            /*var $form = $('.form_option:eq(' + index + ') .form-horizontal');
            $form.removeClass('hide');
            $form.find('#bank_code').attr('class', $(this).attr('class'));
            $form.find('#bank_title').html($(this).attr('title'));
            if ($('.form_option:eq(' + index + ') .payment_method_' + $(this).attr('class')).length) {
                $form.find('#payment_method_box').html($('.form_option:eq(' + index + ') .payment_method_' + $(this).attr('class')).html());
            }*/
        });
        /*$('.form_option:eq(' + index + ') .form-horizontal .backOpt').click(function() {
            $('.form_option:eq(' + index + ') .form-horizontal').addClass('hide');
            $('.form_option:eq(' + index + ') .cardList').removeClass('hide');
            return false;
        });*/
    });
}