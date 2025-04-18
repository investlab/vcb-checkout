var window_height = $(window).height();
$('#sideNav>li').click(function () {
    var sidebar_height = 0;
    var main_height = $('#content>div').height();
    $('#sideNav>li').each(function (index, value) {
        sidebar_height += $(this).height();
    });

    if (main_height > sidebar_height && main_height > window_height) {
        $('#sidebar').css('min-height', main_height + 40);
    } else if(window_height > main_height && window_height > sidebar_height) {
        $('#sidebar').css('min-height', window_height + 40);
    } else {
        $('#sidebar').css('min-height', sidebar_height + 40);
    }
    $('#sidebar').removeClass('sidebar-fixed');
});