<?php
use common\components\utils\Translate;
use common\components\utils\ObjInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\db\Transaction;

$this->title = Translate::get('Chi tiết giao dịch thanh toán');
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
                <h1 class=page-header><?= Translate::get('Chi tiết giao dịch') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('transaction/index') ?>">
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
                        <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
                    </div>
                    <div class=panel-body>
                        <table class="table table-hover" width="100%">
                            <tr>
                                <th><?= Translate::get('Loại giao dịch') ?></th>
                                <td>
                                    <?= isset($transaction['transaction_type_info']['name']) && $transaction['transaction_type_info']['name'] != null ? $transaction['transaction_type_info']['name'] : "" ?>
                                </td>
                                <th>Merchant</th>
                                <td>
                                    <?= isset($transaction['merchant_info']['name']) && $transaction['merchant_info']['name'] != null ? $transaction['merchant_info']['name'] : "" ?>
                                </td>
                            </tr>
                            <?php if (\common\models\db\TransactionType::isPaymentTransactionType($transaction['transaction_type_id']) || \common\models\db\TransactionType::isInstallmentTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Mã đơn hàng') ?></th>
                                <td>
                                    <?= isset($transaction['checkout_order_info']['order_code']) && $transaction['checkout_order_info']['order_code'] != null ? $transaction['checkout_order_info']['order_code'] : "" ?>
                                </td>
                                <th><?= Translate::get('Phương thức thanh toán') ?></th>
                                <td>
                                    <?= isset($transaction['payment_method_info']['name']) && $transaction['payment_method_info']['name'] != null ? $transaction['payment_method_info']['name'] : "" ?>
                                </td>
                            </tr>
                            <?php elseif (\common\models\db\TransactionType::isWithdrawTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Mã phiếu chi') ?></th>
                                <td>
                                    <?= $transaction['cashout_id'] ?>
                                </td>
                                <th><?= Translate::get('Phương thức rút tiền') ?></th>
                                <td>
                                    <?= isset($transaction['payment_method_info']['name']) && $transaction['payment_method_info']['name'] != null ? $transaction['payment_method_info']['name'] : "" ?>
                                </td>
                            </tr>
                            <?php elseif (\common\models\db\TransactionType::isRefundTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Mã đơn hàng') ?></th>
                                <td>
                                    <?= isset($transaction['checkout_order_info']['order_code']) && $transaction['checkout_order_info']['order_code'] != null ? $transaction['checkout_order_info']['order_code'] : "" ?>
                                </td>
                                <th><?= Translate::get('Phương thức hoàn tiền') ?></th>
                                <td>
                                    <?= isset($transaction['payment_method_info']['name']) && $transaction['payment_method_info']['name'] != null ? $transaction['payment_method_info']['name'] : "" ?>
                                </td>
                            </tr>
                            <?php endif;?>
                            <tr>
                                <th><?= Translate::get('Số tiền giao dịch') ?></th>
                                <td>
                                    <?= ObjInput::makeCurrency($transaction['amount'])?>&nbsp;<?= $transaction['currency']?>
                                </td>
                                <th><?= Translate::get('Kênh thanh toán') ?></th>
                                <td>
                                    <?= isset($transaction['partner_payment']['name']) && $transaction['partner_payment']['name'] != null ? $transaction['partner_payment']['name'] : "" ?>
                                </td>
                            </tr>
                            <?php if (\common\models\db\TransactionType::isPaymentTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Phí thu người mua') ?></th>
                                <td><?= ObjInput::makeCurrency($transaction['sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                                <th><?= Translate::get('Phí kênh TT thu người mua') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Phí thu Merchant') ?></th>
                                <td><?= ObjInput::makeCurrency($transaction['receiver_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                                <th><?= Translate::get('Phí kênh TT thu Xpay') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_receiver_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <?php elseif (\common\models\db\TransactionType::isWithdrawTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Phí thu Merchant') ?></th>
                                <td><?= ObjInput::makeCurrency($transaction['sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                                <th><?= Translate::get('Phí kênh rút tiền thu Xpay') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <tr>
                                <th colspan="2"></th>
                                <th><?= Translate::get('Phí kênh rút tiền thu Merchant') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_receiver_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <?php elseif (\common\models\db\TransactionType::isRefundTransactionType($transaction['transaction_type_id'])):?>
                            <tr>
                                <th><?= Translate::get('Phí thu Merchant') ?></th>
                                <td><?= ObjInput::makeCurrency($transaction['sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                                <th><?= Translate::get('Phí kênh hoàn tiền thu Xpay') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_sender_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <tr>
                                <th colspan="2"></th>
                                <th><?= Translate::get('Phí kênh hoàn tiền thu người mua') ?></th>
                                <td><?= ObjInput::makeCurrency(@$transaction['partner_payment_receiver_fee'])?>&nbsp;<?= $transaction['currency']?></td>
                            </tr>
                            <?php endif;?>
                            <tr>
                                <th><?= Translate::get('Giao dịch liên quan') ?></th>
                                <td>
                                    <?= isset($transaction['refer_transaction_id']) && $transaction['refer_transaction_id'] != null ? $transaction['refer_transaction_id'] : 0 ?>
                                </td>
                                <th><?= Translate::get('Mã tham chiếu kênh thanh toán') ?></th>
                                <td>
                                    <?= isset($transaction['partner_payment_method_refer_code']) && $transaction['partner_payment_method_refer_code'] != null ? $transaction['partner_payment_method_refer_code'] : "" ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Trạng thái') ?></th>
                                <td>
                                    <?php if ($transaction['status'] == Transaction::STATUS_NEW) { ?>
                                        <span class="label label-warning"><?= Translate::get('Chờ xử lý') ?></span>
                                    <?php } elseif ($transaction['status'] == Transaction::STATUS_PAID) { ?>
                                        <span class="label label-success"><?= Translate::get('Đã hoàn thành') ?></span>
                                    <?php } elseif ($transaction['status'] == Transaction::STATUS_CANCEL) { ?>
                                        <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                        <br><br>
                                        <div class="small">
                                            <i class="text-danger"> <?= Translate::get('Lý do hủy') ?> :
                                                <?= isset($data['reason_info']['name']) && $data['reason_info']['name'] != null ? $data['reason_info']['name'] : '' ?>
                                                <br>
                                                <?= Translate::get('Mô tả') ?> : <?= $transaction['reason'] ?>
                                            </i>
                                        </div>

                                    <?php } ?>
                                </td>
                                <th><?= Translate::get('Mã giao dịch bên ngân hàng') ?></th>
                                <td>
                                    <?= isset($transaction['bank_refer_code']) && $transaction['bank_refer_code'] != null ? $transaction['bank_refer_code'] : "" ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <td colspan="3">
                                    <div class="small">
                                        <?= Translate::get('Tạo') ?>:
                                        <strong><?= isset($transaction['time_created']) && intval($transaction['time_created']) > 0 ? date('H:i,d-m-Y', $transaction['time_created']) : '' ?></strong>
                                        <br>
                                        <?= Translate::get('Thanh toán') ?>
                                        :
                                        <strong><?= isset($transaction['time_paid']) && intval($transaction['time_paid']) > 0 ? date('H:i,d-m-Y', $transaction['time_paid']) : '' ?></strong>
                                        <br>
                                        <?= Translate::get('Cập nhật') ?>
                                        :
                                        <strong><?= isset($transaction['time_updated']) && intval($transaction['time_updated']) > 0 ? date('H:i,d-m-Y', $transaction['time_updated']) : '' ?></strong>
                                        <br>
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