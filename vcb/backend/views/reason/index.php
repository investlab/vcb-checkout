<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách lý do hủy');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách lý do hủy') ?></h1>
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
                <div class="row group-input-search">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Tạo : Từ ngày') ?>"
                               id="time_created_from" name="time_created_from"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                               id="time_created_to" name="time_created_to"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                        <i class="im-calendar s16 left-input-icon"></i></div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Tên lý do') ?>"
                               id="name" name="name"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="type">
                            <option value="0"><?= Translate::get('Loại lý do') ?></option>
                            <?php
                            foreach ($reason_type as $key => $rt) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->type == $key) ? "selected='true'" : '' ?> >
                                   <?= Translate::get($rt) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="status">
                            <option value="0"><?= Translate::get('Trạng thái') ?></option>
                            <?php
                            foreach ($reason_status as $key => $rs) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->status == $key) ? "selected='true'" : '' ?> >
                                   <?= Translate::get($rs) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('reason/index') ?>"
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
                            class="text-danger"><?= $page->pagination->totalCount; ?></strong> <?= Translate::get('Lý do') ?>
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
                                    <th>ID</th>
                                    <th><?= Translate::get('Loại lý do') ?></th>
                                    <th><?= Translate::get('Tên lý do') ?></th>
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
                                        <tr>
                                            <td>
                                                <?= isset($data->id) && $data->id != null ? $data->id : '' ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?php if (isset($data->type) && $data->type == 1) { ?>
                                                    <span class="text-danger"> <?= Translate::get('Hủy đơn hàng') ?></span>
                                                <?php } elseif (isset($data->type) && $data->type == 2) { ?>
                                                    <span class="text-warning">  <?= Translate::get('Hoàn tiền') ?></span>
                                                <?php } elseif (isset($data->type) && $data->type == 3) { ?>
                                                    <span class="text-primary"> <?= Translate::get('Hủy giao hàng') ?></span>
                                                <?php } elseif (isset($data->type) && $data->type == 4) { ?>
                                                    <span class="text-dark"> <?= Translate::get('Rút tiền') ?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?= isset($data->name) && $data->name != null ? $data->name : '' ?>
                                            </td>
                                            <td class="col-sm-4">
                                                <?= isset($data->description) && $data->description != null ? $data->description : '' ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?php if (isset($data->status) && $data->status == 1) { ?>
                                                    <span class="label label-success"><?= Translate::get('Đang hoạt động') ?></span>
                                                <?php } else { ?>
                                                    <span class="label label-danger"><?= Translate::get('Bị khóa') ?></span>
                                                <?php } ?>
                                                <br><br>
                                                <hr>
                                                <div class="small">
                                                    <?php if (intval($data['time_created']) > 0): ?>
                                                        <?= Translate::get('Tạo') ?>: <strong><?= date('H:i, d/m/Y', $data['time_created']) ?></strong>
                                                        <br>
                                                    <?php endif; ?>
                                                    <?php if (intval($data['time_updated']) > 0): ?>
                                                        <?= Translate::get('Cập nhật') ?>:
                                                        <strong><?= date('H:i, d/m/Y', $data['time_updated']) ?></strong><br>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <td class="col-sm-2">
                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                            class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <?php if ($data->status == 1) { ?>
                                                            <li>
                                                                <a title="<?= Translate::get('Sửa') ?>" href="#Edit" data-toggle="modal"
                                                                   onclick="reason.viewEdit(
                                                                       '<?= $data->id ?>',
                                                                       '<?= Yii::$app->urlManager->createUrl('reason/view-edit') ?>'
                                                                       );">
                                                                    <?= Translate::get('Sửa') ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a onclick="reason.modalLock('<?= $data->id ?>','<?= $data->name?>')"
                                                                   style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <a onclick="reason.modalUnLock('<?= $data->id ?>','<?= $data->name?>')"
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

</div>
<!-- Thêm -->
<div class="modal fade" id="Add" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Thêm lý do') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-reason-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('reason/create'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại lý do') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'type')->label(false)->dropDownList($reason_type, ['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên lý do') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mô tả') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'description')->label(false)
                                ->textarea(array('class' => 'form-control')) ?>
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

<!-- Sửa -->
<div class="modal fade" id="Edit" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Sửa lý do') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-reason-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('reason/edit'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại lý do') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'type')->label(false)->dropDownList($reason_type, ['class' => 'form-control']); ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên lý do') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mô tả') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'description')->label(false)
                                ->textarea(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model, 'id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--Khóa -->
<div class="modal fade" id="Lock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa lý do') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-reason-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('reason/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa lý do này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:reason.submitLock();"><?= Translate::get('Xác
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

<!-- Modal Mở khóa -->
<div class="modal fade" id="Unlock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Mở khóa Lý do') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <form id="unlock-reason-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('reason/unlock') ?>">
                        <!-- content in modal, tinyMCE 4 texarea -->

                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Mở khóa lý do này') ?>?
                            <input name="id" type="hidden">
                        </div>
                        <!-- End .form-group  -->
                        <div class="form-group" align="center">
                            <a class="btn btn-primary" href="javascript:reason.submitUnLock();"><?= Translate::get('Xác
                                nhận') ?></a>
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?>
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
