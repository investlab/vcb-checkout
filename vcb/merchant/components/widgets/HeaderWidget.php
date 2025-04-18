<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace merchant\components\widgets;

use Yii;
use yii\helpers\Url;
use yii\base\Widget;
use yii\helpers\Html;

class HeaderWidget extends Widget
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $params = array_merge([Yii::$app->controller->id.'/'.Yii::$app->controller->action->id], Yii::$app->request->get());
        $params_en = array_merge($params, ['language' => 'en']);
        $params_vi = array_merge($params, ['language' => 'vi']);
        $url_en = Yii::$app->urlManager->createAbsoluteUrl($params_en);
        $url_vi = Yii::$app->urlManager->createAbsoluteUrl($params_vi);
        return $this->render('header_widget', [
            'url_en' => $url_en,
            'url_vi' => $url_vi,
        ]);
    }
}