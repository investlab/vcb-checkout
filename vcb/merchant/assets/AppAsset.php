<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace merchant\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/taskbar.css?v=2.1',
        'css/header.css?v=3.2',
        'css/bootstrap.min.css?v=3',
//        'css/bootstrap.css',
        'css/style-ie.css?v=2',
        'css/datepicker.css?v=2',
//        'css/main.min.css',
        'css/jquery-ui.css?v=2',
        'css/flag-icon.min.css',
        'css/select2.min.css',
    ];
    public $js = [

        'js/jquery-3.5.0.min.js',
        'js/bootstrap3.3.7.min.js',
//        'js/bootstrap-datepicker.js',
        'js/functions.js?v=1.1',
 //       'js/datepicker-vi.js',
        'js/jquery-ui.min.js',
        'js/textUtils.js',
        'js/export_data.js',
        'js/select2.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];

}
