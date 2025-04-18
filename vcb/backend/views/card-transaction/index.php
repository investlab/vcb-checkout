<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\CardTransaction;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Quản lý giao dịch thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Quản lý giao dịch thẻ cào') ?></h1>

            <div class="option-buttons">
                <div class="addNew">
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
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Ngày tạo từ') ?>"
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
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Ngày được rút từ') ?>"
               name="time_withdraw_from"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_withdraw_from) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
               name="time_withdraw_to"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_withdraw_to) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời hạn được rút từ') ?>"
               name="withdraw_time_limit_from"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->withdraw_time_limit_from) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
               name="withdraw_time_limit_to"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->withdraw_time_limit_to) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control" name="bill_type">
            <option value="0"><?= Translate::get('Chọn loại hóa đơn') ?></option>
            <?php
            foreach ($bill_type_arr as $key => $rs) {
                ?>
                <option
                    value="<?= $key ?>" <?= (isset($search) && $search->bill_type == $key) ? "selected='true'" : '' ?> >
        <?= Translate::get($rs) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control" name="cycle_day">
            <option value="0"><?= Translate::get('Chọn kỳ thanh toán') ?></option>
            <?php
            foreach ($cycle_day_arr as $key => $rs) {
                ?>
                <option
                    value="<?= $key ?>" <?= (isset($search) && $search->cycle_day == $key) ? "selected='true'" : '' ?> >
                    <?= Translate::get($rs) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control" name="merchant_id">
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
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã tham chiếu merchant') ?>"
               name="merchant_refer_code"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->merchant_refer_code) : '' ?>">
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control" name="partner_card_id">
            <?php
            foreach ($partner_card_search_arr as $key => $rs) {
                ?>
                <option
                    value="<?= $key ?>" <?= (isset($search) && $search->partner_card_id == $key) ? "selected='true'" : '' ?> >
                    <?= Translate::get($rs) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã tham chiếu đối tác') ?>"
               name="partner_card_refer_code"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->partner_card_refer_code) : '' ?>">
    </div>

    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control" placeholder="<?= Translate::get('ID giao dịch') ?>"
               name="id"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->id) : '' ?>">
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control" name="card_type_id">
            <?php
            foreach ($card_type_search_arr as $key => $rs) {
                ?>
                <option
                    value="<?= $key ?>" <?= (isset($search) && $search->card_type_id == $key) ? "selected='true'" : '' ?> >
                    <?= Translate::get($rs) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã thẻ') ?>"
               name="card_code"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->card_code) : '' ?>">
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control" placeholder="<?= Translate::get('Serial thẻ') ?>"
               name="card_serial"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->card_serial) : '' ?>">
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control selectpicker" name="status[]" multiple=""
                title="<?= Translate::get('Chọn trạng thái') ?>"
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

    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
        <a href="<?= Yii::$app->urlManager->createUrl('card-transaction/index') ?>"
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
    <div class="col-md-10" style="margin-left:-15px"><?= Translate::get('Có') ?> <strong
            class="text-danger"><?php echo $page->pagination->totalCount; ?></strong>
            <?= Translate::get('giao dịch') ?>
    </div>

    <div class="col-md-2" align="right">
        <?php if (!empty($check_all_operators)): ?>
            <?php foreach ($check_all_operators as $key => $operator) :
                $router = isset($operator['router']) ? $operator['router'] : 'card-transaction/' . $key;
                ?>
                <?php if ($key == 'export') { ?>
                <a href="#" onclick="exportData.set('<?= Translate::get('Trích xuất GD') ?>', '<?= Yii::$app->urlManager->createUrl('card-transaction') . \common\components\utils\Utilities::buidLinkExcel('export') ?>');">
<!--                    --><?//= $operator['title'] ?>
                </a>
            <?php } ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <?php
        if (is_array($page->data) && count($page->data) == 0) {
            ?>

            <div class="alert alert-danger fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>sss.
            </div>
        <?php } ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" border="0" cellpadding="0" cellspacing="0"
                   width="100%">
                <thead>
                <tr>
                    <th><?= Translate::get('Thông tin giao dịch') ?></th>
                    <th><?= Translate::get('Thông tin thẻ') ?></th>
                    <th><?= Translate::get('Đối tác gạch thẻ') ?></th>
                    <th><?= Translate::get('Kỳ thanh toán') ?></th>
                    <th><?= Translate::get('Trạng thái') ?></th>
                    <th class="text-center"><?= Translate::get('Thao tác') ?></th>
                </tr>
                </thead>
                <?php
                if (is_array($page->data) && count($page->data) > 0) {
                    foreach ($page->data as $key => $data) {
                        ?>
                        <tbody>
                        <tr>
                            <td>
                                <div class="small">
                                    ID : <strong> <?= @$data['id'] ?></strong>
                                    <hr>
                                    Merchant: <strong class="text-magenta"><?= @$data['merchant_info']["name"] ?></strong>
                                    <hr>
                                    <?= Translate::get('Mã tham chiếu') ?>: <strong><?= @$data['merchant_refer_code'] ?></strong>
                                    <hr>
<?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong><br>
                                    <?php if (intval($data['time_updated']) > 0): ?>
                                    <?= Translate::get('Cập nhật') ?>: <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <strong><?= @$data['card_type_info']["name"] ?></strong>
                                    <hr>
<?= Translate::get('Mã') ?>: <strong class="text-magenta"><?= @$data['card_code'] ?></strong>
                                    <hr>
                                    Serial: <strong><?= @$data['card_serial'] ?></strong>
                                    <hr>
                                    <?= Translate::get('Mệnh giá') ?>: <strong class="text-danger"><?= ObjInput::makeCurrency(@$data['card_price']) ?> <?= @$data['currency']?></strong>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <strong class="text-primary"><?= @$data['partner_card_info']["name"] ?></strong>
                                    <?php if (@$data['partner_card_log_id'] != null) : ?>
                                        <hr><?= Translate::get('Log gạch thẻ') ?>: <strong><?= @$data['partner_card_log_id'] ?></strong>
                                    <?php endif;?>
                                    <?php if (@$data['partner_card_refer_code'] != null) : ?>
                                        <hr><?= Translate::get('Mã tham chiếu') ?>: <strong><?= @$data['partner_card_refer_code'] ?></strong>
                                    <?php endif;?>                                    
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <strong class="text-primary"><?= @$data['bill_type_name'] ?></strong>
                                    <hr>
<?= Translate::get('Kỳ thanh toán') ?> : <strong><?= @$data['cycle_day_name'] ?></strong><br>
                                    <?= Translate::get('Phí') ?>: <strong><?= ObjInput::makeCurrency(@$data['percent_fee']) ?>%</strong>
                                    <hr>
                                    <?php if (intval($data['withdraw_time_limit']) > 0): ?>
<?= Translate::get('Thời hạn rút') ?>: <strong><?= date('H:i, d/m/Y', $data['withdraw_time_limit']) ?></strong><br>
                                    <?php endif; ?>
                                    <?= Translate::get('Số tiền rút') ?>: <strong class="text-danger"><?= ObjInput::makeCurrency(@$data['card_amount']) ?> <?=@$data['currency']?></strong>
                                </div>
                            </td>

                            <td class="col-sm-2">
                                <?php if ($data['status'] == CardTransaction::STATUS_NEW) { ?>
                                    <span class="label label-default"><?= Translate::get('Chưa rút') ?></span>
                                <?php } elseif ($data['status'] == CardTransaction::STATUS_PROCESSING) { ?>
                                    <span class="label label-warning"><?= Translate::get('Đang rút') ?></span>
                                <?php } elseif ($data['status'] == CardTransaction::STATUS_WITHDRAW) { ?>
                                    <span class="label label-success"><?= Translate::get('Đã rút') ?></span>
                                <?php } ?>
                                <?php if ($data['status'] != CardTransaction::STATUS_NEW && intval($data['time_withdraw']) > 0): ?>    
                                <hr>
                                <div class="small">
                                    <?= Translate::get('ID yêu cầu rút') ?>: <strong><?=$data['cashout_id']?></strong><br>
<?= Translate::get('Thời gian rút') ?>: <strong><?= date('H:i, d/m/Y', $data['time_withdraw']) ?></strong>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($data["operators"])) { ?>
                                    <div class="dropdown otherOptions fr">
                                        <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                           data-toggle="dropdown"
                                           role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                class="caret"></span></a>
                                        <ul class="dropdown-menu right" role="menu">
                                            <?php foreach ($data["operators"] as $items => $operator) {
                                                $router = isset($operator['router']) ? $operator['router'] : 'card-transaction/' . $items;
                                                $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                ?>

                                                <li>
                                                    <a
                                                        href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                        <?= Translate::get($operator['title']) ?>
                                                    </a>
                                                </li>
                                            <?php
                                            } ?>

                                        </ul>
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
        <div class="box-control">
            <div class="pagination-router">
                <?= \yii\widgets\LinkPager::widget(['pagination' => $page->pagination,
                    'nextPageLabel' =>  Translate::get('Tiếp'),
                    'prevPageLabel' => Translate::get('Sau'),
                    'maxButtonCount' => 5]); ?>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</div>
</div>
