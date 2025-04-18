<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\CheckoutOrder;
use common\components\utils\Translate;
use common\models\db\CheckoutOrderCallbackHistory;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Quản lý đơn thanh toán');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    CheckoutOrder::STATUS_NEW => 'bg bg-default',
    CheckoutOrder::STATUS_PAYING => 'bg bg-warning',
    CheckoutOrder::STATUS_PAID => 'bg bg-success',
    CheckoutOrder::STATUS_CANCEL => 'bg bg-danger',
    CheckoutOrder::STATUS_REVIEW => 'bg bg-warning',
    CheckoutOrder::STATUS_WAIT_REFUND => 'bg bg-warning',
    CheckoutOrder::STATUS_REFUND => 'bg bg-success',
    CheckoutOrder::STATUS_REFUND_PARTIAL => 'bg bg-success',
    CheckoutOrder::STATUS_WAIT_WIDTHDAW => 'bg bg-warning',
    CheckoutOrder::STATUS_WIDTHDAW => 'bg bg-success',
    CheckoutOrder::STATUS_INSTALLMENT_WAIT => 'bg bg-warning'
);
?>

<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Quản lý đơn thanh toán') ?></h1>
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
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('TG tạo từ') ?>"
                               name="time_created_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_created_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('TG thanh toán từ') ?>"
                               name="time_paid_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_paid_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_paid_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_paid_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('TG notify từ') ?>"
                               name="time_success_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_success_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_success_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_success_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('TG hoàn tiền từ') ?>"
                               name="time_refund_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_refund_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_refund_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_refund_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('Thời hạn TT từ') ?>"
                               name="time_limit_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_limit_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker"
                               placeholder="<?= Translate::get('đến ngày') ?>"
                               name="time_limit_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_limit_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control"
                               placeholder="Order Code NL/SP-<?= Translate::get('Mã giao dịch') ?>"
                               name="transaction_id"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->transaction_id) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control"
                               placeholder="<?= Translate::get('Thông tin người TT') ?>"
                               name="buyer_info"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->buyer_info) : '' ?>">
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã đơn hàng') ?>"
                               name="order_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->order_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã token') ?>"
                               name="token_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->token_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <select class="form-control" name="branch_id" id="select-branch-id"
                                data-url="<?= Yii::$app->urlManager->createUrl('checkout-order/get-merchant-by-branch') ?>">
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
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
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
                        <select class="form-control selectpicker" name="status[]" multiple=""
                                title="<?= Translate::get('Trạng thái đơn TT') ?>">
                            <?php
                            unset($status_arr[1]);
                            unset($status_arr[5]);
                            foreach ($status_arr as $keyS => $dataS) {
                                ?>
                                <option
                                        value="<?= $keyS ?>" <?= !empty($search->status) && in_array($keyS, $search->status) ? "selected='true'" : '' ?> >
                                    <?= Translate::get($dataS) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control selectpicker" name="callback_status[]" multiple=""
                                title="<?= Translate::get('Trạng thái gọi merchant') ?>">
                            <?php
                            foreach ($callback_status_arr as $keyCS => $dataCS) {
                                ?>
                                <option
                                        value="<?= $keyCS ?>" <?= !empty($search->callback_status) && in_array($keyCS, $search->callback_status) ? "selected='true'" : '' ?> >
                                    <?= Translate::get($dataCS) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('checkout-order/index') ?>"
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
                        <?= Translate::get('Đơn TT') ?>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Chưa thanh toán') ?> <strong
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
                        &nbsp;|&nbsp;
                        <?= Translate::get('Bị review') ?> <strong
                                class="text-danger"><?= (isset($page->count_review) ? $page->count_review : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đợi hoàn tiền') ?> <strong
                                class="text-danger"><?= (isset($page->count_wait_refund) ? $page->count_wait_refund : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đã hoàn tiền') ?> <strong
                                class="text-danger"><?= (isset($page->count_refund) ? $page->count_refund : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đợi rút tiền') ?> <strong
                                class="text-danger"><?= (isset($page->count_wait_widthdaw) ? $page->count_wait_widthdaw : '0') ?></strong>
                        &nbsp;|&nbsp;
                        <?= Translate::get('Đã rút tiền') ?> <strong
                                class="text-danger"><?= (isset($page->count_widthdaw) ? $page->count_widthdaw : '0') ?></strong>
                    </div>
                    <br><br>

                    <div class="col-sm-12 no-padding">
                        <div class="col-sm-10 no-padding">
                            <?= Translate::get('Total') ?> : <strong
                                    class="text-danger"><?= ObjInput::makeCurrency(@$page->total_cashin_amount) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
                            &nbsp;|&nbsp;
                            <?= Translate::get('Tổng số tiền được rút') ?>: <strong
                                    class="text-danger"><?= ObjInput::makeCurrency(@$page->total_cashout_amount) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
                        </div>
                        <div class="col-sm-2 no-padding" align="right">

                            <a class="btn btn-primary" href="#"
                               onclick="exportData.set('<?= Translate::get('Trích xuất giao dịch') ?>',
                                       '<?= Yii::$app->urlManager->createUrl('checkout-order') . \common\components\utils\Utilities::buidLinkExcel('export') ?>');
                                       return false;">
                                <i class="icon-excel"></i> <?= Translate::get('Xuất excel') ?></a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    if (is_array($page->data) && count($page->data) == 0) {
                        ?>

                        <div class="alert alert-danger fade in">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>
                            .
                        </div>
                    <?php } ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th><?= Translate::get('Đơn hàng') ?></th>
                                <th><?= Translate::get('GD thanh toán') ?></th>
                                <th><?= Translate::get('Hoàn tiền') ?></th>
                                <th><?= Translate::get('Trạng thái') ?></th>
                                <th><?= Translate::get('Thao tác') ?></th>
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

                                                <?= Translate::get('Mã ĐH') ?>:
                                                <strong><?= @$data['order_code'] ?></strong>
                                                <hr>
                                                <?= Translate::get('Số tiền') ?>: <strong
                                                        class="text-danger"><?= ObjInput::makeCurrency($data['amount']) ?> <?= $data['currency'] ?></strong>

                                                <hr>
                                                <?= Translate::get('Merchant:') ?>
                                                <strong><?= @$data['merchant_info']['name'] ?></strong>
                                                <hr>
                                                <?= Translate::get('Mã token') ?>: <strong
                                                        class="text-primary"><?= @$data['token_code'] ?></strong>
                                                <hr>
                                                Version: <strong><?= @$data['version'] ?></strong>
                                                <hr>
                                                <?php if (intval($data['transaction_id']) > 0) { ?>
                                                    <?= Translate::get('Mã hóa đơn NL') ?>: <strong
                                                            class="text-primary"><?= @$GLOBALS['PREFIX'] . $data['transaction_id'] ?></strong>
                                                <?php } ?>

                                                <?php if (@$data['buyer_email'] != '') { ?>
                                                    <hr>
                                                    <?= Translate::get('Buyer Email') ?>: <strong
                                                            class="text-primary"><?= @$data['buyer_email'] ?></strong>
                                                <?php } ?>
                                                <br>
                                            </div>
                                        </td>
                                        <td class="col-sm-3 text-right">
                                            <div class="small">
                                                <?php if (intval($data['transaction_id']) != 0) : ?>
                                                    <?= Translate::get('Mã GD') ?>: <strong><a target="blank"
                                                                                               href="<?= Yii::$app->urlManager->createUrl(['transaction/detail', 'id' => @$data['transaction_id']]) ?>"><?= @$data['transaction_id'] ?></a></strong>
                                                    <br>
                                                    <hr>
                                                    <div class="text-right">
                                                        <?= Translate::get('Số tiền TT') ?>: <strong
                                                                class="text-danger"><?= ObjInput::makeCurrency(@$data['cashin_amount']) ?> <?= $data['currency'] ?></strong>

                                                        <?php if (@$data['transaction_info']['receiver_fee'] > 0) { ?>
                                                            <hr>

                                                            <?= Translate::get('Phí merchant') ?>:
                                                            <span><?= ObjInput::makeCurrency(@$data['transaction_info']['receiver_fee']) ?> <?= $data['transaction_info']['currency'] ?></span>

                                                        <?php } ?>
                                                        <?php if (@$data['transaction_info']['sender_fee'] > 0) { ?>

                                                            <hr>
                                                            <?= Translate::get('Phí người TT') ?>:
                                                            <span><?= ObjInput::makeCurrency(@$data['transaction_info']['sender_fee']) ?> <?= $data['transaction_info']['currency'] ?></span>

                                                        <?php } ?>
                                                    </div>

                                                    <?php if (@$data['transaction_info']['bank_refer_code'] != '') { ?>
                                                        <hr>
                                                        <?= Translate::get('Mã tham chiếu') ?>: <strong
                                                                class="text-primary"><?= @$data['transaction_info']['bank_refer_code'] ?></strong>
                                                    <?php } ?>
                                                    <hr>
                                                    <strong><?= Translate::get(@$data['transaction_info']['payment_method_info']['name']) ?></strong>
                                                    <br>
                                                    <hr>
                                                    <strong
                                                            class="text-magenta"><?= Translate::get(@$data['transaction_info']['partner_payment_info']['name']) ?></strong>
                                                    <br>

                                                    <hr>
                                                    <strong class="text-magenta">
                                                        <?php if ($data['transaction_info'] ["transaction_type_id"] == 5 && in_array($data['status'], array(3, 10, 4))) { ?>
                                                            <?= Translate::get('Loại thẻ') ?>:
                                                            <?php if (!empty(json_decode($data['installment_info'], true)['transactionInfo'])) { ?>
                                                                <strong class="text-primary"><?= json_decode($data['installment_info'], true)['transactionInfo']['method'] ?> </strong>
                                                                <br/>
                                                            <?php } else { ?>
                                                                <strong class="text-primary"><?= json_decode($data['installment_info'], true)['method'] ?> </strong>
                                                                <br/>
                                                            <?php } ?>
                                                            <?php if (!empty($data['installment_cycle'])) { ?>
                                                                <?= Translate::get('Kỳ hạn trả góp') ?>:
                                                                <strong class="text-primary"><?= $data['installment_cycle'] ?>
                                                                    tháng</strong>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </strong>
                                                    <br>
                                                <?php endif; ?>
                                                <!--                        <span class="text-danger">Chưa thanh toán</span>-->
                                            </div>
                                        </td>
                                        <td class="col-sm-2 text-right">
                                            <div class="small">
                                                <?php
                                                if ($data['status'] == CheckoutOrder::STATUS_WAIT_REFUND || $data['status'] == CheckoutOrder::STATUS_REFUND || $data['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) {
                                                    if (intval($data['refund_transaction_id']) > 0) :
                                                        ?>
                                                        <?= Translate::get('Mã GD đã hoàn') ?>:
                                                        <?php
                                                        foreach ($data['list_refund_transaction'] as $item) {
                                                            ?>
                                                            <strong>
                                                                <a target="blank"
                                                                   href="<?= Yii::$app->urlManager->createUrl(['transaction/detail', 'id' => $item]) ?>"><?= $item ?></a></strong>
                                                            <?php
                                                        }
                                                        ?>
                                                        <div class="text-right">


                                                            <hr>
                                                            <?= Translate::get('Số tiền đã hoàn') ?>: <strong
                                                                    class="text-danger"><?= ObjInput::makeCurrency(@$data['refund_transaction_info']['amount']) ?> <?= $data['refund_transaction_info']['currency'] ?></strong>
                                                            <hr>
                                                            <?= Translate::get('Số tiền đang chờ hoàn') ?>: <strong
                                                                    class="text-danger"><?= ObjInput::makeCurrency(@$data['refund_paying']) ?> <?= $data['refund_transaction_info']['currency'] ?></strong>
                                                            <hr>
                                                            <?= Translate::get('Phí hoàn tiền thu MC') ?>: <strong
                                                                    class="text-danger"><?= ObjInput::makeCurrency(@$data['refund_transaction_info']['sender_fee']) ?> <?= $data['refund_transaction_info']['currency'] ?></strong>
                                                            <?php if (@$data['refund_transaction_info']['partner_payment_receiver_fee'] > 0) { ?>
                                                                <hr>
                                                                <?= Translate::get('Phí hoàn NM chịu') ?>:
                                                                <span><?= ObjInput::makeCurrency(@$data['refund_transaction_info']['partner_payment_receiver_fee']) ?> <?= $data['refund_transaction_info']['currency'] ?></span>

                                                            <?php } ?>
                                                        </div>
                                                        <hr>
                                                        <?= Translate::get('Mã tham chiếu') ?>: <strong
                                                            class="text-primary"><?= @$data['refund_transaction_info']['bank_refer_code'] ?></strong>
                                                        <br>
                                                        <hr>
                                                        <strong><?= @$data['refund_transaction_info']['payment_method_info']['name'] ?></strong>

                                                        <hr>
                                                        <strong
                                                                class="text-magenta"><?= @$data['refund_transaction_info']['partner_payment_info']['name'] ?></strong>
                                                        <br>
                                                    <?php endif; ?>
                                                <?php } ?>
                                            </div>
                                        </td>

                                        <td class="text-right col-sm-2">
                                            <?php if ($data['status'] == CheckoutOrder::STATUS_NEW) { ?>
                                                <span class="label label-default"><?= Translate::get('Chưa thanh toán') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_PAYING) { ?>
                                                <span class="label label-warning"><?= Translate::get('Đang thanh toán') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_PAID) { ?>
                                                <span class="label label-success"><?= Translate::get('Đã thanh toán') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_CANCEL) { ?>
                                                <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REVIEW) { ?>
                                                <span class="label label-warning"><?= Translate::get('Bị review') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WAIT_REFUND) { ?>
                                                <span class="label label-danger"><?= Translate::get('Đang đợi hoàn tiền') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REFUND) { ?>
                                                <span class="label label-magenta"><?= Translate::get('Đã hoàn toàn bộ') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) { ?>
                                                <span class="label label-magenta"><?= Translate::get('Đã hoàn một phần') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WAIT_WIDTHDAW) { ?>
                                                <span class="label label-warning"><?= Translate::get('Đang rút tiền') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WIDTHDAW) { ?>
                                                <span class="label label-success"><?= Translate::get('Đã rút tiền') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_INSTALLMENT_WAIT) { ?>
                                                <span class="label label-success"><?= Translate::get('Đã thanh toán , đợi duyệt trả góp') ?></span>
                                            <?php } elseif ($data['status'] == CheckoutOrder::STATUS_FAILURE) { ?>
                                                <span class="label label-danger"><?= Translate::get('Giao dịch thất bại') ?></span>
                                            <?php } ?>
                                            <hr>
                                            <?php if ($data['callback_status'] == CheckoutOrder::CALLBACK_STATUS_NEW) { ?>
                                                <span class="label label-default"><?= Translate::get('Chưa gọi merchant') ?></span>
                                            <?php } elseif ($data['callback_status'] == CheckoutOrder::CALLBACK_STATUS_PROCESSING) { ?>
                                                <span class="label label-primary"><?= Translate::get('Đang gọi merchant') ?></span>
                                            <?php } elseif ($data['callback_status'] == CheckoutOrder::CALLBACK_STATUS_SUCCESS) { ?>
                                                <span class="label label-success"><?= Translate::get('Gọi lại merchant thành công') ?></span>
                                            <?php } elseif ($data['callback_status'] == CheckoutOrder::CALLBACK_STATUS_ERROR) { ?>
                                                <span class="label label-danger"><?= Translate::get('Lỗi khi gọi lại merchant') ?></span>
                                            <?php } ?>
                                            <hr>
                                            <div class="small text-right">
                                                <?php if (intval($data['time_created']) > 0) { ?>
                                                    <?= Translate::get('Tạo') ?>: <span
                                                            class="text-magenta"><?= date('H:i, d/m/Y', $data['time_created']) ?></span>
                                                    <br>
                                                <?php } ?>
                                                <?php if (intval($data['time_paid']) > 0) { ?>
                                                    <hr>
                                                    <?= Translate::get('Thanh toán') ?>: <span
                                                            class="text-success"><?= date('H:i, d/m/Y', $data['time_paid']) ?></span>
                                                    <br>
                                                <?php } ?>
                                                <?php if (intval($data['time_withdraw']) > 0) { ?>
                                                    <hr>
                                                    <?= Translate::get('Rút') ?>: <span
                                                            class="text-danger"><?= date('H:i, d/m/Y', $data['time_withdraw']) ?></span>
                                                    <br>
                                                <?php } ?>
                                                <?php if (intval($data['time_refund']) > 0) { ?>
                                                    <hr>
                                                    <?= Translate::get('Hoàn') ?>: <span
                                                            class="bold"><?= date('H:i, d/m/Y', $data['time_refund']) ?></span>
                                                    <br>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($data["operators"])) { ?>
                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button"
                                                       aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                                class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <?php
                                                        foreach ($data["operators"] as $items => $operator) {
                                                            $router = isset($operator['router']) ? $operator['router'] : 'checkout-order/' . $items;
                                                            $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                            ?>
                                                            <?php if ($operator['confirm'] == true) { ?>
                                                                <li>
                                                                    <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                       onclick="confirm(
                                                                               '<?= $operator['title'] ?>',
                                                                               '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                               return false;"><?= Translate::get($operator['title']) ?></a>
                                                                </li>
                                                            <?php } else { ?>
                                                                <li>
                                                                    <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                                        <?= Translate::get($operator['title']) ?>
                                                                    </a>
                                                                </li>
                                                                <?php
                                                            }
                                                        }
                                                        ?>

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
                        Bạn có chắc chắn muốn <strong class="title"> </strong> cho đơn thanh toán này không?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#">Xác nhận</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">
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
                    string += '<option value="' + item.id + '">' + item.name + '</option>';
                });
                $('#select-merchant-id').html(string);
            }
        });
    });

</script>