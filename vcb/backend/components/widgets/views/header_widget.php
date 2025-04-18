<?php
use common\components\utils\Translate;

?>
<script>
    var backend_url = '<?php echo $backend_url; ?>';
    $(document).ready(function() {

        (function() {
            $('.table-responsive').on('shown.bs.dropdown', function(e) {
                var $table = $(this),
                        $menu = $(e.target).find('.dropdown-menu'),
                        tableOffsetHeight = $table.offset().top + $table.height(),
                        menuOffsetHeight = $menu.offset().top + $menu.outerHeight(true) + 5,
                        $button = $(e.target).find('.dropdown-toggle');

                $top = $button.offset().top - $(window).scrollTop() + $button.outerHeight();
                if (($top + $menu.outerHeight()) > $(window).height())
                {
                    $top = $top - ($top + $menu.outerHeight() - $(window).height() + $button.outerHeight());
                    $right = $(window).width() - ($button.offset().left - $(window).scrollLeft());
                    $menu.css("right", $right + 'px');
                }
                else {
                    $menu.css("right", '10px');
                }
                $menu.css("top", $top + 'px');
                $menu.css("position", 'fixed');
//                }
            });

            $('.table-responsive').on('hide.bs.dropdown', function() {
                $('.table-responsive').css("min-height", "none");
            })
        })();
    });
</script>
<!-- Start #header -->
<div id="loadingDiv" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header" style="display: none;"></div>
            <div class="modal-body  text-center">
                <img src=<?php echo $backend_url; ?>images/svg/loading-bars.svg width=64 height=64 alt=loading>
               
            </div>
        </div>
    </div>
</div>
<div id=header>
    <div class=container-fluid>
        <div class=navbar>
            <div class=navbar-header><a class='navbar-brand' href='#' target="_blank">
                    <img src="<?=ROOT_URL?>logo.png" style="width:130px">
                </a></div>
            <!--topNav-->
            <nav class=top-nav role=navigation>
                <ul class="nav navbar-nav pull-left">
                    <li id=toggle-sidebar-li><a href=# id=toggle-sidebar><i class="im-list" style="transform: rotate(-180deg);"></i></a></li>
                    <li><a href=# class=full-screen><i class=fa-fullscreen></i></a></li>
                </ul>
                <!--begin notifications-->
                <ul class="nav navbar-nav pull-right">
                    <?php if ($users) : ?>
                        <li class=dropdown>
                            <a href=# data-toggle=dropdown>
                                <img class=user-avatar src="<?php echo $backend_url; ?>images/avatars/48.jpg" alt=loading...>
                                <span><?= $users->fullname;?></span>
                            </a>
                            <ul class="dropdown-menu right" role=menu>
                                <li><a href="<?php echo Yii::$app->urlManager->createAbsoluteUrl('default/detail').'?id='.$users->id;?>"><i class=st-user></i> <?= Translate::get('Thông tin tài khoản')?></a></li>
                                <li><a href="<?php echo $logout_url; ?>"><i class=im-exit></i> <?= Translate::get('Thoát')?></a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        </div>

        <!-- Start #header-area -->
        <div id=header-area class=fadeInDown>
            <div class=header-area-inner>
                <ul class="list-unstyled list-inline">
                    <li>
                        <div class=shortcut-button><a href=#><i class=im-pie></i> <span>Earning Stats</span></a></div>
                    </li>
                    <li>
                        <div class=shortcut-button><a href=#><i class="ec-images color-dark"></i> <span>Gallery</span></a></div>
                    </li>
                    <li>
                        <div class=shortcut-button><a href=#><i class="en-light-bulb color-orange"></i> <span>Fresh ideas</span></a></div>
                    </li>
                    <li>
                        <div class=shortcut-button><a href=#><i class="ec-link color-blue"></i> <span>Links</span></a></div>
                    </li>
                    <li>
                        <div class=shortcut-button><a href=#><i class="ec-support color-red"></i> <span>Support</span></a></div>
                    </li>
                    <li>
                        <div class=shortcut-button><a href=#><i class="st-lock color-teal"></i> <span>Lock area</span></a></div>
                    </li>
                </ul>
            </div>
        </div>
        <!-- End #header-area -->
    </div>
    <!-- Start .header-inner -->
</div>
<!-- End #header -->
