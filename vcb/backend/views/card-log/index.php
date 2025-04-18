<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\CardLogFull;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Quản lý log thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Quản lý log thẻ cào') ?></h1>

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
        <input type="text" class="form-control left-icon datepicker"
               placeholder="<?= Translate::get('Ngày thẻ bị gạch từ') ?>"
               name="time_card_updated_from"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_card_updated_from) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
        name="time_card_updated_to"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_card_updated_to) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker"
               placeholder="<?= Translate::get('Ngày xử lý tạo GD từ') ?>"
               name="time_create_transaction_from"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_create_transaction_from) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
        name="time_create_transaction_to"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_create_transaction_to) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker"
               placeholder="<?= Translate::get('Thời hạn được rút từ') ?>"
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
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Ngày backup từ') ?>"
        name="time_backup_from"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_backup_from) : '' ?>">
        <i class="im-calendar s16 left-input-icon"></i>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
        name="time_backup_to"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_backup_to) : '' ?>">
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
        <input type="text" class="form-control" placeholder="ID log"
               name="id"
               value="<?= (isset($search) && $search != null) ? Html::encode($search->id) : '' ?>">
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control selectpicker" name="card_status[]" multiple=""
                title="<?= Translate::get('Chọn trạng thái thẻ') ?>"
            <?php
            foreach ($card_status_arr as $key => $ss) {
                ?>
                <option
                    value="<?= $key ?>" <?= !empty($search->card_status) && in_array($key, $search->card_status) ? "selected='true'" : '' ?> >
                    <?= Translate::get($ss) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control selectpicker" name="transaction_status[]" multiple=""
                title="<?= Translate::get('Chọn trạng thái giao dịch') ?>"
            <?php
            foreach ($transaction_status_arr as $key => $ss) {
                ?>
                <option
                    value="<?= $key ?>" <?= !empty($search->transaction_status) && in_array($key, $search->transaction_status) ? "selected='true'" : '' ?> >
                    <?= Translate::get($ss) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
        <select class="form-control selectpicker" name="backup_status[]" multiple=""
                title="<?= Translate::get('Chọn trạng thái backup') ?>"
            <?php
            foreach ($backup_status_arr as $key => $ss) {
                ?>
                <option
                    value="<?= $key ?>" <?= !empty($search->backup_status) && in_array($key, $search->backup_status) ? "selected='true'" : '' ?> >
                    <?= Translate::get($ss) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
        <a href="<?= Yii::$app->urlManager->createUrl('card-log/index') ?>"
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
                log
            </div>

            <div class="col-md-2" align="right">
                <?php if (!empty($check_all_operators)): ?>
                    <?php foreach ($check_all_operators as $key => $operator) :
                        $router = isset($operator['router']) ? $operator['router'] : 'card-log/' . $key;
                        ?>
                        <?php if ($key == 'export') { ?>
                        <a href="#"
                           onclick="exportData.set(<?= Translate::get('Trích xuất log') ?>, '<?= Yii::$app->urlManager->createUrl('card-log') . \common\components\utils\Utilities::buidLinkExcel('export') ?>'); return false;">
<!--                            --><?//= Translate::get($operator['title']) ?>
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
                        <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>
                        .
                    </div>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" border="0" cellpadding="0" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <th><?= Translate::get('Thông tin log') ?></th>
                            <th><?= Translate::get('Thông tin thẻ') ?></th>
                            <th><?= Translate::get('Kênh gạch thẻ') ?></th>
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
                                            Merchant: <strong
                                                class="text-magenta"><?= Translate::get(@$data['merchant_info']["name"]) ?></strong><br>
                                            <hr>
                                            <?= Translate::get('Mã tham chiếu') ?>:
                                            <strong><?= @$data['merchant_refer_code'] ?></strong>
                                            <hr>
                                            Version : <strong><?= @$data['version'] ?></strong><br>
                                            <?= Translate::get('Tạo') ?>:
                                            <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong><br>
                                            <?php if (intval($data['time_updated']) > 0): ?>
                                                <?= Translate::get('Cập nhật') ?>:
                                                <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="col-sm-2">
                                        <div class="small">
                                            <strong><?= Translate::get(@$data['card_type_info']["name"]) ?></strong>
                                            <hr>
                                            <?= Translate::get('Mã') ?>: <strong
                                                class="text-magenta"><?= @$data['card_code'] ?></strong>
                                            <hr>
                                            Serial: <strong><?= @$data['card_serial'] ?></strong>
                                            <?php if ($data['card_transaction_id']): ?>
                                                <hr>
                                                <?= Translate::get('Mệnh giá') ?>: <strong
                                                    class="text-danger"><?= ObjInput::makeCurrency(@$data['card_price']) ?> <?= @$data['currency'] ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="col-sm-2">
                                        <div class="small">
                                            <strong
                                                class="text-primary"><?= Translate::get(@$data['partner_card_info']["name"]) ?></strong>
                                            <?php if (@$data['partner_card_log_id'] != null) : ?>
                                                <hr><?= Translate::get('Log gạch thẻ') ?>:
                                                <strong><?= @$data['partner_card_log_id'] ?></strong>
                                            <?php endif; ?>
                                            <?php if (@$data['partner_card_refer_code'] != null) : ?>
                                                <?= Translate::get('Mã tham chiếu') ?>:
                                                <strong><?= @$data['partner_card_refer_code'] ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="col-sm-2">
                                        <div class="small">
                                            <strong
                                                class="text-primary"><?= Translate::get(@$data['bill_type_name']) ?></strong>
                                            <hr>
                                            <?= Translate::get('Kỳ thanh toán') ?> :
                                            <strong><?= Translate::get(@$data['cycle_day_name']) ?></strong><br>
                                            <?= Translate::get('Phí') ?>:
                                            <strong><?= ObjInput::makeCurrency(@$data['percent_fee']) ?>%</strong>
                                            <?php if ($data['card_transaction_id']): ?>
                                                <hr>
                                                <?= Translate::get('Mã giao dịch') ?>
                                                <strong><?= @$data['card_transaction_id'] ?></strong>
                                                <hr>
                                                <?php if (intval($data['withdraw_time_limit']) > 0): ?>
                                                    <?= Translate::get('Thời hạn rút') ?>:
                                                    <strong><?= date('H:i, d/m/Y', $data['withdraw_time_limit']) ?></strong>
                                                    <br>
                                                <?php endif; ?>
                                                <?= Translate::get('Số tiền rút') ?>: <strong
                                                    class="text-danger"><?= ObjInput::makeCurrency(@$data['card_amount']) ?> <?= @$data['currency'] ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="col-sm-2">
                                        <?php if ($data['card_status'] == CardLogFull::CARD_STATUS_FAIL) { ?>
                                            <span class="label label-danger"><?= Translate::get('Thẻ sai') ?></span>
                                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_TIMEOUT) { ?>
                                            <span
                                                class="label label-warning"><?= Translate::get('Thẻ timeout') ?></span>
                                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_NO_SUCCESS) { ?>
                                            <span
                                                class="label label-default"><?= Translate::get('Thẻ chưa bị gạch') ?></span>
                                        <?php } elseif ($data['card_status'] == CardLogFull::CARD_STATUS_SUCCESS) { ?>
                                            <span
                                                class="label label-success"><?= Translate::get('Thẻ bị gạch thành công') ?></span>
                                        <?php } ?>
                                        <hr>
                                        <?php if ($data['backup_status'] == CardLogFull::BACKUP_STATUS_NEW) { ?>
                                            <span
                                                class="label label-default"><?= Translate::get('Chưa backup') ?></span>
                                        <?php } elseif ($data['backup_status'] == CardLogFull::BACKUP_STATUS_CREATING) { ?>
                                            <span
                                                class="label label-primary"><?= Translate::get('Đang backup') ?></span>
                                        <?php } elseif ($data['backup_status'] == CardLogFull::BACKUP_STATUS_CREATED) { ?>
                                            <span class="label label-success"><?= Translate::get('Đã backup') ?></span>
                                        <?php
                                        }?>
                                        <?php if (intval($data['time_backup']) > 0): ?>
                                            <div class="small"><br><?= Translate::get('Thời gian') ?>:
                                                <strong><?= date('H:i, d/m/Y', $data['time_backup']) ?></strong></div>
                                        <?php endif; ?>
                                        <?php if ($data['card_status'] == CardLogFull::CARD_STATUS_SUCCESS) : ?>
                                            <hr>
                                            <?php if ($data['transaction_status'] == CardLogFull::TRANSACTION_STATUS_NEW) { ?>
                                                <span
                                                    class="label label-default"><?= Translate::get('Chưa tạo giao dịch') ?></span>
                                            <?php } elseif ($data['transaction_status'] == CardLogFull::TRANSACTION_STATUS_CREATING) { ?>
                                                <span
                                                    class="label label-primary"><?= Translate::get('Đang tạo giao dịch') ?></span>
                                            <?php } elseif ($data['transaction_status'] == CardLogFull::TRANSACTION_STATUS_CREATED) { ?>
                                                <span
                                                    class="label label-success"><?= Translate::get('Đã tạo giao dịch') ?></span>
                                            <?php } elseif ($data['transaction_status'] == CardLogFull::TRANSACTION_STATUS_ERROR) { ?>
                                                <span
                                                    class="label label-danger"><?= Translate::get('Lỗi khi tạo giao dịch') ?></span>
                                            <?php } ?>
                                            <?php if (intval($data['time_create_transaction']) > 0): ?>
                                                <div class="small"><br><?= Translate::get('Thời gian') ?>:
                                                    <strong><?= date('H:i, d/m/Y', $data['time_create_transaction']) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </td>
                                    <td>
                                        <?php if (!empty($data["operators"])) { ?>
                                            <div class="dropdown otherOptions fr">
                                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                   data-toggle="dropdown"
                                                   role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?>
                                                    <span
                                                        class="caret"></span></a>
                                                <ul class="dropdown-menu right" role="menu">
                                                    <?php foreach ($data["operators"] as $items => $operator) {
                                                        $router = isset($operator['router']) ? $operator['router'] : 'card-log/' . $items;
                                                        $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                        ?>

                                                        <li>
                                                            <a <?php if ($items != 'export'): ?><?php endif; ?>
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
                            'nextPageLabel' => Translate::get('Tiếp'),
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
