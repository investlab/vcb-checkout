<?php

use yii\helpers\Html;
use merchant\assets\AppAsset;
use merchant\components\widgets\HeaderMerchantWidget;
use merchant\components\widgets\FooterMerchantWidget;
use merchant\components\widgets\LeftMerchantWidget;
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

        <link rel="icon" href="<?= ROOT_URL ?>/merchant/web/images/ico/favicon.ico " type="image/x-icon"/>
        <link rel="icon" href="" type="image/x-icon"/>
        <meta name="msapplication-TileColor" content="#3399cc"/>
        <?php echo Html::csrfMetaTags(); ?>
        <title><?php echo Html::encode(Translate::get($this->title)); ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>
        <div class="mm-page nltop">
            <?php echo HeaderMerchantWidget::widget(); ?>
            <div id="wrapbody">
                <div class="col-xs-12 col-sm-1"></div>
                <div class="col-xs-12 col-sm-10 main clearfix">
                    <?php echo LeftMerchantWidget::widget(); ?>



                    <div class="breakCol">
                        <div class="container-fluid">
                            <?php echo $content; ?>


                            <?php echo FooterMerchantWidget::widget(); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-1"></div>
            </div>
        </div>
        <?php $this->endBody() ?>
        <script type="text/javascript">

        </script>
    </body>
</html>
<?php $this->endPage() ?>