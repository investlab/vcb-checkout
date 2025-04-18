<?php

use common\components\utils\ObjInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\db\CardTransaction;
use common\models\db\Method;
use common\models\db\Cashout;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết giao dịch thẻ cào');
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
            <h1 class=page-header>&nbsp;</h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <a class="btn btn-danger btn-sm"
                       href="<?= Yii::$app->urlManager->createUrl('card-transaction/index') ?>">
                        <i class="en-back"></i><?= Translate::get('Quay lại') ?>
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
        <h4><?= Translate::get('Chi tiết giao dịch thẻ cào') ?></h4>
    </div>
    <div class=panel-body>
        <table class="table table-hover" width="100%">

            <tr>
                <th>ID</th>
                <td><?= @$card_transaction['id'] ?></td>
                <th>Version</th>
                <td><?= @$card_transaction['version'] ?></td>
            </tr>
            <tr>
                <th><?= Translate::get('Loại thẻ') ?></th>
                <td><?= @$card_transaction['card_type_info']['name'] ?></td>
                <th><?= Translate::get('Loại hóa đơn') ?></th>
                <td><?= @$card_transaction['bill_type_name'] ?></td>

            </tr>
            <tr>
                <th><?= Translate::get('Mã thẻ') ?></th>
                <td><?= @$card_transaction['card_code'] ?></td>
                <th><?= Translate::get('Đối tác') ?></th>
                <td><?= Translate::get(@$card_transaction['partner_card_info']['name']) ?></td>

            </tr>
            <tr>
                <th><?= Translate::get('Serial thẻ') ?></th>
                <td><?= @$card_transaction['card_serial'] ?></td>
                <th><?= Translate::get('Mã tham chiếu với đối tác') ?></th>
                <td><?= @$card_transaction['partner_card_refer_code'] ?></td>
            </tr>
            <tr>
                <th><?= Translate::get('Mệnh giá thẻ') ?></th>
                <td>
                    <?= ObjInput::makeCurrency(@$card_transaction['card_price']) ?>
                    &nbsp;<?= @$card_transaction['currency'] ?>
                </td>
                <th><?= Translate::get('Log gạch thẻ với đối tác') ?></th>
                <td><?= @$card_transaction['partner_card_log_id'] ?></td>

            </tr>

            <tr>
                <th>Merchant</th>
                <td><?= Translate::get(@$card_transaction['merchant_info']['name']) ?></td>
                <th><?= Translate::get('Số tiền trả merchant') ?></th>
                <td>
                    <?= ObjInput::makeCurrency(@$card_transaction['card_amount']) ?>
                    &nbsp;<?= @$card_transaction['currency'] ?>
                </td>
            </tr>
            <tr>
                <th><?= Translate::get('Mã tham chiếu với merchant') ?></th>
                <td><?= @$card_transaction['merchant_refer_code'] ?></td>

                <th><?= Translate::get('Kỳ thanh toán') ?></th>
                <td><?= Translate::get(@$card_transaction['cycle_day_name']) ?></td>
            </tr>
            <tr>
                <th><?= Translate::get('Mã trả về cho Merchant') ?></th>
                <td><?= @$card_transaction['result_code'] ?></td>
                <th><?= Translate::get('Phần trăm phí') ?></th>
                <td><?= @$card_transaction['percent_fee'] ?></td>

            </tr>
            <tr>
                <th><?= Translate::get('Trạng thái') ?></th>
                <td>
                    <?php if ($card_transaction['status'] == CardTransaction::STATUS_NEW) { ?>
                        <span class="label label-default"><?= Translate::get('Chưa rút') ?></span>
                    <?php } elseif ($card_transaction['status'] == CardTransaction::STATUS_PROCESSING) { ?>
                        <span class="label label-warning"><?= Translate::get('Đang rút') ?></span>
                    <?php } elseif ($card_transaction['status'] == CardTransaction::STATUS_WITHDRAW) { ?>
                        <span class="label label-success"><?= Translate::get('Đã rút') ?></span>
                    <?php } ?>
                </td>

                <th><?= Translate::get('Thời gian') ?></th>
                <td>
                    <div class="small">
                        <?php if (intval($card_transaction['time_created']) > 0): ?>
                            <?= Translate::get('Tạo') ?>:
                            <strong><?= date('H:i, d/m/Y', $card_transaction['time_created']) ?></strong>
                            <br>
                        <?php endif; ?>
                        <?php if (intval($card_transaction['time_withdraw']) > 0): ?>
                            <?= Translate::get('Rút') ?>:
                            <strong><?= date('H:i, d/m/Y', $card_transaction['time_withdraw']) ?></strong>
                            <br>
                        <?php endif; ?>
                        <?php if (intval($card_transaction['withdraw_time_limit']) > 0): ?>
                            <?= Translate::get('Thời hạn rút') ?>:
                            <strong><?= date('H:i, d/m/Y', $card_transaction['withdraw_time_limit']) ?></strong>
                            <br>
                        <?php endif; ?>
                        <?php if (intval($card_transaction['time_updated']) > 0): ?>
                            <?= Translate::get('Cập nhật') ?>:
                            <strong><?= date('H:i, d/m/Y', $card_transaction['time_updated']) ?></strong><br>
                        <?php endif; ?>
                    </div>
                </td>


            </tr>

        </table>
    </div>
</div>

<?php if ($cashout != null) { ?>
    <div class="panel panel-primary">
        <!-- Start .panel -->
        <div class=panel-heading>
            <h3 class=panel-title><?= Translate::get('Thông tin yêu cầu rút tiền') ?></h3>
        </div>
        <div class=panel-body>
            <table class="table table-hover" width="100%">
                <tr>
                    <th>ID</th>
                    <td>
                        <?= isset($cashout['id']) && $cashout['id'] != null ? $cashout['id'] : "" ?>
                    </td>
                    <th>Merchant</th>
                    <td>
                        <?= isset($cashout['merchant_info']['name']) && $cashout['merchant_info']['name'] != null ? Translate::get($cashout['merchant_info']['name']) : "" ?>
                    </td>
                </tr>
                <tr>
                    <th><?= Translate::get('Nhóm phương thức thanh toán') ?></th>
                    <td>
                        <?= isset($cashout['method_info']['name']) && $cashout['method_info']['name'] != null ? Translate::get($cashout['method_info']['name']) : "" ?>
                    </td>
                    <th><?= Translate::get('Số tiền rút') ?><br><br> <?= Translate::get('Phí rút') ?>:</th>
                    <td>
                        <?= isset($cashout['amount']) && $cashout['amount'] != null ? ObjInput::makeCurrency($cashout['amount']) : 0 ?>
                        &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND'] ?>
                        <br><br>
                        <?= isset($cashout['receiver_fee']) && $cashout['receiver_fee'] != null ? ObjInput::makeCurrency($cashout['receiver_fee']) : 0 ?>
                        &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND'] ?>
                    </td>
                </tr>
                <tr>
                    <th><?= Translate::get('Phương thức thanh toán') ?></th>
                    <td>
                        <?= isset($cashout['payment_method_info']['name']) && $cashout['payment_method_info']['name'] != null ? $cashout['payment_method_info']['name'] : "" ?>
                    </td>

                    <th><?= Translate::get('Kênh thanh toán') ?></th>
                    <td>
                        <?= isset($cashout['partner_payment_info']['name']) && $cashout['partner_payment_info']['name'] != null ? Translate::get($cashout['partner_payment_info']['name']) : "" ?>
                    </td>

                </tr>
                <tr>
                    <th>
                        <?php if (Method::isWithdrawIBOffline(@$cashout['method_info']['code'])) : ?>
                            <?= Translate::get('Số tài khoản') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                            <?= Translate::get('Tên chủ tài khoản') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                            <?= Translate::get('Chi nhánh') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_branch'] ?></strong><br>
                        <?php elseif (Method::isWithdrawATMCard(@$cashout['method_info']['code'])): ?>
                            <?= Translate::get('Số thẻ') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                            <?= Translate::get('Tên chủ thẻ') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                            <?= Translate::get('Ngày phát hành') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_card_month'] ?>
                                /<?= @$cashout['bank_card_year'] ?></strong><br>
                        <?php
                        elseif (Method::isWithdrawWallet(@$cashout['method_info']['code'])): ?>
                            <?= Translate::get('Email tài khoản') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                            <?= Translate::get('Tên chủ tài khoản') ?>: <strong
                                class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                        <?php endif; ?>
                    </th>

                    <th><?= Translate::get('Giao dịch rút tiền') ?></th>
                    <td>
                        <?= isset($cashout['transaction_id']) && $cashout['transaction_id'] != null ? $cashout['transaction_id'] : "" ?>
                    </td>
                </tr>


                <tr>
                    <th><?= Translate::get('Trạng thái') ?></th>
                    <td>
                        <?php if ($cashout['status'] == Cashout::STATUS_NEW) { ?>
                            <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_WAIT_VERIFY) { ?>
                            <span class="label label-primary"><?= Translate::get('Đợi merchant xác nhận') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_VERIFY) { ?>
                            <span class="label label-success"><?= Translate::get('Merchant đã xác nhận') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_WAIT_ACCEPT) { ?>
                            <span class="label label-warning"><?= Translate::get('Đã gửi, đợi duyệt') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_REJECT) { ?>
                            <span class="label label-danger"><?= Translate::get('Từ chối') ?></span>
                            <br><br>
                            <div class="small">
                                <i class="text-danger"> <?= Translate::get('Lý do từ chối') ?> :
                                    <?= isset($cashout['reason_info']['name']) && $cashout['reason_info']['name'] != null ? $cashout['reason_info']['name'] : '' ?>
                                    <br>
                                    <?= Translate::get('Mô tả') ?> : <?= Translate::get($cashout['reason']) ?>
                                </i>
                            </div>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_ACCEPT) { ?>
                            <span class="label label-success"><?= Translate::get('Đã duyệt') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_PAID) { ?>
                            <span class="label label-success"><?= Translate::get('Đã chuyển ngân') ?></span>
                        <?php } elseif ($cashout['status'] == Cashout::STATUS_CANCEL) { ?>
                            <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                            <br><br>
                            <div class="small">
                                <i class="text-danger"> <?= Translate::get('Lý do hủy') ?> :
                                    <?= isset($cashout['reason_info']['name']) && $cashout['reason_info']['name'] != null ? $cashout['reason_info']['name'] : '' ?>
                                    <br>
                                    <?= Translate::get('Mô tả') ?> : <?= Translate::get($cashout['reason']) ?>
                                </i>
                            </div>
                        <?php } ?>

                    </td>
                    <th><?= Translate::get('Thời gian') ?></th>
                    <td>
                        <div class="small">
                            <?= Translate::get('Tạo') ?>:
                            <strong><?= isset($cashout['time_created']) && intval($cashout['time_created']) > 0 ? date('H:i,d-m-Y', $cashout['time_created']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Bắt đầu') ?>:
                            <strong><?= isset($cashout['time_begin']) && intval($cashout['time_begin']) > 0 ? date('H:i,d-m-Y', $cashout['time_begin']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Kết thúc') ?>:
                            <strong><?= isset($cashout['time_end']) && intval($cashout['time_end']) > 0 ? date('H:i,d-m-Y', $cashout['time_end']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Yêu cầu') ?>:
                            <strong><?= isset($cashout['time_request']) && intval($cashout['time_request']) > 0 ? date('H:i,d-m-Y', $cashout['time_request']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Duyệt') ?>:
                            <strong><?= isset($cashout['time_accept']) && intval($cashout['time_accept']) > 0 ? date('H:i,d-m-Y', $cashout['time_accept']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Từ chối') ?>:
                            <strong><?= isset($cashout['time_reject']) && intval($cashout['time_reject']) > 0 ? date('H:i,d-m-Y', $cashout['time_reject']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Chuyển ngân') ?>:
                            <strong><?= isset($cashout['time_paid']) && intval($cashout['time_paid']) > 0 ? date('H:i,d-m-Y', $cashout['time_paid']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Hủy:') ?>
                            <strong><?= isset($cashout['time_cancel']) && intval($cashout['time_cancel']) > 0 ? date('H:i,d-m-Y', $cashout['time_cancel']) : '' ?></strong>
                            <br>
                            <?= Translate::get('Cập nhật') ?>:
                            <strong><?= isset($cashout['time_updated']) && intval($cashout['time_updated']) > 0 ? date('H:i,d-m-Y', $cashout['time_updated']) : '' ?></strong>
                            <br>
                        </div>
                    </td>
                </tr>

            </table>
        </div>
    </div>
<?php } ?>

<div class="col-lg-12" style="padding:20px 0px 30px">
    <a href="<?= Yii::$app->urlManager->createUrl('card-transaction/index') ?>"
       class="btn btn-danger btn-sm">
        <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
    </a>
</div>
</div>
</div>
</div>
</div>