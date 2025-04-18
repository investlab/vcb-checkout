<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\models\db\Transaction;
use common\models\db\TransactionType;
use common\components\utils\Translate;

$this->title = Translate::get('Thông tin giao dịch Thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?= Translate::get('Thông tin giao dịch Thanh toán') ?></h1>

    <div class="row margin-top-10">
        <div class="col-xs-12 col-sm-12">
            <div class="panel panel-default margin-bottom-10">
                <div class="panel-body">
                    <div class="form-horizontal pdtop2 mform" role="form" style="display: contents;">
                        <div class="form-group mrgb0">
                            <label for="" class="col-sm-5 col-md-4 control-label bold"> <?= Translate::get('Người mua') ?>:</label>

                            <div class="col-sm-7 col-md-8">
                                <div class="form-control-static">
                                    <h4 class=" media-heading">
                                        <?= isset($checkout_order['buyer_fullname']) && $checkout_order['buyer_fullname'] != null ? $checkout_order['buyer_fullname'] : "" ?>
                                    </h4>

                                    <div>
                                        <i class="fa fa-envelope"></i>
                                        <?= isset($checkout_order['buyer_email']) && $checkout_order['buyer_email'] != null ? $checkout_order['buyer_email'] : "" ?>
                                    </div>
                                    <div>
                                        <i class="fa fa-phone"></i>
                                        <?= isset($checkout_order['buyer_mobile']) && $checkout_order['buyer_mobile'] != null ? $checkout_order['buyer_mobile'] : "" ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hide-for-xs">
                            <hr>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="" class="col-sm-5 col-md-4 control-label bold"> <?= Translate::get('Mã token') ?>:</label>

                            <div class="col-sm-7 col-md-8">
                                <p class="form-control-static">
                                    <?= isset($checkout_order['token_code']) && $checkout_order['token_code'] != null ? $checkout_order['token_code'] : "" ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="" class="col-sm-5 col-md-4 control-label"> <?= Translate::get('Mã đơn hàng') ?>:</label>

                            <div class="col-sm-7 col-md-8">
                                <p class="form-control-static">
                                    <?= isset($checkout_order['order_code']) && $checkout_order['order_code'] != null ? $checkout_order['order_code'] : "" ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="" class="col-sm-5 col-md-4 control-label"> <?= Translate::get('Mô tả đơn hàng') ?>:</label>

                            <div class="col-sm-7 col-md-8">
                                <p class="form-control-static">
                                    <?= isset($checkout_order['order_description']) && $checkout_order['order_description'] != null ? $checkout_order['order_description'] : "" ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Số tiền đơn hàng') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static"><strong class="fontS14 text-success">
                                        <?= isset($checkout_order['amount']) && $checkout_order['amount'] != null ? ObjInput::makeCurrency($checkout_order['amount']) : 0 ?>
                                    </strong> VND</p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Phí giao dịch') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static">
                                    <strong><?= isset($checkout_order['receiver_fee']) && $checkout_order['receiver_fee'] != null ? ObjInput::makeCurrency($checkout_order['receiver_fee']) : 0 ?></strong>
                                    VND</p>
                            </div>
                        </div>

                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Số tiền nhận được') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static">
                                    <strong class="fontS14 text-primary">
                                        <?= isset($checkout_order['cashout_amount']) && $checkout_order['cashout_amount'] != null ? ObjInput::makeCurrency($checkout_order['cashout_amount']) : 0 ?>
                                    </strong> VND</p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Hình thức thanh toán') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static">
                                    <?= isset($checkout_order['payment_method_name']) && $checkout_order['payment_method_name'] != null ? Translate::get($checkout_order['payment_method_name']) : "" ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($checkout_order['installment_info']) { ?>
                            <div class="form-group mrgb0">
                                <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Loại thẻ') ?>:</label>

                                <div class="col-sm-7 col-md-8 pdr5">
                                    <p class="form-control-static">
                                        <?php if (!empty(json_decode($checkout_order['installment_info'], true)['transactionInfo'])) { ?>
                                            <?= json_decode($checkout_order['installment_info'], true)['transactionInfo']['method'] ?>
                                        <?php } else { ?>
                                            <?= json_decode($checkout_order['installment_info'], true)['method'] ?>
                                        <?php } ?>
                                    </p>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($checkout_order['installment_cycle']) { ?>
                            <div class="form-group mrgb0">
                                <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Kỳ hạn trả góp') ?>:</label>

                                <div class="col-sm-7 col-md-8 pdr5">
                                    <p class="form-control-static">
                                        <?= Translate::get($checkout_order['installment_cycle'] . ' Tháng') ?>
                                    </p>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="hide-for-xs">
                            <hr>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Thời gian tạo') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static"><?= isset($checkout_order['time_created']) && intval($checkout_order['time_created']) > 0 ? date('H:i,d-m-Y', $checkout_order['time_created']) : '' ?></p>
                            </div>
                        </div>
                        <div class="form-group mrgb0">
                            <label for="inputPassword3" class="col-sm-5 col-md-4 control-label "> <?= Translate::get('Thời gian thanh toán') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5">
                                <p class="form-control-static"><?= isset($checkout_order['time_paid']) && intval($checkout_order['time_paid']) > 0 ? date('H:i,d-m-Y', $checkout_order['time_paid']) : '' ?></p>
                            </div>
                        </div>
                        <div class="form-group mline">
                            <label for="" class="col-sm-5 col-md-4 control-label"> <?= Translate::get('Trạng thái giao dịch') ?>:</label>

                            <div class="col-sm-7 col-md-8 pdr5 mgtop5">
                                <p class="form-control-static">
                                    <?php if ($checkout_order['status'] == CheckoutOrder::STATUS_NEW) { ?>
                                        <span class="label label-default"><?= Translate::get('Chưa thanh toán') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_PAYING) { ?>
                                        <span class="label label-warning"><?= Translate::get('Đang thanh toán') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_PAID) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã thanh toán') ?></span><br>
                                        <button type="button" class="btn btn-default btn-sm" style="margin-top:5px;" data-toggle="modal" data-target="#modelRefund">
                                            <?= Translate::get('Xem lịch sử hoàn tiền') ?>
                                        </button>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_CANCEL) { ?>
                                        <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_REVIEW) { ?>
                                        <span class="label label-danger"><?= Translate::get('Bị review') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WAIT_REFUND) { ?>
                                        <span class="label label-warning"><?= Translate::get('Đang đợi hoàn tiền') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_REFUND) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã hoàn toàn bộ') ?></span><br>
                                        <button type="button" class="btn btn-default btn-sm" style="margin-top:5px;" data-toggle="modal" data-target="#modelRefund">
                                            <?= Translate::get('Xem lịch sử hoàn tiền') ?>
                                        </button>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã hoàn một phần') ?></span><br>
                                        <button type="button" class="btn btn-default btn-sm" style="margin-top:5px;" data-toggle="modal" data-target="#modelRefund">
                                            <?= Translate::get('Xem lịch sử hoàn tiền') ?>
                                        </button>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WAIT_WIDTHDAW) { ?>
                                        <span class="label label-warning"><?= Translate::get('Đang rút tiền') ?></span>
                                    <?php } elseif ($checkout_order['status'] == CheckoutOrder::STATUS_WIDTHDAW) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã rút tiền') ?></span>
                                    <?php } ?>
                                </p>
                                <p></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!--begin button-->
            <div class="pdtop mobile-btn-center">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index') ?>" class="btn btn-danger"><?= Translate::get('Quay lại') ?></a>
            </div>
        </div>
    </div>

</div>

<div id="modelRefund" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title greenFont"><?= Translate::get('Lịch sử hoàn tiền') ?></h4>
            </div>
            <div class="modal-body">
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
                                <!--<th>ID</th>-->
                                <th><?= Translate::get('Phương thức hoàn tiền') ?></th>
                                <th><?= Translate::get('Kênh') ?></th>
                                <th><?= Translate::get('Số tiền') ?></th>
                                <th><?= Translate::get('Phí') ?></th>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <th><?= Translate::get('Trạng thái') ?></th>
                            </tr>
                        </thead>
                        <?php
                        if (is_array($checkout_order['transaction_info']) && count($checkout_order['transaction_info']) > 0) {
                            foreach ($checkout_order['transaction_info'] as $key => $data) {
                                if ($data['transaction_type_id'] == TransactionType::getRefundTransactionTypeId()) {
                                ?>
                                <tbody>
                                    <tr>
<!--                                        <td>
                                            <?= isset($data['id']) && $data['id'] != null ? $data['id'] : "" ?>
                                        </td>-->
                                        <td class="col-sm-2">
                                            <div class="small">
                                                <?= Translate::get(@$data['payment_method_info']['name']) ?>
                                            </div>
                                        </td>
                                        <td class="col-sm-2">
                                            <div class="small">
                                                <strong><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong>
<!--                                                <hr>
                                                <?= Translate::get('Mã tham chiếu') ?>: <strong><?= $data['bank_refer_code'] ?></strong>-->
                                            </div>
                                        </td>
                                        <td class="col-sm-2">
                                            <div class="small">
                                                <strong class="text-danger">  <?= ObjInput::makeCurrency($data['amount']) ?> <?= $data['currency'] ?></strong>
                                            </div>
                                        </td>
                                        <td class="col-sm-2">
                                            <div class="small">
                                                <?= Translate::get('Phí hoàn tiền thu MC') ?>: <strong><?= ObjInput::makeCurrency($data['sender_fee']) ?> <?= $data['currency'] ?></strong><br>
                                            </div>
                                        </td>
                                        <td class="col-sm-2">
                                            <div class="small">
                                                <?= Translate::get('Tạo') ?>: <strong><?= intval($data['time_created']) > 0 ? date('H:i,d-m-Y', $data['time_created']) : '' ?></strong><br>
                                                <?= Translate::get('Cập nhật') ?>: <strong><?= intval($data['time_updated']) > 0 ? date('H:i,d-m-Y', $data['time_updated']) : '' ?></strong><br>
                                                <?= Translate::get('Thành công') ?>: <strong><?= intval($data['time_paid']) > 0 ? date('H:i,d-m-Y', $data['time_paid']) : '' ?></strong><br>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($data['status'] == Transaction::STATUS_NEW) { ?>
                                                <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
                                            <?php } elseif ($data['status'] == Transaction::STATUS_PAYING) { ?>
                                                <span class="label label-primary"><?= Translate::get('Đang chờ xử lý') ?></span>
                                            <?php } elseif ($data['status'] == Transaction::STATUS_PAID) { ?>
                                                <span class="label label-success"><?= Translate::get('Đã thành công') ?></span>
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
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Đóng') ?></button>
            </div>
        </div>
    </div>
</div>