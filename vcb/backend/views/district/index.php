<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 4/14/2016
 * Time: 2:50 PM
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Zone;
use common\components\utils\ObjInput;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách Quận - Huyện';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header>Danh sách Quận - Huyện</h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <div class="addNew"><a href="#Add" data-toggle="modal" class="btn btn-sm btn-success"><i
                                class="en-plus3"></i> Thêm</a></div>
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
                    <input type="text" class="form-control left-icon datepicker" placeholder="Thời gian tạo từ"
                           name="time_created_from"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_from) : '' ?>">
                    <i class="im-calendar s16 left-input-icon"></i>
                </div>
                <div class="col-md-2 ui-sortable">
                    <input type="text" class="form-control left-icon datepicker" placeholder="đến ngày"
                           name="time_created_to"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->time_created_to) : '' ?>">
                    <i class="im-calendar s16 left-input-icon"></i>
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control" placeholder="Tên Quận - Huyện"
                           name="name"
                           value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="city_id">
                        <option value="0">Chọn Tỉnh - Thành Phố</option>
                        <?php
                        foreach ($city_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $key ?>" <?= (isset($search) && $search->city_id == $key) ? "selected='true'" : '' ?> >
                                <?= $data ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-control" name="status">
                        <option value="0">Chọn trạng thái</option>
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
                <div class="col-md-3">
                    <button class="btn btn-danger" type="submit">Tìm kiếm</button>
                    &nbsp;
                    <a href="<?= Yii::$app->urlManager->createUrl('district/index') ?>"
                       class="btn btn-default">
                        Bỏ lọc
                    </a>
                </div>
            </div>

        </form>
    </div>
    <div class=row>
        <div class=col-md-12>
            <div class="clearfix" style="border-bottom:1px solid #dcdcdc; margin-bottom:15px; padding-bottom:10px">
                <div class="col-md-6" style="margin-left:-15px">Có <strong
                        class="text-danger"><?php echo $page->pagination->totalCount; ?></strong>
                    Quận - Huyện
                    &nbsp;|&nbsp;
                    Kích hoạt <strong
                        class="text-danger"><?= (isset($page->count_active) ? $page->count_active : '0') ?></strong>
                    &nbsp;|&nbsp;
                    Bị khóa <strong
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
                            <strong>Thông báo</strong> Không tìm thấy kết quả nào phù hợp.
                        </div>
                    <?php } ?>
                    <?php
                    if ($page->errors != null) {
                        ?>
                        <div class="alert alert-danger fade in">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php foreach ($page->errors as $key => $data) { ?>
                                <strong>Thông báo!!</strong> <?= $data ?>.<br>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <form method="post"
                          action="<?= Yii::$app->urlManager->createUrl(['district/update-position']) ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên Thành Phố</th>
                                    <th>Tên Quận - Huyện</th>
                                    <th width="85">Vị trí
                                        <button type="submit"><i class="fa-refresh"></i></button>
                                    </th>
                                    <th class="text-center">Trạng thái</th>
                                    <th>
                                        <div align="right">Thao tác</div>
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
                                                <?= isset($data['city_name']) && $data['city_name'] != null ? $data['city_name'] : '' ?>
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
                                                    <span class="label label-success">Kích hoạt</span>
                                                <?php } elseif ($data['status'] == Zone::STATUS_LOCK) { ?>
                                                    <span class="label label-danger">Bị khóa</span>
                                                <?php } ?>
                                            </td>
                                            <td>

                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button" aria-expanded="false">Thao tác <span
                                                            class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <li>
                                                            <a title="Sửa" href="#Edit" data-toggle="modal"
                                                               onclick="district.viewEdit(
                                                                   '<?= $data['id'] ?>',
                                                                   '<?= Yii::$app->urlManager->createUrl('district/view-edit') ?>'
                                                                   );">
                                                                Sửa
                                                            </a>
                                                        </li>

                                                        <?php if ($data['status'] == Zone::STATUS_ACTIVE) { ?>
                                                            <li>
                                                                <a onclick="district.modalLock(
                                                                    '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                                   style="cursor: pointer ">Khóa</a>
                                                            </li>
                                                        <?php } elseif ($data['status'] == Zone::STATUS_LOCK) { ?>
                                                            <li>
                                                                <a onclick="district.modalActive(
                                                                    '<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                                   style="cursor: pointer ">Kích hoạt</a>
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
                                'nextPageLabel' => 'Tiếp',
                                'prevPageLabel' => 'Sau',
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
                <h4 class="modal-title">Kích hoạt Tỉnh - Thành Phố</h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="active-district-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('district/active') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            Bạn có chắc chắn muốn <strong>Kích hoạt <span id="activeBNumber"></span></strong> không?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:district.submitActive();">Xác
                            nhận</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ
                            qua
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
                <h4 class="modal-title">Khóa Tỉnh - Thành Phố</h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-district-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('district/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            Bạn có chắc chắn muốn <strong>Khóa <span id="lockBNumber"></span></strong> không?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:district.submitLock();">Xác
                            nhận</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ
                            qua
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
                <h4 class="modal-title">Thêm Quận - Huyện</h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-district-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('district/add'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tỉnh - Thành Phố<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'parent_id')->label(false)
                                ->dropDownList($city_arr) ?>
                        </div>
                    </div>


                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tên<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Vị trí<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Khoảng cách<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'remote')->
                            dropDownList($remote_arr, ['id' => 'remote', 'class' => 'form-control'])->label(false); ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary">Thêm</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
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
                <h4 class="modal-title">Cập nhật Quận - Huyện</h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-district-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('district/update'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tỉnh - Thành phố<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'parent_id')->label(false)
                                ->dropDownList($city_arr) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Tên<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Vị trí<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label">Khoảng cách<span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_update, 'remote')->
                            dropDownList($remote_arr, ['id' => 'remote', 'class' => 'form-control'])->label(false); ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
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

