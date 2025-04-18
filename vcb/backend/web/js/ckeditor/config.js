/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';

    //config.filebrowserBrowseUrl = 'http://mtq.local/nvkd/mtqv2/backend/web/js/ckfinder/ckfinder.html';
    config.filebrowserImageBrowseUrl = 'https://manhthuongquan.vn/v1.1/backend/web/js/ckfinder/ckfinder.html?type=Images';
    //config.filebrowserFlashBrowseUrl = 'http://mtq.local/nvkd/mtqv2/backend/web/js/ckfinder/ckfinder.html?type=Flash';
    //config.filebrowserUploadUrl = 'http://mtq.local/nvkd/mtqv2/backend/web/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = 'https://manhthuongquan.vn/v1.1/backend/web/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
    //config.filebrowserFlashUploadUrl = 'http://mtq.local/nvkd/mtqv2/backend/web/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};
