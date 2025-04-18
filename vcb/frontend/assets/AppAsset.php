<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/font-awesome.css',
        'css/flag-icon.min.css',
        'css/hackie.css?v=1.1',
        'css/custom.css'
    ];
    public $js = [
        'js/jquery.min.js',
        'js/bootstrap.min.js',
        //'js/bootstrap-datetimepicker.js',
        //'js/ajax.js',
        //'js/functions.js',
        //'js/sale-app.js',
        'js/frontend.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];
}
