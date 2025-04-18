<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $sourcePath = '@bower/jquery/dist';
    public $css = [
        'css/bootstrap.css?v=1',
        'css/main.min.css?v=2.1',
        // 'css/captcha.css',
        // 'css/summary.css',
        'css/jquery-ui.css?v=1',
        'css/jquery.fancybox.css?v=1',
        //  'css/product_image.css',
        'css/bootstrap-select.min.css?v=1',
        'css/selectize/selectize.default.css?v=1',
        'css/daterangepicker.css?v=1',
        'css/mtq-style.css?v=1.4',
        'css/custom.css',
        'css/select2.min.css',
    ];
    public $js = [
        'js/jquery-ui.min.js?v=1',
        'js/datepicker-vi.js?v=1',
        'js/bootstrap-datetimepicker.js?v=1',
        'js/moment.min.js?v=1',
        'js/daterangepicker.js',
        'js/Chart.min.js',
        'js/textUtils.js?v=1',
        'js/ckeditor/ckeditor.js?v=1',
        'js/ckeditor/config.js?v=1',
        'js/ckfinder/ckfinder.js?v=1',
        'js/jquery.fancybox.js?v=1',
        'js/cropbox.js?v=1',
        'js/bootstrap-select.min.js?v=1',
        'js/selectize/selectize.min.js?v=1',
        'js/functions.js?v=1',
        'js/business.js?v=1',
        'js/export_data.js?v=1',
        'js/select2.min.js',
        'js/default.js',
        'js/merchant.js?v=1',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];

}
