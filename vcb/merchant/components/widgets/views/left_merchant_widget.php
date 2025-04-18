<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
?>

<div class="leftMenu">
    <div id="navigation" class="lstMenu">
        <?php foreach ($menu as $key => $item) : ?>
            <div class="lstMenuCont <?= $item['class'] ?>"><a data-toggle="collapse" data-parent="#navigation" href="#<?= $key ?>"><?= Translate::get($item['title']) ?></a></div>
            <div id="<?= $key ?>" class="panel-collapse collapse <?php if ($item['class'] == 'active'): ?>in<?php endif; ?>">    
                <ul>
                    <?php foreach ($item['notes'] as $sub_key => $sub_item) : ?>
                        <li class="<?= $sub_item['class'] ?>"><a href="<?= $sub_item['url'] ?>"><i class="<?= $sub_item['icon'] ?>"></i><?= Translate::get($sub_item['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
        <script>
            $(".lstMenuCont").click(function () {
                var children = $('.lstMenu').find('.lstMenuCont');

                $(".lstMenuCont").removeClass("active");
                $(this).addClass("active");
                for (var i = 1; i <= children.length; i++)
                {
                    var menuId = "#Menu0" + i;
                    if (menuId != $(this).find("a").attr("href"))
                    {
                        $(menuId).removeClass("in");
                    }
                }
            });
        </script>
    </div>
</div>