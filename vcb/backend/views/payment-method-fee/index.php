<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PaymentMethodFee;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Phí thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Phí thanh toán') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew"><a href="#Add" data-toggle="modal" class="btn btn-sm btn-success"><i
                            class="en-plus3"></i> <?= Translate::get('Thêm') ?></a></div>
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
        <div class="row">
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
                <select class="form-control" name="payment_method_id">
                    <option value="0"><?= Translate::get('Phương thức thanh toán') ?></option>
                    <?php
                    foreach ($payment_method_arr as $key => $data) {
                        ?>
                        <option
                            value="<?= $data['id'] ?>" <?= (isset($search) && $search->payment_method_id == $data['id']) ? "selected='true'" : '' ?> >
                            <?= Translate::get($data['name']) ?>
                        </option>
                    <?php } ?>
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
                <a href="<?= Yii::$app->urlManager->createUrl('payment-method-fee/index') ?>"
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
                <?= Translate::get('Phí thanh toán') ?>
                &nbsp;|&nbsp;
                <?= Translate::get('Từ chối') ?> <strong
                    class="text-danger"><?= (isset($page->count_notreject) ? $page->count_notreject : '0') ?></strong>
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
                            <strong><?= Translate::get('Thông báo') ?>!!</strong> <?= Translate::get($data) ?>.<br>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th width="35">ID</th>
                            <th><?= Translate::get('Phương thức thanh toán') ?></th>
                            <th><?= Translate::get('Phí phần trăm') ?></th>
                            <th><?= Translate::get('Phí cố định') ?></th>
                            <th><?= Translate::get('Thời gian') ?></th>
                            <th><?= Translate::get('Trạng thái') ?></th>
                            <th>
                                <div align="right"><?= Translate::get('Thao tác') ?></div>
                            </th>
                        </tr>
                        </thead>
                        <?php
                        if (is_array($page->data) && count($page->data) > 0 && $page->errors == null) {
                            foreach ($page->data as $key => $data) {
                                ?>
                                <tbody>
                                <tr>
                                    <td>
                                        <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                    </td>
                                    <td>
                                        <?= Translate::get('Mã') ?> :
                                        <strong><?= isset($data['payment_method_code']) && $data['payment_method_code'] != null ? $data['payment_method_code'] : '' ?></strong>
                                        <br><?= Translate::get('Tên') ?> :
                                        <strong><?= isset($data['payment_method_name']) && $data['payment_method_name'] != null ?  Translate::get($data['payment_method_name']) : '' ?></strong>
                                    </td>
                                    <td>
                                        <?= isset($data['percentage_fee']) && $data['percentage_fee'] != null ? $data['percentage_fee'] : '0' ?>
                                    </td>
                                    <td>
                                        <?= isset($data['flat_fee']) && $data['flat_fee'] != null ? ObjInput::makeCurrency($data['flat_fee']) : '0' ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= Translate::get('Tạo') ?>
                                            : <?= isset($data['time_created']) && $data['time_created'] != null ? date('H:i,d-m-Y', $data['time_created']) : '' ?>
                                            <br>
                                            <?= Translate::get('Bắt đầu') ?>
                                            : <?= isset($data['time_begin']) && $data['time_begin'] != null ? date('H:i,d-m-Y', $data['time_begin']) : '' ?>
                                            <br>
                                            <?= Translate::get('Kết thúc') ?>
                                            : <?= isset($data['time_end']) && $data['time_end'] != null ? date('H:i,d-m-Y', $data['time_end']) : '' ?>
                                            <br>
                                            <?= Translate::get('Gửi yêu cầu') ?>
                                            : <?= isset($data['time_request']) && $data['time_request'] != null ? date('H:i,d-m-Y', $data['time_request']) : '' ?>
                                            <br>
                                            <?= Translate::get('Kích hoạt') ?>
                                            : <?= isset($data['time_active']) && $data['time_active'] != null ? date('H:i,d-m-Y', $data['time_active']) : '' ?>
                                            <br>
                                            <?= Translate::get('Từ chối') ?>
                                            : <?= isset($data['user_reject']) && $data['user_reject'] != null ? date('H:i,d-m-Y', $data['user_reject']) : '' ?>
                                            <br>
                                            <?= Translate::get('Khóa') ?>
                                            : <?= isset($data['time_lock']) && $data['time_lock'] != null ? date('H:i,d-m-Y', $data['time_lock']) : '' ?>
                                            <br>
                                            <?= Translate::get('Cập nhật') ?>
                                            : <?= isset($data['time_updated']) && $data['time_updated'] != null ? date('H:i,d-m-Y', $data['time_updated']) : '' ?>
                                            <br>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($data['status'] == PaymentMethodFee::STATUS_NOT_REQUEST) { ?>
                                            <span class="label label-default"><?= Translate::get('Chưa gửi duyệt') ?></span>
                                        <?php } elseif ($data['status'] == PaymentMethodFee::STATUS_REQUEST) { ?>
                                            <span class="label label-warning"><?= Translate::get('Gửi duyệt') ?></span>
                                        <?php } elseif ($data['status'] == PaymentMethodFee::STATUS_REJECT) { ?>
                                            <span class="label label-danger"><?= Translate::get('Từ chối') ?></span>
                                        <?php } elseif ($data['status'] == PaymentMethodFee::STATUS_ACTIVE) { ?>
                                            <span class="label label-success"><?= Translate::get('Kích hoạt') ?></span>
                                        <?php } elseif ($data['status'] == PaymentMethodFee::STATUS_LOCK) { ?>
                                            <span class="label label-danger"><?= Translate::get('Khóa') ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($data['status'] != PaymentMethodFee::STATUS_LOCK) { ?>
                                            <div class="dropdown otherOptions fr">
                                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                   data-toggle="dropdown"
                                                   role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                        class="caret"></span></a>
                                                <ul class="dropdown-menu right" role="menu">

                                                    <li>
                                                        <a onclick="payment_method_fee.modalLock(
                                                            '<?= $data['id'] ?>')"
                                                           style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                    </li>

                                                    <?php if ($data['status'] == PaymentMethodFee::STATUS_NOT_REQUEST) { ?>
                                                        <li>
                                                            <a onclick="payment_method_fee.modalRequest(
                                                                '<?= $data['id'] ?>')"
                                                               style="cursor: pointer "><?= Translate::get('Gửi duyệt') ?></a>
                                                        </li>
                                                        <li>
                                                            <a title="<?= Translate::get('Sửa') ?>" href="#Edit" data-toggle="modal"
                                                               onclick="payment_method_fee.viewEdit(
                                                                   '<?= $data['id'] ?>',
                                                                   '<?= Yii::$app->urlManager->createUrl('payment-method-fee/view-edit') ?>'
                                                                   );">
                                                                <?= Translate::get('Sửa') ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($data['status'] == PaymentMethodFee::STATUS_REQUEST) { ?>
                                                        <li>
                                                            <a onclick="payment_method_fee.modalActive(
                                                                '<?= $data['id'] ?>')"
                                                               style="cursor: pointer "><?= Translate::get('Kích hoạt') ?></a>
                                                        </li>
                                                        <li>
                                                            <a onclick="payment_method_fee.modalReject(
                                                                '<?= $data['id'] ?>')"
                                                               style="cursor: pointer "><?= Translate::get('Từ chối') ?></a>
                                                        </li>
                                                    <?php } ?>

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
                <h4 class="modal-title"><?= Translate::get('Khóa phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-payment-method-fee-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method-fee/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa phí thanh toán này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:payment_method_fee.submitLock();"><?= Translate::get('Xác
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

<!--Gửi duyệt -->
<div class="modal fade" id="Request" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Gửi duyệt phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="request-payment-method-fee-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method-fee/request') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Gửi duyệt phí thanh toán này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:payment_method_fee.submitRequest();"><?= Translate::get('Xác
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

<!--Duyệt -->
<div class="modal fade" id="Active" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Kích hoạt phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="active-payment-method-fee-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method-fee/active') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Kích hoạt phí thanh toán này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:payment_method_fee.submitActive();"><?= Translate::get('Xác
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

<!--Từ chối -->
<div class="modal fade" id="Reject" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Từ chối kích hoạt phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="reject-payment-method-fee-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('payment-method-fee/reject') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Từ chối kích hoạt phí thanh toán này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:payment_method_fee.submitReject();"><?= Translate::get('Xác
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


<!-- Thêm -->
<div class="modal fade" id="Add" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Thêm phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-payment-method-fee-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('payment-method-fee/add'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phương thức thanh toán') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payment_method_id')->label(false)
                                ->dropDownList($payment_method_add) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'percentage_fee')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'flat_fee')->label(false)
                                ->textInput(array('class' => 'form-control input_number')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm người mua chịu') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payer_percentage_fee')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định người mua chịu') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payer_flat_fee')->label(false)
                                ->textInput(array('class' => 'form-control input_number')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('TG bắt đầu') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model, 'time_begin', [
                                'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                            ])->label(false)
                                ->textInput(array('class' => 'form-control datetimepaid')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('TG kết thúc') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model, 'time_end', [
                                'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                            ])->label(false)
                                ->textInput(array('class' => 'form-control datetimepaid')) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Thêm') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Cập nhật -->
<div class="modal fade" id="Edit" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Cập nhật phí thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-payment-method-fee-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('payment-method-fee/edit'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phương thức thanh toán') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payment_method_id')->label(false)
                                ->dropDownList($payment_method_add) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'percentage_fee')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'flat_fee')->label(false)
                                ->textInput(array('class' => 'form-control input_number')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí phần trăm người mua chịu') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payer_percentage_fee')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Phí cố định người mua chịu') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'payer_flat_fee')->label(false)
                                ->textInput(array('class' => 'form-control input_number')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('TG bắt đầu') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model, 'time_begin', [
                                'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                            ])->label(false)
                                ->textInput(array('class' => 'form-control datetimepaid')) ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('TG kết thúc') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model, 'time_end', [
                                'inputTemplate' => '<div class="input-group">{input} <span class="input-group-addon"><i class="fa-calendar"></i></span></div>',
                            ])->label(false)
                                ->textInput(array('class' => 'form-control datetimepaid')) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model, 'id')->label(false)
                    ->hiddenInput(array('class' => 'form-control')) ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>