<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components\libs;

use yii\web\AssetBundle;
use yii\web\View;
use common\components\libs\FileMergerContent;

class MTQAsset extends AssetBundle
{

    const MERGE_JS = false;
    const MERGE_CSS = false;

    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];

    public function registerAssetFiles($view)
    {
        $manager = $view->getAssetManager();
        if (self::MERGE_JS && ($file_name = $this->_getMergeFileName($view, $this->js, 'js')) != false) {
            $view->registerJsFile($manager->getAssetUrl($this, 'assets/js/' . $file_name), $this->jsOptions);
        } else {
            foreach ($this->js as $js) {
                if (is_array($js)) {
                    $file = array_shift($js);
                    $options = ArrayHelper::merge($this->jsOptions, $js);
                    $view->registerJsFile($manager->getAssetUrl($this, $file), $options);
                } else {
                    $view->registerJsFile($manager->getAssetUrl($this, $js), $this->jsOptions);
                }
            }
        }
        if (self::MERGE_CSS && ($file_name = $this->_getMergeFileName($view, $this->css, 'css')) != false) {
            $view->registerCssFile($manager->getAssetUrl($this, 'assets/css/' . $file_name), $this->cssOptions);
        } else {
            foreach ($this->css as $css) {
                if (is_array($css)) {
                    $file = array_shift($css);
                    $options = ArrayHelper::merge($this->cssOptions, $css);
                    $view->registerCssFile($manager->getAssetUrl($this, $file), $options);
                } else {
                    $view->registerCssFile($manager->getAssetUrl($this, $css), $this->cssOptions);
                }
            }
        }
    }

    protected function _getMergeFileName($view, $files, $file_type)
    {
        $module_id = $view->context->module->id;
        $dir_path = ROOT_PATH . DS . $module_id . DS . 'web' . DS;
        $file_paths = array();
        foreach ($files as $file) {
            $file_paths[] = $dir_path . DS . $file;
        }
        $file_name = FileMergerContent::getFileMergerName($dir_path . 'assets' . DS . $file_type . DS, $file_type, $file_paths);
        return $file_name;
    }
}


