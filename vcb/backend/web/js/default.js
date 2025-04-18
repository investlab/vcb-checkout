var defaults = {};
// $(document).ready(function () {
//     $('#change_pass').on('hidden.bs.modal', function () {
//         $(this).find('form')[0].reset();
//     });
// });

$(document).ready(() => {
    var width_nav = $('#header ul.navbar-nav>li.dropdown>a').width() + 40;
    $('#header ul.navbar-nav>li.dropdown').width(width_nav);
});