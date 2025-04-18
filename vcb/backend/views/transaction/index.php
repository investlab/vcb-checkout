<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Transaction;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Giao dịch thanh toán');
$this->params['breadcrumbs'][] = $this->title;

$array_color = array(
    Transaction::STATUS_NEW => 'bg bg-warning',
    Transaction::STATUS_PAYING => 'bg bg-warning',
    Transaction::STATUS_PAID => 'bg bg-success',
    Transaction::STATUS_CANCEL => 'bg bg-danger',
);
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Giao dịch thanh toán') ?></h1>
                <div class="option-buttons">
                    <div class="addNew">
                    <?php if (!empty($check_all_operators)) : ?>
                            <div class="dropdown otherOptions fr">
                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm" data-toggle="dropdown"
                                   role="button" aria-expanded="false"><?= Translate::get('Thêm') ?> <span class="caret"></span></a>
                                <ul class="dropdown-menu right" role="menu">
                                    <?php
                                    foreach ($check_all_operators as $key => $operator) :
                                        $router = isset($operator['router']) ? $operator['router'] : 'transaction/' . $key;
                                        ?>
                                        <li>
                                            <a href="<?= Yii::$app->urlManager->createUrl($router) ?>"><?= Translate::get($operator['title']) ?></a>
                                        </li>
                                     <?php endforeach; ?>
                                </ul>
                            </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class="well well-sm fillter">
            <form class="form-horizontal" role=form>
                <div class="row group-input-search">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian tạo từ') ?>"
                               name="time_created_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_created_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian thanh toán từ') ?>"
                               name="time_paid_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_paid_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_paid_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_paid_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian hủy từ') ?>"
                               name="time_cancel_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_cancel_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_cancel_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_cancel_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="transaction_type_id">
                            <?php
                            foreach ($transaction_type_search_arr as $key => $rs) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->transaction_type_id == $key) ? "selected='true'" : '' ?> >
                                <?= Translate::get($rs) ?>
                                </option>
<?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <select class="form-control" name="branch_id" id="select-branch-id" data-url="<?= Yii::$app->urlManager->createUrl('checkout-order/get-merchant-by-branch') ?>">
                            <?php
                            foreach ($branchs as $key => $branch) {
                                ?>
                                <option
                                        value="<?= $key ?>" <?= (isset($search) && $search->branch_id == $key) ? "selected='true'" : '' ?> >
                                    <?= Translate::get($branch) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="merchant_id" id="select-merchant-id">
                            <?php
                            foreach ($merchant_search_arr as $key => $rs) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->merchant_id == $key) ? "selected='true'" : '' ?> >
                                <?= Translate::get($rs) ?>
                                </option>
<?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="payment_method_id" id="payment_method_id">
                            <?php
                            foreach ($payment_method_search_arr as $key => $data) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->payment_method_id == $key) ? "selected='true'" : '' ?> >
                                <?= Translate::get($data) ?>
                                </option>
<?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="partner_payment_id">
                            <?php
                            foreach ($partner_payment_search_arr as $key => $data) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->partner_payment_id == $key) ? "selected='true'" : '' ?> >
                                <?= Translate::get($data) ?>
                                </option>
<?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã tham chiếu với kênh thanh toán') ?>"
                               name="partner_payment_method_refer_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->partner_payment_method_refer_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã giao dịch bên ngân hàng') ?>"
                               name="bank_refer_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->bank_refer_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã giao dịch') ?>"
                               name="id"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->id) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã đơn hàng') ?>"
                               name="order_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->order_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã token đơn hàng') ?>"
                               name="token_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->token_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Thông tin người thanh toán') ?>"
                               name="buyer_info"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->buyer_info) : '' ?>">
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã giao dịch liên quan') ?>"
                               name="refer_transaction_id"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->refer_transaction_id) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã phiếu chi') ?>"
                               name="cashout_id"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->cashout_id) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control selectpicker" name="status[]" multiple=""
                                title="<?= Translate::get('Trạng thái') ?>">
                                    <?php
                                    foreach ($status_arr as $key => $ss) {
                                        ?>
                                <option
                                    value="<?= $key ?>" <?= !empty($search->status) && in_array($key, $search->status) ? "selected='true'" : '' ?> >
                                <?= Translate::get($ss) ?>
                                </option>
<?php } ?>
                        </select>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('transaction/index') ?>"
                           class="btn btn-default">
<?= Translate::get('Bỏ lọc') ?>
                        </a>
                    </div>
                </div>

            </form>
        </div>
        <div class=row>
            <div class=col-md-12>
                <div class="clearfix" style="border-bottom:1px solid #dcdcdc; margin-bottom:15px; padding-bottom:10px">
                    <div class="col-md-12" style="margin-left:-15px"><?= Translate::get('Có') ?> <strong
                            class="text-danger"><?php echo $page->pagination->totalCount; ?></strong>
                        <?= Translate::get('Giao dịch') ?>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Chờ xử lý') ?> <strong
                            class="text-danger"><?= (isset($page->count_new) ? $page->count_new : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đang thanh toán') ?> <strong
                            class="text-danger"><?= (isset($page->count_paying) ? $page->count_paying : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đã thanh toán') ?> <strong
                            class="text-danger"><?= (isset($page->count_paid) ? $page->count_paid : '0') ?></strong>
                        &nbsp;|&nbsp;
<?= Translate::get('Đã hủy') ?> <strong
                            class="text-danger"><?= (isset($page->count_cancel) ? $page->count_cancel : '0') ?></strong>
                    </div>
                    <br><br>
                    <div class="col-md-12" style="margin-left:-15px">
                        <div class="col-sm-10 no-padding">
                            <?= Translate::get('Tổng số tiền giao dịch') ?> : <strong
                                class="text-danger"><?= ObjInput::makeCurrency(@$page->total_amount) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
                            &nbsp;|&nbsp;
                            <?= Translate::get('Tổng phí') ?>: <strong
                                class="text-danger"><?= ObjInput::makeCurrency(@$page->total_fee) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
                        </div>
                        <div class="col-sm-2 no-padding" align="right">

                            <a class="btn btn-primary" href="#" onclick="exportData.set('<?= Translate::get('Trích xuất giao dịch') ?>',
                                '<?= Yii::$app->urlManager->createUrl('transaction') . \common\components\utils\Utilities::buidLinkExcel('export') ?>');
                                    return false;">
                                <i class="icon-excel"></i> <?= Translate::get('Xuất excel') ?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        if (is_array($page->data) && count($page->data) == 0) {
                            ?>

                            <div class="alert alert-danger fade in">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
                            </div>
<?php } ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0"
                                   width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?= Translate::get('Thông tin giao dịch') ?></th>
                                        <th><?= Translate::get('Hình thức thanh toán') ?></th>
                                        <th><?= Translate::get('Số tiền') ?></th>
                                        <th><?= Translate::get('Trạng thái') ?></th>
                                        <th class="text-center"><?= Translate::get('Thao tác') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (is_array($page->data) && count($page->data) > 0) {
                                        foreach ($page->data as $key => $data) {
                                            ?>                        
                                            <tr class="<?= @$array_color[$data['status']] ?>">
                                                <td>
        <?= isset($data['id']) && $data['id'] != null ? $data['id'] : "" ?>
                                                </td>
                                                <td class="col-sm-3">
                                                    <div class="small">
                                                        Loại: <strong><?= $data['transaction_type_info']['name'] ?></strong>
                                                        <hr>
                                                        Merchant: <strong><?= @$data['merchant_info']['name'] ?></strong>
                                                        <?php if (intval($data['refer_transaction_id']) != 0) { ?>
                                                            <hr>
                                                            <?= Translate::get('Mã GD liên quan') ?>:
                                                            <strong><?= intval($data['refer_transaction_id']) != 0 ? $data['refer_transaction_id'] : "" ?></strong>
                                                        <?php } ?>
                                                        <hr>
                                                        <?php if (\common\models\db\TransactionType::isWithdrawTransactionType($data['transaction_type_id'])) : ?>
                                                            <?= Translate::get('Mã phiếu chi') ?>: <strong><?= @$data['cashout_info']['id'] ?></strong>
                                                        <?php elseif (\common\models\db\TransactionType::isDepositTransactionType($data['transaction_type_id'])) : ?>

                                                        <?php else: ?>
                                                            <?= Translate::get('Mã token đơn hàng') ?>:
                                                            <strong><?= @$data['checkout_order_info']['token_code'] ?></strong>
        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="col-sm-3">
                                                    <div class="small">
                                                        <strong><?= isset($data['payment_method_info']['name']) && $data['payment_method_info']['name'] != null ? Translate::get($data['payment_method_info']['name']) : "" ?></strong>

                                                        <?php if (\common\models\db\TransactionType::isPaymentTransactionType($data['transaction_type_id']) || \common\models\db\TransactionType::isInstallmentTransactionType($data['transaction_type_id'])): ?>
                                                            <hr>
                                                            <?= Translate::get('Kênh thanh toán') ?>: <strong
                                                                class="text-magenta"><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong>
                                                            <br>
                                                        <?php elseif (\common\models\db\TransactionType::isWithdrawTransactionType($data['transaction_type_id'])): ?>
                                                            <hr>
                                                            <?= Translate::get('Kênh rút tiền') ?>: <strong
                                                                class="text-magenta"><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong>
                                                            <br>
                                                            <?php elseif (\common\models\db\TransactionType::isRefundTransactionType($data['transaction_type_id'])):
                                                            ?>
                                                            <hr>
                                                            <?= Translate::get('Kênh hoàn tiền') ?>: <strong
                                                                class="text-magenta"><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong>
                                                            <br>
                                                            <?php elseif (\common\models\db\TransactionType::isDepositTransactionType($data['transaction_type_id'])):
                                                            ?>
                                                            <hr>
                                                            <?= Translate::get('Deposit channel') ?>: <strong
                                                                class="text-magenta"><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong>
                                                            <br>    
                                                        <?php endif; ?>                                   
                                                        <?php if (empty(@$data['partner_payment_account_id'])) : ?>
                                                            <hr>
                                                            <?php if (\common\models\db\TransactionType::isPaymentTransactionType($data['transaction_type_id'])): ?>
                                                                <?= Translate::get('Tài khoản nhận tiền') ?>: <strong><?= @$data['partner_payment_account_info']['partner_payment_account'] ?></strong>
                                                            <?php else: ?>
                                                                <?= Translate::get('Tài khoản chuyển tiền') ?>: <strong><?= @$data['partner_payment_account_info']['partner_payment_account'] ?></strong>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if (@$data['bank_refer_code'] != null) : ?>
                                                            <hr>
            <?= Translate::get('Mã tham chiếu với ngân hàng') ?>: <strong><?= @$data['bank_refer_code'] ?></strong>
        <?php endif; ?>

                                                    </div>
                                                </td>
                                                <td class="col-sm-3 text-right">
                                                    <div class="small">
                                                            <?= Translate::get('Số tiền giao dịch') ?> : <strong
                                                            class="text-primary"><?= ObjInput::makeCurrency($data['amount']) ?> <?= $data['currency'] ?></strong><hr>
                                                        <?php if (\common\models\db\TransactionType::isPaymentTransactionType($data['transaction_type_id'])): ?>
                                                            <?php if (@$data['sender_fee'] > 0) { ?>
                                                                <?= Translate::get('Phí thu NM') ?>:
                                                                <span>+<?= ObjInput::makeCurrency($data['sender_fee']) ?> <?= $data['currency'] ?></span><br> <hr>
                                                            <?php } ?>
                                                            <?= Translate::get('Số tiền gửi sang kênh TT') ?>: <strong class="text-magenta">  <?= ObjInput::makeCurrency($data['amount'] + $data['sender_fee']) ?> <?= $data['currency'] ?></strong><hr>
            <?php if (@$data['partner_payment_sender_fee'] > 0) { ?>
                                                                <?= Translate::get('Phí kênh TT thu NM') ?>:
                                                                <span>+<?= ObjInput::makeCurrency(@$data['partner_payment_sender_fee']) ?> <?= $data['currency'] ?></span><br>
                                                                <hr>
                                                                <?php } ?>
                                                                <?= Translate::get('Tổng tiền NM TT') ?>: <strong
                                                                class="text-magenta">  <?= ObjInput::makeCurrency($data['amount'] + $data['sender_fee'] + $data['partner_payment_sender_fee']) ?> <?= $data['currency'] ?></strong><hr>
            <?php if (@$data['receiver_fee'] > 0) { ?>
                                                                <?= Translate::get('Phí thu merchant') ?>:
                                                                <span>-<?= ObjInput::makeCurrency($data['receiver_fee']) ?> <?= $data['currency'] ?></span><br>
                                                                <hr>
                                                                <?php } ?>
                                                                <?= Translate::get('Số tiền merchant nhận được') ?>: <strong
                                                                class="text-magenta">  <?= ObjInput::makeCurrency($data['amount'] - $data['receiver_fee']) ?> <?= $data['currency'] ?></strong>
                                                        <?php elseif (\common\models\db\TransactionType::isWithdrawTransactionType($data['transaction_type_id'])): ?>
            <?php if (@$data['receiver_fee'] > 0) { ?>
                                                                <?= Translate::get('Phí thu merchant') ?>:
                                                                <span>-<?= ObjInput::makeCurrency($data['sender_fee']) ?> <?= $data['currency'] ?></span><br>
                                                                <hr>
            <?php } ?>
                                                            <?= Translate::get('Số tiền gửi sang kênh rút tiền') ?>: <strong
                                                                class="text-magenta"><?= ObjInput::makeCurrency($data['amount']) ?> <?= $data['currency'] ?></strong>
                                                            <hr>
            <?php if (@$data['partner_payment_receiver_fee'] > 0) { ?>
                <?= Translate::get('Phí kênh rút tiền thu merchant') ?>:
                                                                <span>-<?= ObjInput::makeCurrency(@$data['partner_payment_receiver_fee']) ?> <?= $data['currency'] ?></span>
                                                                <br>
                                                                <hr>
            <?php } ?>
                                                            <?= Translate::get('Số tiền merchant nhận được') ?>: <strong
                                                                class="text-magenta">  <?= ObjInput::makeCurrency($data['amount'] - $data['partner_payment_receiver_fee']) ?> <?= $data['currency'] ?></strong>
                                                            <hr>
            <?php if (@$data['partner_payment_sender_fee'] > 0) { ?>
                                                                <?= Translate::get('Phí kênh rút tiền thu SEAPAY') ?>:
                                                                <span>+<?= ObjInput::makeCurrency(@$data['partner_payment_sender_fee']) ?> <?= $data['currency'] ?></span>
                                                                <br>
                                                            <?php } ?>
                                                            <?php elseif (\common\models\db\TransactionType::isRefundTransactionType($data['transaction_type_id'])):
                                                            ?>
            <?php if (@$data['sender_fee'] > 0) { ?>
                <?= Translate::get('Phí thu merchant') ?>:
                                                                <span>-<?= ObjInput::makeCurrency($data['sender_fee']) ?> <?= $data['currency'] ?></span>
                                                                <br>
                                                                <hr>
            <?php } ?>
                                                                <?= Translate::get('Số tiền gửi sang kênh hoàn tiền') ?>: 
                                                            <strong
                                                                class="text-magenta">  <?= ObjInput::makeCurrency($data['amount']) ?> <?= $data['currency'] ?></strong><hr>
            <?php if (@$data['partner_payment_receiver_fee'] > 0) { ?>    
                <?= Translate::get('Phí kênh hoàn tiền thu NM') ?>:
                                                                <span>-<?= ObjInput::makeCurrency(@$data['partner_payment_receiver_fee']) ?> <?= $data['currency'] ?></span>
                                                                <br>
                                                                <hr>  
                                                                <?php } ?>
                                                                <?= Translate::get('Số tiền NM nhận được') ?>: <strong
                                                                class="text-magenta">  <?= ObjInput::makeCurrency($data['amount'] - $data['partner_payment_receiver_fee']) ?> <?= $data['currency'] ?></strong><hr>
                                                            <?php if (@$data['partner_payment_sender_fee'] > 0) { ?>        
                                                                <?= Translate::get('Phí kênh hoàn tiền thu SEAPAY') ?>:
                                                                <span>+<?= ObjInput::makeCurrency(@$data['partner_payment_sender_fee']) ?> <?= $data['currency'] ?></span><br>
            <?php } ?>
        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <td class="col-sm-2 text-right">
                                                    <?php if ($data['status'] == Transaction::STATUS_NEW) { ?>
                                                        <span class="label label-default"><?= Translate::get('Chờ xử lý') ?></span>
                                                    <?php } else if ($data['status'] == Transaction::STATUS_PAYING) { ?>
                                                        <span class="label label-warning"><?= Translate::get('Đang xử lý') ?></span>    
                                                    <?php } elseif ($data['status'] == Transaction::STATUS_PAID) { ?>
                                                        <span class="label label-success"><?= Translate::get('Đã hoàn thành') ?></span>
                                                    <?php } elseif ($data['status'] == Transaction::STATUS_PAID) { ?>
                                                        <span class="label label-success"><?= Translate::get('Đã hoàn thành') ?></span>
        <?php } elseif ($data['status'] == Transaction::STATUS_CANCEL) { ?>
                                                        <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                                        <br><br>
                                                        <div class="small">
                                                            <i class="text-danger"> <?= Translate::get('Lý do hủy') ?> :
            <?= $data['reason_info']['name'] ?><br>
            <?= Translate::get('Mô tả') ?> : <?= $data['reason'] ?>
                                                            </i>
                                                        </div>

                                                        <?php } ?>
                                                    <hr>
                                                    <div class="small text-right">
                                                        <?php if (intval($data['time_created']) > 0): ?>
                                                            <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong><br>
                                                        <?php endif; ?>
                                                        <?php if (intval($data['time_paid']) > 0): ?>
                                                            <?= Translate::get('Thanh toán') ?>:
                                                            <strong><?= date('H:i, d/m/Y', $data['time_paid']) ?></strong><br>
                                                        <?php endif; ?>
                                                        <?php if (intval($data['time_cancel']) > 0): ?>
                                                            <?= Translate::get('Hủy') ?>: <strong><?= date('H:i, d/m/Y', $data['time_cancel']) ?></strong><br>
                                                        <?php endif; ?>
                                                        <?php if (intval($data['time_updated']) > 0): ?>
                                                            <?= Translate::get('Cập nhật') ?>:
                                                            <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
        <?php if (!empty($data["operators"])) { ?>
                                                        <div class="dropdown otherOptions fr">
                                                            <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                               data-toggle="dropdown"
                                                               role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                                    class="caret"></span></a>
                                                            <ul class="dropdown-menu right" role="menu">
                                                                <?php
                                                                foreach ($data["operators"] as $items => $operator) {
                                                                    $router = isset($operator['router']) ? $operator['router'] : 'transaction/' . $items;
                                                                    $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                                    ?>
                                                                    <li>
                                                                        <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                                    <?= Translate::get($operator['title']) ?>
                                                                        </a>
                                                                    </li>
                                                        <?php } ?>
                                                            </ul>
                                                        </div>
        <?php } ?>
                                                </td>
                                            </tr>


        <?php
    }
}
?>
                                </tbody>        
                            </table>
                        </div>
                        <div class="box-control">
                            <div class="pagination-router">
                                <?=
                                \yii\widgets\LinkPager::widget([
                                    'pagination' => $page->pagination,
                                    'nextPageLabel' => Translate::get('Tiếp'),
                                    'prevPageLabel' => Translate::get('Sau'),
                                    'maxButtonCount' => 5
                                ]);
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-dialog" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title title"></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <div class="alert alert-warning fade in" align="center">
<?= Translate::get('Bạn có chắc chắn muốn') ?> <strong class="title"> </strong> <?= Translate::get('cho đơn thanh toán này không') ?>?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#"><?= Translate::get('Xác nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">
    $('#payment_method_id').select2();
<?php echo Yii::$app->view->renderFile('@app/web/js/ajax.js', array()); ?>
    function confirm(title, url) {
        $('#confirm-dialog .title').html(title);
        $('#confirm-dialog').modal('show');
        $('#confirm-dialog .btn-accept').click(function () {
            document.location.href = url;
        });
    }

    $('#select-branch-id').change(function () {
        var url = $(this).data('url');
        var branch_id = $(this).val();

        $.ajax({
            type: 'post',
            url: url,
            data: {
                branch_id: branch_id,
            }, success: function (res) {
                var data = JSON.parse(res);
                var string = '';
                $('#select-merchant-id').html('');
                data.forEach(function (item, index) {
                    string += '<option value="'+ item.id +'">'+ item.name +'</option>';
                });
                $('#select-merchant-id').html(string);
            }
        });
    });

</script>