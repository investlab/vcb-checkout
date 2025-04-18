<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\CheckoutOrderCallbackHistory;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Lịch sử gọi lại merchant');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    CheckoutOrderCallbackHistory::STATUS_NEW => 'bg bg-default',
    CheckoutOrderCallbackHistory::STATUS_PROCESSING => 'bg bg-warning',
    CheckoutOrderCallbackHistory::STATUS_SUCCESS => 'bg bg-success',
    CheckoutOrderCallbackHistory::STATUS_ERROR => 'bg bg-danger',
);
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Lịch sử gọi lại merchant') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
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

<div class="well well-sm fillter">
    <form class="form-horizontal" role=form>
        <div class="row group-input-search">
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian gọi từ') ?>"
                       name="time_request_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_request_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_request_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_request_to) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian nhận KQ từ') ?>"
                       name="time_response_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_response_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_response_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_response_to) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã đơn thanh toán') ?>"
                       name="order_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->order_code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã token') ?>"
                       name="token_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->token_code) : '' ?>">
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="merchant_id">
                    <option value="0"><?= Translate::get('Chọn Merchant') ?></option>
                    <?php
                    if (isset($merchant_search_arr) && count($merchant_search_arr) > 0) {
                        foreach ($merchant_search_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $data['id'] ?>" <?= (isset($search) && $search->merchant_id == $data['id']) ? "selected='true'" : '' ?> >
                                 <?= Translate::get($data['name']) ?>
                            </option>
                        <?php
                        }
                    } ?>
                </select>
            </div>


            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="status">
                    <option value="0"><?= Translate::get('Chọn trạng thái') ?></option>
                    <?php
                    foreach ($status_arr as $key => $data) {
                        ?>
                        <option
                            value="<?= $key ?>" <?= (isset($search) && $search->status == $key) ? "selected='true'" : '' ?> >
                            <?= $data ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                <a href="<?= Yii::$app->urlManager->createUrl('checkout-order-callback-history/index') ?>"
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
            <div class="col-md-6" style="margin-left:-15px"><?= Translate::get('Có') ?> <strong
                    class="text-danger"><?php echo $page->pagination->totalCount; ?></strong>
                <?= Translate::get('Lịch sử') ?>
                &nbsp;|&nbsp;
                <?= Translate::get('Chưa gọi') ?> <strong
                    class="text-danger"><?= (isset($page->count_new) ? $page->count_new : '0') ?></strong>
                &nbsp;|&nbsp;
                <?= Translate::get('Đang gọi') ?> <strong
                    class="text-danger"><?= (isset($page->count_processing) ? $page->count_processing : '0') ?></strong>
                &nbsp;|&nbsp;
                <?= Translate::get('Lỗi') ?> <strong
                    class="text-danger"><?= (isset($page->count_error) ? $page->count_error : '0') ?></strong>
                &nbsp;|&nbsp;
                <?= Translate::get('Đã gọi') ?> <strong
                    class="text-danger"><?= (isset($page->count_success) ? $page->count_success : '0') ?></strong>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <?php
                if (is_array($page->data) && count($page->data) == 0 && $page->errors == null) {
                    ?>

                    <div class="alert alert-danger fade in">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
                    </div>
                <?php } ?>
                <?php
                if ($page->errors != null) {
                    ?>
                    <div class="alert alert-danger fade in">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php foreach ($page->errors as $key => $data) { ?>
                            <strong><?= Translate::get('Thông báo') ?>!!</strong> <?= $data ?>.<br>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= Translate::get('Đơn thanh toán') ?></th>
                            <th><?= Translate::get('Merchant') ?></th>
                            <th><?= Translate::get('Lệnh gọi lại') ?></th>
                            <th><?= Translate::get('Giá trị trả về') ?></th>
                            <th><?= Translate::get('Trạng thái') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (is_array($page->data) && count($page->data) > 0 && $page->errors == null) {
                            $callback_status = common\models\db\CheckoutOrderCallback::getStatus();
                            foreach ($page->data as $key => $data) {
                                ?>                                
                                <tr class="<?=$array_color[$data['status']]?>">
                                    <td>
                                        <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                    </td>
                                    <td>
                                        <?= Translate::get('Mã đơn thanh toán') ?> :
                                        <strong><?= isset($data['checkout_order_info']['order_code']) && $data['checkout_order_info']['order_code'] != null ? $data['checkout_order_info']['order_code'] : '' ?></strong>
                                        <br><br>
                                        <?= Translate::get('Mã token') ?> :
                                        <strong><?= isset($data['checkout_order_info']['token_code']) && $data['checkout_order_info']['token_code'] != null ? $data['checkout_order_info']['token_code'] : '' ?></strong>

                                    </td>
                                    <td>
                                        <?= isset($data['checkout_order_info']['merchant_info']['name']) && $data['checkout_order_info']['merchant_info']['name'] != null ? $data['checkout_order_info']['merchant_info']['name'] : '' ?>
                                    </td>
                                    <td>
                                       ID: <?= isset($data['checkout_order_callback_id']) && $data['checkout_order_callback_id'] != null ? $data['checkout_order_callback_id'] : '' ?><br>
                                        <?= Translate::get(@$callback_status[$data['checkout_orders_callback_info']['status']])?>
                                    </td>
                                    <td>
                                        <?= isset($data['response_data']) && $data['response_data'] != null ? $data['response_data'] : "" ?>
                                    </td>

                                    <td>
                                        <?php if ($data['status'] == CheckoutOrderCallbackHistory::STATUS_NEW) { ?>
                                            <span class="label label-default"><?= Translate::get('Chưa gọi') ?></span>
                                        <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_PROCESSING) { ?>
                                            <span class="label label-primary"><?= Translate::get('Đang gọi') ?></span>
                                        <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_SUCCESS) { ?>
                                            <span class="label label-success"><?= Translate::get('Đã gọi') ?></span>
                                        <?php } elseif ($data['status'] == CheckoutOrderCallbackHistory::STATUS_ERROR) { ?>
                                            <span class="label label-danger"><?= Translate::get('Lỗi') ?></span>
                                        <?php } ?>
                                        <br><br>
                                        <hr>
                                        <div class="small">
                                            <?php if (intval($data['time_request']) > 0): ?>
                                                <?= Translate::get('Gọi') ?>: <strong><?= date('H:i, d/m/Y', $data['time_request']) ?></strong>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_response']) > 0): ?>
                                                <?= Translate::get('Nhận KQ trả về') ?>:
                                                <strong><?= date('H:i, d/m/Y', $data['time_response']) ?></strong><br>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                
                            <?php
                            }
                        } ?>
                        </tbody>        
                    </table>
                </div>
                <div class="box-control">
                    <div class="pagination-router">
                        <?= \yii\widgets\LinkPager::widget([
                            'pagination' => $page->pagination,
                            'nextPageLabel' => Translate::get('Tiếp'),
                            'prevPageLabel' => Translate::get('Sau'),
                            'maxButtonCount' => 5
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

