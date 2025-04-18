/**
 * 
 */

var cashout = {};
cashout.changeMethod = function(obj) {
    var payment_method_id = obj.value;
    var url = $(obj).attr('data-url');
    document.location.href = url + '?payment_method_id=' + payment_method_id;

};
