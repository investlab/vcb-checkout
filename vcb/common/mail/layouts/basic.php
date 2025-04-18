<?php

use yii\helpers\Html;


/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<body>
<div style="max-width:650px;margin: 0 auto">
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tbody>
        <?php $this->beginBody() ?>

        <?= $content ?>

        <?php $this->endBody() ?>

        </tbody>
    </table>
</div>
</body>
</html>
<?php $this->endPage() ?>
