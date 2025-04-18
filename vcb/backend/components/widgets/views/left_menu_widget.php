<?php
use common\components\utils\Translate;

?>

<div class=sidebar-inner>
    <ul id=sideNav class="nav nav-pills nav-stacked">
        <li>
            <a <?php if (Yii::$app->controller->id == 'default') echo 'class=active' ?>
                href="home.html">
                <?= Translate::get('Trang chủ')?> <i class="im-home"></i>
            </a>
        </li>
        <?php 
        $controller = Yii::$app->controller->id;
        foreach ($rights as $right) :?>
        <li>
            <?php if (!empty($right['childs'])) :?>
            <a href="#"> <?= Translate::get(@$right['title'])?><i class="im-stack"></i></a>
            <ul class="nav sub">
                <?php foreach ($right['childs'] as $sub_right):?>
                <li>
                    <a <?php if ('/'.$controller == substr($sub_right['link'], -strlen('/'.$controller))): echo 'class=current'; endif; ?> href="<?=$sub_right['link']?>">
                        <i class="en-arrow-right7"></i> <p class="title-submenu"><?= Translate::get(@$sub_right['title'])?></p>
                    </a>
                </li>
                <?php endforeach;?>
            </ul>
            <?php else:?>
            <a href="<?= $right['link']?>" <?php if ('/'.$controller == substr($right['link'], -strlen('/'.$controller))): echo 'class=current'; endif; ?> > <?= $right['title']?><i class="im-stack"></i></a>
            <?php endif;?>
        </li>
        <?php endforeach;?>
    </ul>
</div>