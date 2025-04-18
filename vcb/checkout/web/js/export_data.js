var exportData = {};
exportData.isStop = false;
exportData.url = '';

exportData.set = function(title, export_url) {
    exportData._init(title);
    exportData.showMessageWarning('Processing ...');
    $('#modal-export-data').modal('show');
    $('#modal-export-data').on('hidden.bs.modal', function () {
        exportData.stop();
    });
    exportData._getData(export_url);
    return false;
};

exportData._getData = function(url) {
    if (exportData.isStop == false) {
        exportData.url = url;
        $.get(url, function(data) {
            if (data) {
                var result = JSON.parse(data);
                if (result.error == '') {
                    if (result.type_url == 'get_data') {
                        exportData.showMessageWarning('Processing (processed ' + result.row_processed + ' row) ...');
                        window.setTimeout('exportData._getData("' + result.next_url + '");', 500);

                    } else {
                        exportData.showMessageWarning('Export file excel ...');
                        window.setTimeout('exportData._export("' + result.next_url + '");', 500);
                    }
                } else {
                    exportData.showMessageError(result.error);
                }
            }
        });
    }
};

exportData._export = function(url) {
    if (exportData.isStop == false) {
        $('#modal-export-data').modal('hide');
        document.location.href = url;

    }
};

exportData._checkIframeLoaded = function() {
    if ($('#iframe-export-data').length && $('#iframe-export-data').attr('readyState') == 'complete') {
        exportData.showMessageSuccess('Exported file excel');
        clearTimeout(exportData.check_iframe_loaded);
    }
};

exportData.stop = function() {
    exportData.isStop = true;
    if (exportData.url != '') {
        var url = exportData.url;
        url = url.replace(/option=get_data/, "option=clear_temp");
        $.get(url, function(data) {});
    }
};

exportData._init = function(title) {
    exportData.isStop = false;
    if (!$('#modal-export-data').length) {
        var html = '<div class="modal fade" id="modal-export-data" tabindex=-1 role=dialog aria-hidden=true>';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header">';
        html += '<button type="button" class="close" data-dismiss="modal" onclick="exportData.stop();" aria-hidden="true">&times;</button>';
        html += '<h4 class="modal-title">' + title + '</h4>';
        html += '</div>';
        html += '<div class="modal-body">';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        $('body').append(html);
    }
    if ($('#iframe-export-data').length) {
        $('#iframe-export-data').remove();
    }
};

exportData.showMessageWarning = function(message) {
    var html = '<div class="alert alert-warning"> ' + message + '</div>';
    $('#modal-export-data .modal-body').html(html);
};

exportData.showMessageSuccess = function(message) {
    var html = '<div class="alert alert-success">' + message + '</div>';
    $('#modal-export-data .modal-body').html(html);
    if ($('#iframe-export-data').length) {
        $('#iframe-export-data').remove();
    }
};

exportData.showMessageError = function(message) {
    var html = '<div class="alert alert-danger"><i class="glyphicon-warning-sign"></i>  ' + message + '</div>';
    $('#modal-export-data .modal-body').html(html);
};