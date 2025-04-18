<?php
use common\models\db\TransactionType;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\MerchantFee;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Cấu hình phí thanh toán mặc định');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    MerchantFee::STATUS_NEW => 'bg bg-default',
    MerchantFee::STATUS_REQUEST => 'bg bg-warning',
    MerchantFee::STATUS_REJECT => 'bg bg-danger',
    MerchantFee::STATUS_ACTIVE => 'bg bg-success',
    MerchantFee::STATUS_LOCK => 'bg bg-danger',
);
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Cấu hình phí thanh toán mặc định') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons no-margin-mobile">
                <div class="addNew no-margin-mobile">
                    <?php if (!empty($check_all_operators)) { ?>
                        <?php foreach ($check_all_operators as $key => $operator) {
                            $router = isset($operator['router']) ? $operator['router'] : 'merchant-fee-default/' . $key;
                            ?>
                            <?php if ($key == 'add') { ?>
                                <a href="<?= Yii::$app->urlManager->createUrl('merchant-fee-default/add') ?>"
                                   class="btn btn-sm btn-success">
                                    <i class="en-plus3"></i><?= Translate::get($operator['title']) ?>
                                </a>
                            <?php
                            }
                        }
                    } ?>
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
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian bắt đầu từ') ?>"
                       name="time_begin_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_begin_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_begin_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_begin_to) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian kết thúc từ') ?>"
                       name="time_end_from"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_end_from) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                       name="time_end_to"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->time_end_to) : '' ?>">
                <i class="im-calendar s16 left-input-icon"></i>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="method_id">
                    <option value="0"><?= Translate::get('Nhóm phương thức TT') ?></option>
                    <?php
                    if (isset($method_search_arr) && count($method_search_arr) > 0) {
                        foreach ($method_search_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $data['id'] ?>" <?= (isset($search) && $search->method_id == $data['id']) ? "selected='true'" : '' ?> >
                                <?= Translate::get($data['name']) ?>
                            </option>
                        <?php
                        }
                    } ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="payment_method_id" id="payment_method_id">
                    <option value="0"><?= Translate::get('Chọn phương thức TT') ?></option>
                    <?php
                    if (isset($payment_method_search_arr) && count($payment_method_search_arr) > 0) {
                        foreach ($payment_method_search_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $data['id'] ?>" <?= (isset($search) && $search->payment_method_id == $data['id']) ? "selected='true'" : '' ?> >
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
                            <?= Translate::get($data) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                <a href="<?= Yii::$app->urlManager->createUrl('merchant-fee-default/index') ?>"
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
                <?= Translate::get('Mức phí') ?>
                &nbsp;|&nbsp;
                <?= Translate::get('Kích hoạt') ?> <strong
                    class="text-danger"><?= (isset($page->count_active) ? $page->count_active : '0') ?></strong>
                &nbsp;|&nbsp;
                <?= Translate::get('Bị khóa') ?> <strong
                    class="text-danger"><?= (isset($page->count_lock) ? $page->count_lock : '0') ?></strong>
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
                            <strong><?= Translate::get('Thông báo') ?>!</strong> <?= Translate::get($data) ?>.<br>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th width="35">ID</th>
                            <th><?= Translate::get('Phương thức thanh toán') ?></th>
                            <th><?= Translate::get('Số tiền tối thiểu') ?> (VND)</th>
                            <th><?= Translate::get('Phí người chuyển') ?></th>
                            <th><?= Translate::get('Phí người nhận') ?></th>
                            <th><?= Translate::get('Trạng thái') ?></th>
                            <th>
                                <div align="right"><?= Translate::get('Thao tác') ?></div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (is_array($page->data) && count($page->data) > 0 && $page->errors == null) {
                            foreach ($page->data as $key => $data) {
                                ?>
                                <tr class="<?=@$array_color[$data['status']]?>">
                                    <td>
                                        <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                    </td>
                                    <td class="col-sm-3">
                                        <div class="small">
                                            <?= Translate::get('Nhóm') ?> :
                                            <strong><?= isset($data['method_info']['name']) && $data['method_info']['name'] != null ? $data['method_info']['name'] : '' ?></strong>
                                            <?php if (isset($data['payment_method_info']['name']) && $data['payment_method_info']['name'] != null) { ?>
                                                <hr>
                                                <?= Translate::get('Phương thức') ?> :
                                                <strong><?=  Translate::get($data['payment_method_info']['name'] )?></strong>
                                            <?php } ?>
                                        </div>

                                    </td>

                                    <td class="col-sm-2">
                                        <strong><?= isset($data['min_amount']) && $data['min_amount'] != null ? ObjInput::makeCurrency($data['min_amount']) : '' ?> </strong>
                                    </td>
                                    <td class="col-sm-2">
                                        <?= Translate::get('Cố đinh') ?> :
                                        <strong><?= isset($data['sender_flat_fee']) && $data['sender_flat_fee'] != null ? ObjInput::makeCurrency($data['sender_flat_fee']) : '' ?></strong>
                                        &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND'] ?>
                                        <br><br>
                                        <?= Translate::get('Phần trăm') ?> :
                                        <strong> <?= isset($data['sender_percent_fee']) && $data['sender_percent_fee'] != null ? $data['sender_percent_fee'] : '' ?></strong>
                                        &nbsp;%
                                    </td>
                                    <td class="col-sm-2">
                                        <?= Translate::get('Cố đinh') ?> :
                                        <strong><?= isset($data['receiver_flat_fee']) && $data['receiver_flat_fee'] != null ? ObjInput::makeCurrency($data['receiver_flat_fee']) : '' ?></strong>
                                        &nbsp;&nbsp;<?= $GLOBALS['CURRENCY']['VND'] ?>
                                        <br><br>
                                        <?= Translate::get('Phần trăm') ?> :
                                        <strong> <?= isset($data['receiver_percent_fee']) && $data['receiver_percent_fee'] != null ? $data['receiver_percent_fee'] : '' ?></strong>
                                        &nbsp;%
                                    </td>

                                    <td class="col-sm-2">
                                        <?php if ($data['status'] == MerchantFee::STATUS_ACTIVE) { ?>
                                            <span class="label label-success"><?= Translate::get('Kích hoạt') ?></span>
                                        <?php } elseif ($data['status'] == MerchantFee::STATUS_LOCK) { ?>
                                            <span class="label label-danger"><?= Translate::get('Đang khóa') ?></span>
                                        <?php } elseif ($data['status'] == MerchantFee::STATUS_NEW) { ?>
                                            <span class="label label-default"><?= Translate::get('Mới tạo') ?></span>
                                        <?php } elseif ($data['status'] == MerchantFee::STATUS_REQUEST) { ?>
                                            <span class="label label-warning"><?= Translate::get('Đang đợi duyệt') ?></span>
                                        <?php } elseif ($data['status'] == MerchantFee::STATUS_REJECT) { ?>
                                            <span class="label label-danger"><?= Translate::get('Từ chối') ?></span>
                                        <?php } ?>

                                        <br><br>
                                        <hr>
                                        <div class="small">
                                            <?php if (intval($data['time_created']) > 0): ?>
                                                <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_begin']) > 0): ?>
                                                <span
                                                    class="text-danger"><?= Translate::get('Bắt đầu') ?>: <strong><?= date('H:i, d/m/Y', $data['time_begin']) ?></strong></span>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_end']) > 0): ?>
                                                <?= Translate::get('Kết thúc') ?>: <strong><?= date('H:i, d/m/Y', $data['time_end']) ?></strong>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_active']) > 0): ?>
                                                <?= Translate::get('Kích hoạt') ?>:
                                                <strong><?= date('H:i, d/m/Y', $data['time_active']) ?></strong>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_lock']) > 0): ?>
                                                <?= Translate::get('Khóa') ?>: <strong><?= date('H:i, d/m/Y', $data['time_lock']) ?></strong>
                                                <br>
                                            <?php endif; ?>
                                            <?php if (intval($data['time_updated']) > 0): ?>
                                                <?= Translate::get('Cập nhật') ?>:
                                                <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="col-sm-1">
                                        <?php if (!empty($data["operators"])) {
                                            ?>
                                            <div class="dropdown otherOptions fr">
                                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                   data-toggle="dropdown"
                                                   role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                        class="caret"></span></a>
                                                <ul class="dropdown-menu right" role="menu">
                                                    <?php foreach ($data["operators"] as $key => $operator) {
                                                        $router = isset($operator['router']) ? $operator['router'] : 'merchant-fee-default/' . $key;
                                                        $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                        ?>
                                                        <?php if ($operator['confirm'] == true) { ?>
                                                            <li>
                                                                <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                   onclick="confirm('<?= $operator['title'] ?>', '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                       return false;"><?= Translate::get($operator['title']) ?></a>
                                                            </li>
                                                        <?php
                                                        }
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

