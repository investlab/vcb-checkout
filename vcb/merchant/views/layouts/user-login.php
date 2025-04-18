<?php

use yii\helpers\Html;
use merchant\assets\AppAsset;
use merchant\components\widgets\HeaderWidget;
use merchant\components\widgets\FooterWidget;
use common\components\utils\Translate;
AppAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <base href="<?php echo Yii::$app->urlManager->baseUrl; ?>/" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="refresh" content="3600"/>

        <link rel="icon" href="<?php echo Yii::$app->urlManager->baseUrl; ?>/images/ico/favicon.ico " type="image/x-icon"/>
        <link rel="icon" href="" type="image/x-icon"/>
        <meta name="msapplication-TileColor" content="#3399cc"/>
        <?php echo Html::csrfMetaTags(); ?>
        <title><?php echo Html::encode(Translate::get($this->title)); ?></title>
        <?php $this->head() ?>
    </head>
    <body id="login">
        <?php $this->beginBody() ?>
        <?php echo HeaderWidget::widget(); ?>
        <div id="wrapbody-login">
            <?php echo $content; ?>
            <?php echo FooterWidget::widget(); ?>
        </div>        
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>