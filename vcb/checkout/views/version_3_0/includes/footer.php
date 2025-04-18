<?php

use common\components\utils\ObjInput;
use common\components\utils\Translate;

/** @var array $checkout_order */

?>
<?php
// TINH FEE VA TOTAL AMOUNT
$fee = isset($checkout_order['sender_fee']) && intval($checkout_order['sender_fee']) > 0
    ? $checkout_order['sender_fee'] : 0;
$total_amount = $checkout_order['amount'] + $fee;
?>
<div class="footer-other">
    <ul>
        <li>
            <a class="dropdown-toggle" href="#other-footer" data-toggle="modal">
                <i class="las la-file-invoice"></i>
                <span><?= Translate::get('Chi tiết đơn hàng') ?></span>
                <i class="las la-angle-down"></i>
            </a>
        </li>
    </ul>
</div>

<!-- Modal -->
<div class="modal fade" id="other-footer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="check-list" aria-labelledby="dropdownMenuLink">
                    <div class="cl-top">
                        <p>
                            <?= Translate::get('Mã đơn hàng') ?>
                            <span><?= $checkout_order['order_code'] ?></span>
                        </p>
                        <p>
                            <?= Translate::get('Mô tả') ?>
                            <span>
                                <?= $checkout_order['order_description'] ?>
                            </span>
                        </p>
                    </div>
                    <hr>
                    <div class="cl-bottom">
                        <p>
                            <?= Translate::get('Giá trị đơn hàng') ?>:
                            <span><?= ObjInput::makeCurrency($checkout_order['amount']) ?> <?= $checkout_order['currency'] ?></span>
                        </p>
                        <p>
                            <?= Translate::get('Phí') ?>:
                            <span><?= ObjInput::makeCurrency($fee) ?> <?= $checkout_order['currency'] ?></span>
                        </p>
                        <p>
                            <label><?= Translate::get('Tổng tiền') ?>: </label>
                            <b><?= ObjInput::makeCurrency($total_amount) ?> <?= $checkout_order['currency'] ?></b>
                        </p>
                    </div>
                    <?php /** @var bool $allow_cancel */
                    if ($allow_cancel): ?>
                        <hr>
                        <div class="cl-link">
                            <a href="#pay-cancel" data-dismiss="modal" data-toggle="modal"><i
                                        class="las la-times-circle"></i> <?= Translate::get('Huỷ đơn hàng') ?></a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>


<footer class="background-head-foot">
    <div class="container">
        <div class="footer-left">
            <a href=""><img src="dist/images/icon-logo.png" alt=""></a>
            <div class="fl-text">
                <p><?= \common\components\utils\Translate::get('Một dịch vụ của') ?><b> Vietcombank</b></p>
                <!--                <ul>-->
                <!--                    <li><i class="las la-phone"></i> 1900585885</li>-->
                <!--                    <li><i class="las la-envelope"></i> vcbnews.ho@vietcombank.com.vn​</li>-->
                <!--                </ul>-->
            </div>
        </div>
        <div class="footer-right">
            <ul>
                <!--                <li><a href="#"><img src="upgrade/images/Layer3.png" alt=""></a></li>-->
                <!--                <li><a href="#"><img src="upgrade/images/Layer2.png" alt=""></a></li>-->
                <li>
                    <span><?= \common\components\utils\Translate::get('An toàn & bảo mật') ?> </span><br><?= \common\components\utils\Translate::get('đạt tiêu chuẩn quốc tế') ?>
                </li>
            </ul>
        </div>
    </div>
</footer>
