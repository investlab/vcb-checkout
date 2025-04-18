<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;

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

        <link rel="icon" href="<?php echo Yii::$app->urlManager->baseUrl;?>/favicon.ico " type="image/x-icon" />
        <link rel="icon" href="" type="image/x-icon" />
        <meta name="msapplication-TileColor" content="#3399cc" />
        <?php echo Html::csrfMetaTags(); ?>
        <title><?php echo Html::encode($this->title); ?></title>
        <?php $this->head() ?>

    </head>
    <body class=login-page>
        <?php $this->beginBody() ?>
        <!-- Start #login -->
        <div id=login class="animated bounceIn">
            <img id="logo" src="<?=ROOT_URL?>logo.png" alt="sprFlat Logo" width="150">
                 <!-- Start .login-wrapper -->
                 <div class=login-wrapper>
                    <div class="tab-pane fade active in pt10">
                        <?php echo $content; ?>
                    </div>
                </div>
                <!-- End #.login-wrapper -->
        </div>


        <?php $this->endBody() ?>
        <script type="text/javascript">

        </script>
    </body>
</html>
<?php $this->endPage() ?>