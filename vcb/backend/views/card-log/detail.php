<?php
use common\components\utils\ObjInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\db\CardLogFull;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết log thẻ cào');
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
                           href="<?= Yii::$app->urlManager->createUrl('card-log/index') ?>">
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
                        <h4><?= Translate::get('Chi tiết log thẻ cào') ?></h4>
                    </div>
                    <div class=panel-body>
                        <table class="table table-hover" width="100%">

                            <tr>
                                <th>ID</th>
                                <td><?= @$card_log['id'] ?></td>
                                <th>Version</th>
                                <td><?= @$card_log['version'] ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Loại thẻ') ?></th>
                                <td><?= Translate::get(@$card_log['card_type_info']['name']) ?></td>
                                <th><?= Translate::get('Loại hóa đơn') ?></th>
                                <td><?= Translate::get(@$card_log['bill_type_name']) ?></td>

                            </tr>
                            <tr>
                                <th><?= Translate::get('Mã thẻ') ?></th>
                                <td><?= @$card_log['card_code'] ?></td>
                                <th><?= Translate::get('Đối tác') ?></th>
                                <td><?= Translate::get(@$card_log['partner_card_info']['name']) ?></td>

                            </tr>
                            <tr>
                                <th><?= Translate::get('Serial thẻ') ?></th>
                                <td><?= @$card_log['card_serial'] ?></td>
                                <th><?= Translate::get('Mã tham chiếu với đối tác') ?></th>
                                <td><?= @$card_log['partner_card_refer_code'] ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Mệnh giá thẻ') ?></th>
                                <td>
                                    <?= ObjInput::makeCurrency(@$card_log['card_price']) ?>
                                    &nbsp;<?= @$card_log['currency'] ?>
                                </td>
                                <th><?= Translate::get('Log gạch thẻ với đối tác') ?></th>
                                <td><?= @$card_log['partner_card_log_id'] ?></td>

                            </tr>
                            <tr>
                                <th><?= Translate::get('Trạng thái') ?></th>
                                <td>
                                    <?php if ($card_log['card_status'] == CardLogFull::CARD_STATUS_FAIL) { ?>
                                        <span class="label label-danger"><?= Translate::get('Thẻ sai') ?></span>
                                    <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_TIMEOUT) { ?>
                                        <span class="label label-warning"><?= Translate::get('Thẻ timeout') ?></span>
                                    <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_NO_SUCCESS) { ?>
                                        <span
                                            class="label label-default"><?= Translate::get('Thẻ chưa bị gạch') ?></span>
                                    <?php } elseif ($card_log['card_status'] == CardLogFull::CARD_STATUS_SUCCESS) { ?>
                                        <span
                                            class="label label-success"><?= Translate::get('Thẻ bị gạch thành công') ?></span>
                                    <?php } ?>
                                    <hr>
                                    <?php if ($card_log['transaction_status'] == CardLogFull::TRANSACTION_STATUS_NEW) { ?>
                                        <span
                                            class="label label-default"><?= Translate::get('Chưa tạo giao dịch') ?></span>
                                    <?php } elseif ($card_log['transaction_status'] == CardLogFull::TRANSACTION_STATUS_CREATING) { ?>
                                        <span
                                            class="label label-primary"><?= Translate::get('Đang tạo giao dịch') ?></span>
                                    <?php } elseif ($card_log['transaction_status'] == CardLogFull::TRANSACTION_STATUS_CREATED) { ?>
                                        <span
                                            class="label label-success"><?= Translate::get('Đã tạo giao dịch') ?></span>
                                    <?php } elseif ($card_log['transaction_status'] == CardLogFull::TRANSACTION_STATUS_ERROR) { ?>
                                        <span
                                            class="label label-danger"><?= Translate::get('Lỗi khi tạo giao dịch') ?></span>
                                    <?php } ?>
                                    <hr>
                                    <?php if ($card_log['backup_status'] == CardLogFull::BACKUP_STATUS_NEW) { ?>
                                        <span class="label label-default"><?= Translate::get('Chưa backup') ?></span>
                                    <?php } elseif ($card_log['backup_status'] == CardLogFull::BACKUP_STATUS_CREATING) { ?>
                                        <span class="label label-primary"><?= Translate::get('Đang backup') ?></span>
                                    <?php } elseif ($card_log['backup_status'] == CardLogFull::BACKUP_STATUS_CREATED) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã backup') ?></span>
                                    <?php
                                    }?>
                                </td>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <td>
                                    <div class="small">
                                        <?php if (intval($card_log['time_created']) > 0): ?>
                                            <?= Translate::get('Tạo') ?>:
                                            <strong><?= date('H:i, d/m/Y', $card_log['time_created']) ?></strong>
                                            <br>
                                        <?php endif; ?>
                                        <?php if (intval($card_log['time_create_transaction']) > 0): ?>
                                            <?= Translate::get('Xử lý tạo GD') ?>:
                                            <strong><?= date('H:i, d/m/Y', $card_log['time_create_transaction']) ?></strong>
                                            <br>
                                        <?php endif; ?>
                                        <?php if (intval($card_log['time_backup']) > 0): ?>
                                            Backup: <strong><?= date('H:i, d/m/Y', $card_log['time_backup']) ?></strong>
                                            <br>
                                        <?php endif; ?>
                                        <?php if (intval($card_log['withdraw_time_limit']) > 0): ?>
                                            <?= Translate::get('Thời hạn rút') ?>:
                                            <strong><?= date('H:i, d/m/Y', $card_log['withdraw_time_limit']) ?></strong>
                                            <br>
                                        <?php endif; ?>
                                        <?php if (intval($card_log['time_updated']) > 0): ?>
                                            <?= Translate::get('Cập nhật') ?>:
                                            <strong><?= date('H:i, d/m/Y', $card_log['time_updated']) ?></strong><br>
                                        <?php endif; ?>
                                    </div>
                                </td>

                            </tr>

                            <tr>
                                <th>Merchant</th>
                                <td><?= Translate::get(@$card_log['merchant_info']['name']) ?></td>
                                <th><?= Translate::get('Số tiền trả merchant') ?></th>
                                <td>
                                    <?= ObjInput::makeCurrency(@$card_log['card_amount']) ?>
                                    &nbsp;<?= @$card_log['currency'] ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Mã tham chiếu với merchant') ?></th>
                                <td><?= @$card_log['merchant_refer_code'] ?></td>

                                <th><?= Translate::get('Kỳ thanh toán') ?></th>
                                <td><?= Translate::get(@$card_log['cycle_day_name']) ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Mã trả về cho Merchant') ?></th>
                                <td><?= @$card_log['result_code'] ?></td>
                                <th><?= Translate::get('Phần trăm phí') ?></th>
                                <td><?= @$card_log['percent_fee'] ?> %</td>

                            </tr>
                            <?php if (@$card_log['merchant_input'] != null) { ?>
                                <tr>
                                    <th colspan="4"><?= Translate::get('Đầu vào từ Merchant') ?></th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <div class="small">
                                            <?= @$card_log['merchant_input'] ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            if (@$card_log['merchant_output'] != null) {
                                ?>
                                <tr>
                                    <th colspan="4"><?= Translate::get('Đầu ra trả lại Merchant') ?></th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <div class="small">
                                            <?= @$card_log['merchant_output'] ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        </table>
                    </div>
                </div>

                <div class="col-lg-12" style="padding:20px 0px 30px">
                    <a href="<?= Yii::$app->urlManager->createUrl('card-log/index') ?>"
                       class="btn btn-danger btn-sm">
                        <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>