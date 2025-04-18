<?php

use common\components\utils\ObjInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\db\Cashout;
use common\models\db\Method;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết yêu cầu rút tiền');
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
                           href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>">
                            <?= Translate::get('Quay lại') ?>
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
                        <h4><?= Translate::get('Chi tiết yêu cầu rút tiền') ?></h4>
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
                                    <?= isset($cashout['merchant_info']['name']) && $cashout['merchant_info']['name'] != null ? $cashout['merchant_info']['name'] : "" ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Nhóm phương thức rút tiền') ?></th>
                                <td>
                                    <?= isset($cashout['method_info']['name']) && $cashout['method_info']['name'] != null ? Translate::get($cashout['method_info']['name']) : "" ?>
                                </td>
                                <th><?= Translate::get('Số tiền rút') ?><br><br> <?= Translate::get('Phí rút') ?>:</th>
                                <td>
                                    <?= isset($cashout['amount']) && $cashout['amount'] != null ? ObjInput::makeCurrency($cashout['amount']) : 0 ?>
                                    &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND']?>
                                    <br><br>
                                    <?= isset($cashout['receiver_fee']) && $cashout['receiver_fee'] != null ? ObjInput::makeCurrency($cashout['receiver_fee']) : 0 ?>
                                    &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND']?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Phương thức rút tiền') ?></th>
                                <td>
                                    <?= isset($cashout['payment_method_info']['name']) && $cashout['payment_method_info']['name'] != null ? Translate::get($cashout['payment_method_info']['name']) : "" ?>
                                </td>

                                <th><?= Translate::get('Kênh rút tiền') ?></th>
                                <td>
                                    <?= isset($cashout['partner_payment_info']['name']) && $cashout['partner_payment_info']['name'] != null ? Translate::get($cashout['partner_payment_info']['name']) : "" ?>
                                </td>

                            </tr>
                            <tr>
                                <th><?= Translate::get('Loại') ?></th>
                                <td><?= @$cashout['type_name'] ?></td>
                                <th><?= Translate::get('Giao dịch rút tiền') ?></th>
                                <td>
                                    <?= isset($cashout['transaction_id']) && $cashout['transaction_id'] != null ? $cashout['transaction_id'] : "" ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php if (Method::isWithdrawIBOffline(@$cashout['method_info']['code'])) :?>
                                        <?= Translate::get('Số tài khoản') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                                        <?= Translate::get('Tên chủ tài khoản') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                                        <?= Translate::get('Chi nhánh') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_branch'] ?></strong><br>
                                    <?php elseif (Method::isWithdrawATMCard(@$cashout['method_info']['code'])):?>
                                        <?= Translate::get('Số thẻ') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                                        <?= Translate::get('Tên chủ thẻ') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                                        <?= Translate::get('Ngày phát hành') ?>: <strong class="text-magenta"><?= @$cashout['bank_card_month'] ?>/<?= @$cashout['bank_card_year'] ?></strong><br>
                                    <?php elseif (Method::isWithdrawWallet(@$cashout['method_info']['code'])):?>
                                        <?= Translate::get('Email tài khoản') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_code'] ?></strong><br>
                                        <?= Translate::get('Tên chủ tài khoản') ?>: <strong class="text-magenta"><?= @$cashout['bank_account_name'] ?></strong><br>
                                    <?php endif;?>
                                </th>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>


                            <tr>
                                <th><?= Translate::get('Trạng thái') ?></th>
                                <td>
                                    <?php if ($cashout['status'] == Cashout::STATUS_VERIFY) { ?>
                                        <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
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
                                                <?= Translate::get('Mô tả') ?> : <?= $cashout['reason'] ?>
                                            </i>
                                        </div>
                                    <?php } ?>

                                </td>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <td>
                                    <div class="small">
                                        <?php if (intval($cashout['time_created']) > 0):?>
                                            <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $cashout['time_created']) ?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_request']) > 0):?>
                                            <?= Translate::get('Yêu cầu') ?>: <strong><?= date('H:i, d/m/Y', $cashout['time_request']) ?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_accept']) > 0):?>
                                            <?= Translate::get('Duyệt') ?>: <strong><?= date('H:i, d/m/Y', $cashout['time_accept']) ?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_reject']) > 0):?>
                                            <?= Translate::get('Từ chối') ?>: <strong><?= date('H:i, d/m/Y', $cashout['time_reject']) ?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_paid']) > 0):?>
                                            <?= Translate::get('Chuyển ngân') ?>: <strong><?=  date('H:i, d/m/Y', $cashout['time_paid']) ?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_cancel']) > 0):?>
                                            <?= Translate::get('Hủy') ?>: <strong><?=date('H:i, d/m/Y', $cashout['time_cancel'])?></strong><br>
                                        <?php endif;?>
                                        <?php if (intval($cashout['time_updated']) > 0):?>
                                            <?= Translate::get('Cập nhật') ?>: <strong><?=  date('H:i, d/m/Y', $cashout['time_updated']) ?></strong><br>
                                        <?php endif;?>
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>