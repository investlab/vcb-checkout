<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\components\widgets\HeaderWidget;
use backend\components\widgets\FooterWidget;
use backend\components\widgets\LeftMenuWidget;


AppAsset::register($this);

$this->beginPage();
?>

    <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <base href="<?php echo Yii::$app->urlManager->baseUrl;?>/" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta name="copyright" content="2017 (c) SEAPAY" />
        <meta name="robots" content="noindex" />
        <meta name="author" content="SEAPAY"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="refresh" content="3600"/>

        <link rel="icon" href="<?php echo Yii::$app->urlManager->baseUrl;?>/favicon.ico" type="image/x-icon" />
        <link rel="icon" href="" type="image/x-icon" />
        <meta name="msapplication-TileColor" content="#3399cc" />
        <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
        }
        </style>
        <?php echo Html::csrfMetaTags(); ?>
        <title><?php echo Html::encode($this->title); ?></title>
        
        <?php $this->head() ?>

    </head>
    <body>
    <?php $this->beginBody() ?>
    <div id="header"><?php echo HeaderWidget::widget(); ?></div>
    <div id="sidebar"><?php echo LeftMenuWidget::widget(); ?></div>
    <div id="content">
        <?php echo $content;?>
        <?php echo FooterWidget::widget(); ?>
    </div>
    <?php $this->endBody() ?>
    <script type="text/javascript">
        <?= $this->context->staticClient; ?>
    </script>
    </body>
    </html>
<?php $this->endPage() ?>