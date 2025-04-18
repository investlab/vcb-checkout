<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\Cashout;
use common\models\db\Method;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Quản lý yêu cầu rút tiền');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    Cashout::STATUS_NEW => 'bg bg-default',
    Cashout::STATUS_WAIT_VERIFY => 'bg bg-warning',
    Cashout::STATUS_VERIFY => 'bg bg-default',
    Cashout::STATUS_WAIT_ACCEPT => 'bg bg-warning',
    Cashout::STATUS_ACCEPT => 'bg bg-warning',
    Cashout::STATUS_REJECT => 'bg bg-danger',
    Cashout::STATUS_CANCEL => 'bg bg-danger',
    Cashout::STATUS_PAID => 'bg bg-success',
);
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Quản lý yêu cầu rút tiền') ?></h1>

            <div class="option-buttons">
                <div class="addNew">
                    <?php if (!empty($check_all_operators)) : ?>
                        <div class="dropdown otherOptions fr">
                            <a href="#" class="dropdown-toggle btn btn-primary btn-sm" data-toggle="dropdown"
                               role="button" aria-expanded="false"><?= Translate::get('Thêm') ?> <span class="caret"></span></a>
                            <ul class="dropdown-menu right" role="menu">
                                <?php foreach ($check_all_operators as $key => $operator) :
                                    $router = isset($operator['router']) ? $operator['router'] : 'cashout/' . $key;
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
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian YC rút từ') ?>"
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
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian duyệt từ') ?>"
                       name="time_accept_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_accept_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_accept_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_accept_to) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('TG chuyển ngân từ') ?>"
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
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('TG từ chối YC rút từ') ?>"
                       name="time_reject_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_reject_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_reject_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_reject_to) : '' ?>">
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

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã yêu cầu') ?>"
                       name="id"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->id) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <select class="form-control" name="type">
                    <option value="0"><?= Translate::get('Chọn loại') ?></option>
                    <?php
                    foreach ($type_arr as $key => $rs) {
                        ?>
                        <option
                            value="<?= $key ?>" <?= (isset($search) && $search->type == $key) ? "selected='true'" : '' ?> >
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
                <select class="form-control" name="payment_method_id" id="payment_method_id">
                    <?php
                    foreach ($payment_method_search_arr as $key => $rs) {
                        ?>
                        <option
                            value="<?= $key ?>" <?= (isset($search) && $search->payment_method_id == $key) ? "selected='true'" : '' ?> >
                            <?= Translate::get($rs) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <select class="form-control" name="partner_payment_id">
                    <?php
                    foreach ($partner_payment_search_arr as $key => $rs) {
                        ?>
                        <option
                            value="<?= $key ?>" <?= (isset($search) && $search->partner_payment_id == $key) ? "selected='true'" : '' ?> >
                            <?= Translate::get($rs) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã giao dịch') ?>"
                       name="transaction_id"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->transaction_id) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Số thẻ/Tài khoản/Email ví điện tử') ?>"
                       name="bank_account_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->bank_account_code) : '' ?>">
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control selectpicker" name="status[]" multiple=""
                        title="<?= Translate::get('Chọn trạng thái') ?>">
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
             <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('reference code merchant') ?>"
                       name="reference_code_merchant"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->reference_code_merchant) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                <a href="<?= Yii::$app->urlManager->createUrl('cashout/index') ?>"
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
    <div class="col-md-12" style="margin-left:-15px"><?= Translate::get('Tổng') ?> <strong
            class="text-danger"><?php echo $page->pagination->totalCount; ?></strong>
        <?= Translate::get('yêu cầu') ?>
        &nbsp;|&nbsp;
        <?= Translate::get('Mới tạo') ?> <strong
            class="text-danger"><?= (isset($page->count_verify) ? $page->count_verify : '0') ?></strong>
        &nbsp;|&nbsp;
        <?= Translate::get('Đã gửi,đợi duyệt') ?> <strong
            class="text-danger"><?= (isset($page->count_wait_accept) ? $page->count_wait_accept : '0') ?></strong>
        &nbsp;|&nbsp;
        <?= Translate::get('Từ chối') ?> <strong
            class="text-danger"><?= (isset($page->count_reject) ? $page->count_reject : '0') ?></strong>
        &nbsp;|&nbsp;
        <?= Translate::get('Đã duyệt') ?> <strong
            class="text-danger"><?= (isset($page->count_accept) ? $page->count_accept : '0') ?></strong>
        &nbsp;|&nbsp;
        <?= Translate::get('Đã chuyển ngân') ?> <strong
            class="text-danger"><?= (isset($page->count_paid) ? $page->count_paid : '0') ?></strong>
        &nbsp;|&nbsp;
        <?= Translate::get('Đã hủy') ?> <strong
            class="text-danger"><?= (isset($page->count_cancel) ? $page->count_cancel : '0') ?></strong>
    </div>
    <br><br>

    <div class="col-md-12" style="margin-left:-15px"><?= Translate::get('Tổng') ?> <strong
            class="text-danger"><?php echo ObjInput::makeCurrency(@$page->total_amount) ?></strong>
        VND  &nbsp;|&nbsp;
        <?= Translate::get('Mới tạo') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_verify) ?ObjInput::makeCurrency( $page->total_amount_verify) : '0') ?></strong> VND
        &nbsp;|&nbsp;
        <?= Translate::get('Đã gửi,đợi duyệt') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_wait_accept) ? ObjInput::makeCurrency($page->total_amount_wait_accept) : '0') ?></strong> VND
        &nbsp;|&nbsp;
        <?= Translate::get('Từ chối') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_reject) ? ObjInput::makeCurrency($page->total_amount_reject) : '0') ?></strong> VND
        &nbsp;|&nbsp;
        <?= Translate::get('Đã duyệt') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_accept) ? ObjInput::makeCurrency($page->total_amount_accept) : '0') ?></strong> VND
        &nbsp;|&nbsp;
        <?= Translate::get('Đã chuyển ngân') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_paid) ? ObjInput::makeCurrency($page->total_amount_paid) : '0') ?></strong> VND
        &nbsp;|&nbsp;
        <?= Translate::get('Đã hủy') ?> <strong
            class="text-danger"><?= (isset($page->total_amount_cancel) ? ObjInput::makeCurrency($page->total_amount_cancel) : '0') ?></strong> VND

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
            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= Translate::get('Cashout') ?></th>
                    <th><?= Translate::get('Số tiền') ?></th>
                    <th><?= Translate::get('Phương thức rút tiền') ?></th>
                    <th><?= Translate::get('Thông tin tài khoản rút') ?></th>
                    <th><?= Translate::get('Trạng thái') ?></th>
                    <th><?= Translate::get('Thao tác') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (is_array($page->data) && count($page->data) > 0) {
                    foreach ($page->data as $key => $data) {
                        ?>                        
                        <tr class="<?=$array_color[$data['status']]?>">
                            <td>
                                <?= $data['id'] ?>
                            </td>
                            <td class="col-sm-2">
                                Merchant: <strong class="text-primary"><?= $data['merchant_info']["name"] ?></strong>
                                <hr>
                                <div class="small">
                                    <?= Translate::get('Loại') ?>: <strong class="text-magenta"><?= Translate::get(@$data['type_name']) ?></strong><br>
                                   
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <?= Translate::get('Số tiền rút') ?>: <strong
                                        class="text-danger"><?= isset($data['amount']) && $data['amount'] != null ? ObjInput::makeCurrency($data['amount']) : "" ?> <?= $data['currency'] ?></strong><br>
                                    <hr>
                                    <?= Translate::get('Phí rút') ?>:
                                    <strong><?= isset($data['receiver_fee']) && $data['receiver_fee'] != null ? ObjInput::makeCurrency($data['receiver_fee']) : "" ?> <?= $data['currency'] ?></strong><br>
                                </div>
                            </td>
                            <td class="col-sm-2">
                                <div class="small">
                                    <strong><?= Translate::get(@$data['payment_method_info']['name']) ?></strong><br>
                                    <hr>
                                    <?= Translate::get('Kênh rút tiền') ?>: <strong><?= Translate::get(@$data['partner_payment_info']['name']) ?></strong><br>
                                    <?= Translate::get('Mã tham chiếu') ?>: <strong><?= @$data['transaction_info']['bank_refer_code'] ?></strong><br><?= Translate::get('Reference Merchant') ?>: <strong><?= @$data['reference_code_merchant'] ?></strong><br>

                                </div>
                            </td>

                            <td class="col-sm-2">
                                <div class="small">
                                    <?php if (Method::isWithdrawIBOffline(@$data['method_info']['code'])) : ?>
                                        <?= Translate::get('Số tài khoản') ?>: <strong
                                            class="text-magenta"><?= @$data['bank_account_code'] ?></strong><br>
                                        <?= Translate::get('Tên chủ tài khoản') ?>: <strong><?= @$data['bank_account_name'] ?></strong><br>
                                        <?= Translate::get('Chi nhánh') ?>: <strong><?= @$data['bank_account_branch'] ?></strong><br>
                                    <?php elseif (Method::isWithdrawATMCard(@$data['method_info']['code'])): ?>
                                        <?= Translate::get('Số thẻ') ?>: <strong class="text-magenta"><?= @$data['bank_account_code'] ?></strong>
                                        <br>
                                        <?= Translate::get('Tên chủ thẻ') ?>: <strong><?= @$data['bank_account_name'] ?></strong><br>
                                        <?= Translate::get('Ngày phát hành') ?>: <strong><?= @$data['bank_card_month'] ?>
                                            /<?= @$data['bank_card_year'] ?></strong><br>
                                    <?php
                                    elseif (Method::isWithdrawWallet(@$data['method_info']['code'])): ?>
                                        <?= Translate::get('Email tài khoản') ?>: <strong
                                            class="text-magenta"><?= @$data['bank_account_code'] ?></strong><br>
                                        <?= Translate::get('Tên chủ tài khoản') ?>: <strong><?= @$data['bank_account_name'] ?></strong><br>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="col-sm-2">

                                <?php if ($data['status'] == Cashout::STATUS_VERIFY) { ?>
                                    <span class="label label-primary"><?= Translate::get('Mới tạo') ?></span>
                                <?php } elseif ($data['status'] == Cashout::STATUS_WAIT_ACCEPT) { ?>
                                    <span class="label label-warning"><?= Translate::get('Đã gửi, đợi duyệt') ?></span>
                                <?php } elseif ($data['status'] == Cashout::STATUS_REJECT) { ?>
                                    <span class="label label-danger"><?= Translate::get('Từ chối') ?></span>
                                <?php } elseif ($data['status'] == Cashout::STATUS_ACCEPT) { ?>
                                    <span class="label label-success"><?= Translate::get('Đã duyệt') ?></span>
                                <?php } elseif ($data['status'] == Cashout::STATUS_PAID) { ?>
                                    <span class="label label-success"><?= Translate::get('Đã chuyển ngân') ?></span>
                                <?php } elseif ($data['status'] == Cashout::STATUS_CANCEL) { ?>
                                    <span class="label label-danger"><?= Translate::get('Đã hủy') ?></span>
                                <?php } ?>
                                <br><br>
                                <hr>
                                <div class="small">
                                    <?php if (intval($data['time_created']) > 0): ?>
                                        <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_request']) > 0): ?>
                                        <?= Translate::get('Yêu cầu') ?>: <strong><?= date('H:i, d/m/Y', $data['time_request']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_accept']) > 0): ?>
                                        <?= Translate::get('Duyệt') ?>: <strong><?= date('H:i, d/m/Y', $data['time_accept']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_reject']) > 0): ?>
                                        <?= Translate::get('Từ chối') ?>: <strong><?= date('H:i, d/m/Y', $data['time_reject']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_paid']) > 0): ?>
                                        <?= Translate::get('Chuyển ngân') ?>: <strong><?= date('H:i, d/m/Y', $data['time_paid']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_cancel']) > 0): ?>
                                        <?= Translate::get('Hủy') ?>: <strong><?= date('H:i, d/m/Y', $data['time_cancel']) ?></strong><br>
                                    <?php endif; ?>
                                    <?php if (intval($data['time_updated']) > 0): ?>
                                        <?= Translate::get('Cập nhật') ?>: <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
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
                                            <?php foreach ($data["operators"] as $items => $operator) {
                                                $router = isset($operator['router']) ? $operator['router'] : 'cashout/' . $items;
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
                                                <?php
                                                } else {
                                                    ?>
                                                    <li>
                                                        <a <?php if ($items != 'detail' && $items != 'update-status-paid'): ?> class="ajax-link"<?php endif; ?>
                                                            href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                            <?= Translate::get($operator['title']) ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                            } ?>

                                        </ul>
                                    </div>
                                <?php } ?>
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
                        <?= Translate::get('Bạn có chắc chắn muốn') ?> <strong class="title"> </strong> <?= Translate::get('này không') ?>?
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

</script>