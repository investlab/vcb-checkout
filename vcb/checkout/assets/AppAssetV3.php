<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace checkout\assets;

use yii\bootstrap4\BootstrapAsset;
use yii\web\AssetBundle;

class AppAssetV3 extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/bootstrap.min.css',
        'css/font-awesome.css',
        'css/flag-icon.min.css',
       // 'css/version_1_1.css?v=1.1',
       // 'css/hackie.css?v=1.1',

        'dist/css/bootstrap.css',
        'dist/css/style.css',
        'dist/css/line-awesome.css',
        'dist/css/responsive.css',
        'dist/css/site.css',
        'dist/css/custom-responsive.css',
    ];
    public $js = [
        //'js/jquery-3.5.0.min.js',
        //'js/bootstrap3.3.7.min.js',
        //'js/bootstrap-datetimepicker.js',
        //'js/ajax.js',
        //'js/functions.js',
        //'js/sale-app.js',
        //'js/version_1_0.js?v=1.1',
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js',
        'dist/js/bootstrap.js',
        'dist/js/checkout.js',
        'https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.0.2/cleave.min.js',
        'dist/js/style.js',
    ];
    public $depends = [
//        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
//        'position' => \yii\web\View::POS_HEAD
    ];
}
