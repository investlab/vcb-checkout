<?php

use common\components\utils\ObjInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\db\CheckoutOrder;
use common\models\db\Transaction;
use common\models\db\CheckoutOrderCallbackHistory;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết đơn thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Start .content-wrapper -->
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Chi tiết đơn thanh toán') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <a class="btn btn-danger btn-sm"
                       href="<?= Yii::$app->urlManager->createUrl('checkout-order/index') ?>">
                        <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
                    </a>
                </div>
            </div>
            <!-- InstanceEndEditable -->
        </div>
    </div>
    <!-- End .page-header -->
</div>
<!-- End .row -->
<div class=outlet>
<!-- InstanceBeginEditable name="EditRegion2" -->

<div class=row>
<div class=col-lg-12>
<!-- Start col-lg-12 -->
<div class="panel panel-primary">
    <!-- Start .panel -->
    <div class=panel-heading>
        <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
    </div>
    <div class=panel-body>
        <table class="table table-hover" width="100%">

            <tr>
                <th>Mã token</th>
                <td><?= $checkout_order['token_code'] ?></td>
                <th>Version</th>
                <td><?= @$checkout_order['version'] ?></td>
            </tr>

            <tr>
                <th>Merchant</th>
                <td><?= @$checkout_order['merchant_info']['name'] ?></td>                
                <th><?= Translate::get('Tên người thanh toán') ?></th>
                <td><?= $checkout_order['buyer_fullname'] ?></td>
            </tr>
            <tr>
                <th><?= Translate::get('Mã đơn hàng') ?></th>
                <td><?= @$checkout_order['order_code'] ?></td>
                <th><?= Translate::get('Email người thanh toán') ?></th>
                <td><?= $checkout_order['buyer_email'] ?></td>
            </tr>
            <tr>    
                <th><?= Translate::get('Số tiền đơn hàng') ?></th>
                <td><?= ObjInput::makeCurrency($checkout_order['amount']) ?>&nbsp;<?= $checkout_order['currency'] ?></td>
                <th><?= Translate::get('Số điện thoại') ?></th>
                <td><?= $checkout_order['buyer_mobile'] ?></td>
            </tr>
            <tr >
                <?php if ($checkout_order['installment_cycle']) { ?>
                <th>
                    <?= Translate::get('Kỳ hạn trả góp') ?>
                </th>
                <td><?= $checkout_order['installment_cycle'] ?> tháng</td>
                <?php } ?>

                <?php if ($checkout_order['installment_info']) { ?>
                <th><?= Translate::get('Loại thẻ') ?></th>
                <td>
                    <?php if (!empty(json_decode($checkout_order['installment_info'],true)['transactionInfo'])) { ?>
                        <?= json_decode($checkout_order['installment_info'],true)['transactionInfo']['method'] ?>
                    <?php } else {?>
                        <?= json_decode($checkout_order['installment_info'],true)['method'] ?>
                    <?php }?>
                </td>
                <?php } ?>
            </tr>
            <tr>
                <th><?= Translate::get('Mô tả đơn hàng') ?></th>
                <td><?= @$checkout_order['order_description'] ?></td>
                <th><?= Translate::get('Địa chỉ người thanh toán') ?></th>
                <td><?= $checkout_order['buyer_address'] ?></td>
            </tr>
            <tr>
                <th rowspan="2"><?= Translate::get('Thời gian') ?></th>
                <td rowspan="2">
                    <div class="small">
                        <?= Translate::get('TG tạo') ?>: <strong><?= intval($checkout_order['time_created']) > 0 ? date('H:i, d/m/Y', $checkout_order['time_created']) : '' ?></strong><br>
                        <?= Translate::get('TG cập nhật') ?>: <strong><?= intval($checkout_order['time_updated']) > 0 ? date('H:i, d/m/Y', $checkout_order['time_updated']) : '' ?></strong><br>
                        <?= Translate::get('TG thanh toán') ?>: <strong><?= intval($checkout_order['time_paid']) > 0 ? date('H:i, d/m/Y', $checkout_order['time_paid']) : '' ?></strong><br>
                        <?= Translate::get('TG hoàn tiền') ?>: <strong><?= intval($checkout_order['time_refund']) > 0 ? date('H:i, d/m/Y', $checkout_order['time_refund']) : '' ?></strong><br>
                        <?= Translate::get('TG rút tiền') ?>: <strong><?= intval($checkout_order['time_withdraw']) > 0 ? date('H:i, d/m/Y', $checkout_order['time_withdraw']) : '' ?></strong><br>
                    </div>
                </td>
                <th><?= Translate::get('Trạng thái đơn hàng')?></th>
                <td>
                    <?php if ($checkout_order['status'] == CheckoutOrder::STATUS_NEW) { ?>
                        <span class="label label-default"><?= Translate::get('Chưa thanh toán') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_PAYING) { ?>
                        <span class="label label-warning"><?= Translate::get('Đang thanh toán') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_PAID) { ?>
                        <span class="label label-success"><?= Translate::get('Đã thanh toán') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_CANCEL) { ?>
                        <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_REVIEW) { ?>
                        <span class="label label-danger"><?= Translate::get('Bị review') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WAIT_REFUND) { ?>
                        <span class="label label-warning"><?= Translate::get('Đang đợi hoàn tiền') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_REFUND) { ?>
                        <span class="label label-success"><?= Translate::get('Đã hoàn tiền') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WAIT_WIDTHDAW) { ?>
                        <span class="label label-warning"><?= Translate::get('Đang rút tiền') ?></span>
                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WIDTHDAW) { ?>
                        <span class="label label-success"><?= Translate::get('Đã rút tiền') ?></span>
                    <?php }  elseif ($checkout_order['status'] == CheckoutOrder::STATUS_FAILURE) { ?>
                        <span class="label label-danger"><?= Translate::get('Giao dịch thất bại') ?></span>
                    <?php } ?>
                </td>
            </tr>
            <tr>                
                <th><?= Translate::get('Trạng thái gọi lại merchant') ?></th>
                <td>
                    <?php if ($checkout_order['callback_status'] == CheckoutOrder::CALLBACK_STATUS_NEW) { ?>
                        <span class="label label-default"><?= Translate::get('Chưa gọi') ?></span>
                    <?php } elseif ($checkout_order['callback_status'] == CheckoutOrder::CALLBACK_STATUS_PROCESSING) { ?>
                        <span class="label label-warning"><?= Translate::get('Đang gọi') ?></span>
                    <?php } elseif ($checkout_order['callback_status'] == CheckoutOrder::CALLBACK_STATUS_SUCCESS) { ?>
                        <span class="label label-success"><?= Translate::get('Đã gọi') ?></span>
                    <?php } elseif ($checkout_order['callback_status'] == CheckoutOrder::CALLBACK_STATUS_ERROR) { ?>
                        <span class="label label-danger"><?= Translate::get('Lỗi') ?></span>
                    <?php } ?>
                </td>

            </tr>
        </table>
    </div>
</div>
<!-- Danh sách giao dịch thanh toán -->
<div class="panel panel-primary">
    <!-- Start .panel -->
    <div class=panel-heading>
        <h3 class=panel-title><?= Translate::get('Danh sách giao dịch thanh toán') ?></h3>
    </div>
    <div class=panel-body>
        <?php
        if ($checkout_order['transaction_info'] == null || is_array($checkout_order['transaction_info']) && count($checkout_order['transaction_info']) == 0) {
            ?>
            <div class="alert alert-danger fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
            </div>
        <?php } ?>
        <div class="table-responsive">
            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= Translate::get('Phương thức thanh toán') ?></th>
                    <th><?= Translate::get('Kênh thanh toán') ?></th>
                    <th><?= Translate::get('Số tiền') ?></th>
                    <th><?= Translate::get('Phí') ?></th>
                    <th><?= Translate::get('Thời gian') ?></th>
                    <th><?= Translate::get('Trạng thái') ?></th>
                </tr>
                </thead>
                <?php
                if (is_array($checkout_order['transaction_info']) && count($checkout_order['transaction_info']) > 0) {
                    foreach ($checkout_order['transaction_info'] as $key => $data) {
                        ?>
                        <tbody>
                        <tr>
                            <td>
                                <?= isset($data['id']) && $data['id'] != null ? $data['id'] : "" ?>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <?= Translate::get(@$data['payment_method_info']['name']) ?>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <?= Translate::get('Tên') ?>: <strong><?= Translate::get(@$data['partner_payment_info']['name'])?></strong>
                                    <hr>
                                    <?= Translate::get('Mã tham chiếu') ?>: <strong><?= $data['bank_refer_code'] ?></strong>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <strong class="text-danger">  <?= ObjInput::makeCurrency($data['amount'])?> <?=$data['currency']?></strong>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <?= Translate::get('Người chuyển') ?>: <strong><?= ObjInput::makeCurrency($data['sender_fee'])?> <?=$data['currency']?></strong><br>
                                    <hr>
                                    <?= Translate::get('Người nhận') ?>: <strong> <?= ObjInput::makeCurrency($data['receiver_fee'])?> <?=$data['currency']?></strong><br>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <?= Translate::get('Tạo') ?>: <strong><?= intval($data['time_created']) > 0 ? date('H:i,d-m-Y', $data['time_created']) : ''?></strong><br>
                                    <?= Translate::get('Cập nhật') ?>: <strong><?= intval($data['time_updated']) > 0 ? date('H:i,d-m-Y', $data['time_updated']) : '' ?></strong><br>
                                    <?= Translate::get('Thanh toán') ?>: <strong><?= intval($data['time_paid']) > 0 ? date('H:i,d-m-Y', $data['time_paid']) : '' ?></strong><br>
                                </div>
                            </td>
                            <td>
                                <?php if ($data['status'] == Transaction::STATUS_NEW) { ?>
                                    <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
                                <?php } elseif ($data['status'] == Transaction::STATUS_PAYING) { ?>
                                    <span class="label label-primary"><?= Translate::get('Đang thanh toán') ?></span>
                                <?php } elseif ($data['status'] == Transaction::STATUS_PAID) { ?>
                                    <span class="label label-success"><?= Translate::get('Đã thanh toán') ?></span>
                                <?php } elseif ($data['status'] == Transaction::STATUS_CANCEL) { ?>
                                    <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                    <br><br>
                                    <div class="small">
                                        <i class="text-danger"> <?= Translate::get('Lý do hủy') ?> :
                                            <?= isset($data['reason_info']['name']) && $data['reason_info']['name'] != null ? $data['reason_info']['name'] : '' ?>
                                            <br>
                                            <?= Translate::get('Mô tả') ?> : <?= $data['reason'] ?>
                                        </i>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                        </tbody>
                    <?php
                    }
                } ?>

            </table>
        </div>
    </div>
</div>
<!-- Danh sách các lần gọi lại merchant -->
<div class="panel panel-primary">
    <div class=panel-heading>
        <h3 class=panel-title><?= Translate::get('Danh sách các lần gọi lại merchant') ?></h3>
    </div>
    <div class=panel-body>
        <?php
        if ($checkout_order['checkout_order_callback_history_info'] == null || is_array($checkout_order['checkout_order_callback_history_info']) && count($checkout_order['checkout_order_callback_history_info']) == 0) {
            ?>
            <div class="alert alert-danger fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
            </div>
        <?php } ?>
        <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>ID</th>
                <th><?= Translate::get('Giá trị trả về') ?></th>
                <th><?= Translate::get('Thời gian') ?></th>
                <th><?= Translate::get('Trạng thái') ?></th>
            </tr>
            </thead>
            <?php
            if (is_array($checkout_order['checkout_order_callback_history_info']) && count($checkout_order['checkout_order_callback_history_info']) > 0) {
                foreach ($checkout_order['checkout_order_callback_history_info'] as $key => $data) {
                    ?>
                    <tr>
                        <td class="col-sm-2">
                            <?= isset($data['id']) && $data['id'] != null ? $data['id'] : "" ?>
                        </td>
                        <td class="col-sm-4">
                            <?= isset($data['response_data']) && $data['response_data'] != null ? $data['response_data'] : "" ?>
                        </td>
                        <td class="col-sm-3">
                            <?= Translate::get('Gọi') ?>:
                            <strong> <?= isset($data['time_request']) && intval($data['time_request']) > 0 ? date('H:i:s, d-m-Y', $data['time_request']) : '' ?></strong>
                            <br><?= Translate::get('Nhận kết quả') ?>:
                            <strong> <?= isset($data['time_response']) && intval($data['time_response']) > 0 ? date('H:i:s, d-m-Y', $data['time_response']) : '' ?></strong>
                        </td>
                        <td class="col-sm-2">
                            <?php if ($data['status'] == CheckoutOrderCallbackHistory::STATUS_NEW) { ?>
                                <span class="label label-default"><?= Translate::get('Chưa gọi') ?></span>
                            <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_PROCESSING) { ?>
                                <span class="label label-primary"><?= Translate::get('Đang gọi') ?></span>
                            <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_SUCCESS) { ?>
                                <span class="label label-success"><?= Translate::get('Đã gọi') ?></span>
                            <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_ERROR) { ?>
                                <span class="label label-danger"><?= Translate::get('Lỗi') ?></span>
                            <?php } ?>
                        </td>

                    </tr>
                <?php
                }
            } ?>

        </table>
    </div>
</div>
<div class="col-lg-12" style="padding:20px 0px 30px">
    <a href="<?= Yii::$app->urlManager->createUrl('checkout-order/index') ?>" class="btn btn-danger btn-sm">
        <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
    </a>
</div>
</div>
</div>
</div>
</div>