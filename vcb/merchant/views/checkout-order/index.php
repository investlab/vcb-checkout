<?php
/**
 * Created by PhpStorm.
 * User: THU
 * Date: 6/8/2018
 * Time: 14:35
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\components\utils\Utilities;

$this->title = Translate::get('Lịch sử giao dịch');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">

    <h1 class="titlePage"><?= Translate::get('Lịch sử giao dịch') ?></h1>

    <div class="dropSrchbox advance clearfix">
<!--        <form class="form-horizontal" role=form>-->
            <?php $form = ActiveForm::begin(['class'=>'form-horizontal','method'=>'get', 'id'=>'form-search']); ?>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding relt fixCol">
                <input type="text" class="form-control datepicker" placeholder="<?= Translate::get('Từ ngày')?>"
                       name="time_created_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                <i class="date"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding relt fixCol">
                <input type="text" class="form-control datepicker" placeholder="<?= Translate::get('Đến ngày')?>"
                       name="time_created_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                <i class="date"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding fixCol">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã đơn hàng')?>"
                       name="order_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->order_code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding fixCol">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã token')?>"
                       name="token_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->token_code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding fixCol">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Thông tin người mua')?>"
                       name="buyer_info"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->buyer_info) : '' ?>">
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding fixCol">
                <select class="form-control" name="status_merchant" style="width: ">
                    <option value="0"><?= Translate::get('Chọn trạng thái')?></option>
                    <?php
                    foreach ($status_arr as $key => $rs) {
                        ?>
                        <option
                            value="<?= $key ?>" <?= (isset($search) && $search->status_merchant == $key) ? "selected='true'" : '' ?> >
                            <?= Translate::get($rs) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-8 col-lg-6 no-padding fixCol">
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
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 no-padding fixCol group-btn-search mobile-btn-center">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm')?></button>
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('checkout-order/index') ?>"
                   class="btn btn-default display-middle-center">
                    <?= Translate::get('Bỏ lọc')?>
                </a>
            </div>
        <?php ActiveForm::end(); ?>
    </div>

    <!-- begin status -->
    <div class="statistic clearfix" style="display:">
        <span class="pdr15"><?= Translate::get('Tìm thấy')?> <strong class="orrageFont"><?php echo $page->pagination->totalCount; ?></strong> <?= Translate::get('giao dịch')?>;
            <?= Translate::get('Tổng tiền')?>: <strong class="orrageFont"><?php echo ObjInput::makeCurrency($page->total_amount) ?></strong> đ</span><span
            class="sprLine"></span>
        <?php if ($export_permisson == 1){ ?>
        <span class="fr">

               <a href="#" onclick="exportData.set('<?= Translate::get('Trích xuất giao dịch')?>',
                   '<?= Yii::$app->urlManager->createUrl('checkout-order') . Utilities::buidLinkExcel('export') ?>'); return false;">
                   <i class="icon-excel"></i> <?= Translate::get('Xuất excel')?></a>
        </span>
        <?php } ?>
    </div>
    <?php
    if (is_array($page->data) && count($page->data) == 0) {
        ?>

        <div class="alert alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong><?= Translate::get('Thông báo')?></strong> <?= Translate::get('Không tìm thấy')?>.
        </div>
    <?php } ?>
    <!-- begin table -->
    <div class="table-responsive" style="padding-bottom: 50px;">
        <table class="table table-hover" cellspacing="0" cellpadding="0" border="0">
            <thead>
            <tr class="active">
                <th><?= Translate::get('Thông tin đơn thanh toán')?></th>
                <th><?= Translate::get('Người mua')?></th>
                <th width="120">
                    <div align="center"><?= Translate::get('Số tiền')?></div>
                </th>
                <th width="120"><i class="fa fa-clock-o"></i> <?= Translate::get('Thời gian tạo')?></th>
                <th width="120"><i class="fa fa-clock-o"></i> <?= Translate::get('Hoàn thành')?></th>
                <th width="80">
                    <div align="center"><?= Translate::get('Thao tác')?></div>
                </th>
            </tr>
            </thead>
            <?php
            if (is_array($page->data) && count($page->data) > 0) {
                foreach ($page->data as $key => $data) {
                    ?>
                    <tbody>
                    <tr>
                        <td>
                            <?= Translate::get('Mã đơn hàng')?>:
                    <span class="allCap">
                        <?= isset($data['order_code']) && $data['order_code'] != null ? $data['order_code'] : "" ?>
                    </span><br>
                            <div class="small">
                                <?= Translate::get('Mô tả đơn hàng')?>:
                                <?= isset($data['order_description']) && $data['order_description'] != null ? $data['order_description'] : "" ?>
                                <br>
                                <?= Translate::get('Mã token')?>:
                                <strong class="text-primary">
                                    <?= isset($data['token_code']) && $data['token_code'] != null ? $data['token_code'] : "" ?>
                                </strong>
                                <?php if ($data['transaction_info'] ["transaction_type_id"] == 5 && in_array($data['status'],array(3,10,4))) {?>
                                    <?= Translate::get('Loại thẻ') ?>:
                                    <?php if (!empty(json_decode($data['installment_info'],true)['transactionInfo'])) {?>
                                        <strong class="text-primary"><?= json_decode($data['installment_info'],true)['transactionInfo']['method'] ?> </strong>
                                        <br/>
                                    <?php } else { ?>
                                        <strong class="text-primary"><?= json_decode($data['installment_info'],true)['method'] ?> </strong>
                                        <br/>
                                    <?php } ?>
                                    <?php if (!empty($data['installment_cycle'])) {?>
                                        <?= Translate::get('Kỳ hạn trả góp') ?>:
                                        <strong class="text-primary"><?= $data['installment_cycle'] ?> tháng</strong>
                                    <?php } ?>

                                <?php } ?>
                            </div>
                        </td>
                        <td>
                            <?= isset($data['buyer_fullname']) && $data['buyer_fullname'] != null ? $data['buyer_fullname'] : "" ?><br>

                            <div class="small">
                                Email: <?= isset($data['buyer_email']) && $data['buyer_email'] != null ? $data['buyer_email'] : "" ?><br>
                                Mobile: <?= isset($data['buyer_mobile']) && $data['buyer_mobile'] != null ? $data['buyer_mobile'] : "" ?><br>
                            </div>
                        </td>
                        <td align="center">
                            <p class="mrgbm5 bold fontS13"><?= isset($data['amount']) && $data['amount'] != null ? ObjInput::makeCurrency($data['amount']) : "" ?></p>

                                 <?php if ($data['status'] == CheckoutOrder::STATUS_NEW) { ?>
                                     <span class="label label-default"><?= Translate::get('Chưa thanh toán')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_PAYING) { ?>
                                     <span class="label label-warning"><?= Translate::get('Đang thanh toán')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_PAID) { ?>
                                     <span class="label label-success"><?= Translate::get('Đã thanh toán')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_CANCEL) { ?>
                                     <span class="label label-danger"><?= Translate::get('Đã hủy')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REVIEW) { ?>
                                     <span class="label label-danger"><?= Translate::get('Bị review')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WAIT_REFUND) { ?>
                                     <span class="label label-warning"><?= Translate::get('Đang đợi hoàn tiền')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REFUND) { ?>
                                     <span class="label label-success"><?= Translate::get('Đã hoàn toàn bộ')?></span>
                                <?php } elseif ($data['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) { ?>
                                     <span class="label label-success"><?= Translate::get('Đã hoàn một phần')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WAIT_WIDTHDAW) { ?>
                                     <span class="label label-warning"><?= Translate::get('Đang rút tiền')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_WIDTHDAW) { ?>
                                     <span class="label label-success"><?= Translate::get('Đã rút tiền')?></span>
                                 <?php } elseif ($data['status'] == CheckoutOrder::STATUS_INSTALLMENT_WAIT) { ?>
                                     <span class="label label-success"><?= Translate::get('Đã thanh toán, đợi duyệt trả góp')?></span>
                                 <?php } ?>
                        </td>
                        <td><span class="grayFont small"><?= isset($data['time_created']) && intval($data['time_created']) > 0 ? date('H:i,d-m-Y', $data['time_created']) : '' ?></span></td>
                        <td><span class="grayFont small"><?= isset($data['time_paid']) && intval($data['time_paid']) > 0 ? date('H:i,d-m-Y', $data['time_paid']) : '' ?></span></td>
                        <td align="center">
                            <div class="dropdown otherOptions">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false"><?= Translate::get('Thao tác')?><span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a class="" target="_blank" href="<?= Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/detail','id' => $data['id']])?>">
                                            <?= Translate::get('Chi tiết')?>
                                        </a>
                                    </li>
                                    <?php if ($data['status'] == CheckoutOrder::STATUS_PAID || $data['status'] == CheckoutOrder::STATUS_REFUND_PARTIAL) { ?>
                                        <?php if ($refund_permisson == 1) {?>
                                        <li>
                                            <a class="" target="_blank" href="<?= Yii::$app->urlManager->createAbsoluteUrl(['checkout-order/update-status-wait-refund','id' => $data['id']])?>">
                                                <?= Translate::get('Hoàn tiền')?>
                                            </a>
                                        </li>
                                        <?php }?>

                                    <?php }?>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    </tbody>
                <?php }
            } ?>
        </table>
    </div>
</div>
<div class="box-control">
    <div class="pagination-router">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $page->pagination,
            'nextPageLabel' =>  Translate::get('Tiếp'),
            'prevPageLabel' =>  Translate::get('Sau'),
            'maxButtonCount' => 5
        ]); ?>
    </div>
</div>

<script>
    $('#payment_method_id').select2();
</script>