<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Zone;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách Tỉnh - Thành Phố');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách Tỉnh - Thành Phố') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <div class="addNew"><a href="#Add" data-toggle="modal" class="btn btn-sm btn-success"><i
                                class="en-plus3"></i> <?= Translate::get('Thêm') ?></a></div>
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
            <div class="row">
                <div class="col-md-2 ui-sortable">
                    <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('Thời gian tạo từ') ?>"
                           name="time_created_from"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                    <i class="im-calendar s16 left-input-icon"></i>
                </div>
                <div class="col-md-2 ui-sortable">
                    <input type="text" class="form-control left-icon datepicker" placeholder="<?= Translate::get('đến ngày') ?>"
                           name="time_created_to"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                    <i class="im-calendar s16 left-input-icon"></i>
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control" placeholder="<?= Translate::get('Tên Tỉnh - Thành Phố') ?>"
                           name="name"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
                </div>

                <div class="col-md-2">
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
                <div class="col-md-3">
                    <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                    &nbsp;
                    <a href="<?= Yii::$app->urlManager->createUrl('city/index') ?>"
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
                    <?= Translate::get('Tỉnh - Thành Phố') ?>
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
                                <strong><?= Translate::get('Thông báo') ?>!!</strong> <?= $data ?>.<br>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <form method="post"
                          action="<?= Yii::$app->urlManager->createUrl(['city/update-position']) ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= Translate::get('Tên') ?></th>
                                    <th width="85"><?= Translate::get('Vị trí') ?>
                                        <button type="submit"><i class="fa-refresh"></i></button>
                                    </th>
                                    <th class="text-center"><?= Translate::get('Trạng thái') ?></th>
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
                                                <strong><?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?></strong>
                                            </td>
                                            <td>
                                                <?= isset($data['name']) && $data['name'] != null ? $data['name'] : '' ?>
                                            </td>
                                            <td>
                                                <input class="form-control col-sm-1 text-center" name="positions[]"
                                                       value="<?= $data["position"] ?>"/>
                                                <input type="hidden" name="ids[]" value="<?= $data["id"] ?>"/>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($data['status'] == Zone::STATUS_ACTIVE) { ?>
                                                    <span class="label label-success"><?= Translate::get('Kích hoạt') ?></span>
                                                <?php } elseif ($data['status'] == Zone::STATUS_LOCK) { ?>
                                                    <span class="label label-danger"><?= Translate::get('Bị khóa') ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>

                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button" aria-expanded="false"><?= Translate::get('Thao tác ') ?><span
                                                            class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <li>
                                                            <a title="Sửa" href="#Edit" data-toggle="modal"
                                                               onclick="city.viewEdit(
                                                                   '<?= $data['id'] ?>',
                                                                   '<?= Yii::$app->urlManager->createUrl('city/view-edit') ?>'
                                                                   );">
                                                                <?= Translate::get('Sửa') ?>
                                                            </a>
                                                        </li>

                                                        <?php if ($data['status'] == Zone::STATUS_ACTIVE) { ?>
                                                            <li>
                                                                <a onclick="city.modalLock(
                                                                    '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                                   style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                            </li>
                                                        <?php } elseif ($data['status'] == Zone::STATUS_LOCK) { ?>
                                                            <li>
                                                                <a onclick="city.modalActive(
                                                                    '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                                   style="cursor: pointer "><?= Translate::get('Kích hoạt') ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        </tbody>
                                    <?php
                                    }
                                } ?>
                            </table>
                        </div>
                    </form>
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

<!--Kích hoạt -->
<div class="modal fade" id="Active" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Kích hoạt Tỉnh - Thành Phố') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="active-city-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('city/active') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn') ?> <strong><?= Translate::get('Kích hoạt') ?> <span id="activeBNumber"></span></strong> <?= Translate::get('không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:city.submitActive();"><?= Translate::get('Xác
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

<!--Khóa -->
<div class="modal fade" id="Lock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa Tỉnh - Thành Phố') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-city-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('city/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn') ?> <strong><?= Translate::get('Khóa') ?> <span id="lockBNumber"></span></strong> <?= Translate::get('không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:city.submitLock();"><?= Translate::get('Xác
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
                <h4 class="modal-title"><?= Translate::get('Thêm Tỉnh - Thành Phố') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-city-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('city/add'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Vị trí') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Khoảng cách') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'remote')->
                            dropDownList($remote_arr, ['id' => 'remote', 'class' => 'form-control'])->label(false); ?>
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
                <h4 class="modal-title"><?= Translate::get('Cập nhật Tỉnh - Thành Phố') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-city-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('city/update'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Vị trí') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Khoảng cách') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'remote')->
                            dropDownList($remote_arr, ['id' => 'remote', 'class' => 'form-control'])->label(false); ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>

                <?= $form->field($model_update, 'id')->label(false)
                    ->hiddenInput(array('class' => 'form-control')) ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

