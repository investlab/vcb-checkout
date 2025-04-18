<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PaymentMethod;
use common\components\utils\ObjInput;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Phương thức thanh toán');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    PaymentMethod::STATUS_ACTIVE => 'bg bg-default',
    PaymentMethod::STATUS_LOCK => 'bg bg-danger',
);

?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Phương thức thanh toán') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons no-margin-mobile">
                <div class="addNew no-margin-mobile">
                    <span class="m10"><input type="checkbox" class="noStyle" id="check-all-method"/> Chọn all </span>
                    <a onclick="paymentmethod.modalLockAll()" class="btn btn-sm btn-warning">
                        <?= Translate::get('Khoá phí')?>
                    </a>
                    <a href="<?= Yii::$app->urlManager->createUrl('payment-method/add') ?>" data-toggle="modal" class="btn btn-sm btn-success">
                        <i class="en-plus3"></i> <?= Translate::get('Thêm') ?>
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
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã phương thức') ?>"
                       name="code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Tên phương thức') ?>"
                       name="name"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="transaction_type_id">
                    <?php
                    if (isset($transaction_type_arr) && count($transaction_type_arr) > 0) {
                        foreach ($transaction_type_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $key ?>" <?= (isset($search) && $search->transaction_type_id == $key) ? "selected='true'" : '' ?> >
                                <?= Translate::get($data) ?>
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
                <a href="<?= Yii::$app->urlManager->createUrl('payment-method/index') ?>"
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
                <?= Translate::get('Phương thức') ?>
                &nbsp;|&nbsp;
                <?= Translate::get('Đang sử dụng') ?> <strong
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
                            <strong><?= Translate::get('Thông báo') ?>!!</strong> <?= Translate::get($data) ?>.<br>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th width="35">ID</th>
                            <th><?= Translate::get('Phương thức') ?></th>
                            <th class="text-center"><?= Translate::get('Số tiền tối thiểu') ?></th>
                            <th><?= Translate::get('Cấu hình') ?></th>
                            <th><?= Translate::get('Mô tả') ?></th>
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
                                    <td class="text-center">
                                        <input type="checkbox" class="payment-method-checkbox noStyle" name="payment_method" value="<?= $data['id']?>">
                                        <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                    </td>
                                    <td class="col-md-3">
                                        <?= Translate::get('Mã') ?> :
                                        <strong><?= isset($data['code']) && $data['code'] != null ? $data['code'] : '' ?></strong>
                                        <br><?= Translate::get('Tên') ?> :
                                        <strong><?= isset($data['name']) && $data['name'] != null ? Translate::get($data['name']) : '' ?></strong>
                                        <hr><br> <?= Translate::get('Nhóm') ?> :
                                        <strong><?= isset($data['transaction_type_name']) && $data['transaction_type_name'] != null ? $data['transaction_type_name'] : '' ?></strong>

                                    </td>
                                    <td class="col-md-2 text-center">
                                        <?=  ObjInput::makeCurrency(@$data['min_amount'])?>
                                    </td>
                                    <td class="col-md-2">
                                        <div class="small">
                                            <?= isset($data['config']) && $data['config'] != null ? $data['config'] : '' ?>
                                        </div>
                                    </td>
                                    <td class="col-md-2">
                                        <div class="small">
                                            <?= isset($data['description']) && $data['description'] != null ? $data['description'] : '' ?>
                                        </div>
                                    </td>
                                    <td class="col-md-2">
                                        <?php if ($data['status'] == PaymentMethod::STATUS_ACTIVE) { ?>
                                            <span class="label label-success"><?= Translate::get('Đang sử dụng') ?></span>
                                        <?php } elseif ($data['status'] == PaymentMethod::STATUS_LOCK) { ?>
                                            <span class="label label-danger"><?= Translate::get('Bị khóa') ?></span>
                                        <?php } ?>
                                        <br><br><hr>
                                        <div class="small">
                                            <?php if (intval($data['time_created']) > 0):?>
                                                <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong><br>
                                            <?php endif;?>
                                            <?php if (intval($data['time_updated']) > 0):?>
                                                <?= Translate::get('Cập nhật') ?>: <strong><?=  date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
                                            <?php endif;?>
                                        </div>
                                    </td>
                                    <td class="col-md-2">
                                        <div class="dropdown otherOptions fr">
                                            <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                               data-toggle="dropdown"
                                               role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                    class="caret"></span></a>
                                            <ul class="dropdown-menu right" role="menu">
                                                <li>
                                                    <a title=<?= Translate::get('Kênh thanh toán hỗ trợ') ?>
                                                       href="<?= Yii::$app->urlManager->createUrl(['partner-payment-method/list-by-payment-method', 'id' => $data['id']]) ?>">
                                                        <?= Translate::get('DS kênh thanh toán hỗ trợ') ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a title=<?= Translate::get('Sửa') ?>
                                                       href="<?= Yii::$app->urlManager->createUrl(['payment-method/view-edit', 'id' => $data['id']]) ?>">
                                                        <?= Translate::get('Sửa') ?>
                                                    </a>
                                                </li>
                                                <?php if ($data['status'] == PaymentMethod::STATUS_ACTIVE) { ?>
                                                    <li>
                                                        <a onclick="paymentmethod.modalLock(
                                                            '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                           style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                    </li>

                                                <?php } elseif ($data['status'] == PaymentMethod::STATUS_LOCK) { ?>
                                                    <li>
                                                        <a onclick="paymentmethod.modalUnLock(
                                                            '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                           style="cursor: pointer "><?= Translate::get('Mở Khóa') ?></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
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

<!--Khóa -->
<div class="modal fade" id="Lock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa phương thức') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-paymentmethod-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa phương thức này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:paymentmethod.submitLock();"><?= Translate::get('Xác
                            nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ
                            qua') ?>
                        </button>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--Mở khóa -->
<div class="modal fade" id="Unlock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Mở khóa phương thức') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="unlock-paymentmethod-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method/unlock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Mở khóa phương thức này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:paymentmethod.submitUnLock();"><?= Translate::get('Xác
                            nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ
                            qua') ?>
                        </button>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--Khóa all selected -->
<div class="modal fade" id="LockAll" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa phương thức') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-all-paymentmethod-form" method="get" action="<?= Yii::$app->urlManager->createUrl('payment-method/lock-all') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa tất cả phương thức đã chọn không') ?>?
                            <input name="arr_method_id" id="arr-method-id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:paymentmethod.submitLockAll();"><?= Translate::get('Xác
                    nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ
                    qua') ?>
                        </button>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

