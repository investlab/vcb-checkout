<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Method;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách nhóm phương thức thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách nhóm phương thức thanh toán') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons no-margin-mobile">
                <div class="addNew no-margin-mobile">
                    <a href="#Add_Method" data-toggle="modal" class="btn btn-sm btn-success">
                        <i class="en-plus3"></i>
                        <?= Translate::get('Thêm') ?>
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
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="transaction_type_id">
                    <?php
                    if (isset($transaction_type_arr) && count($transaction_type_arr) > 0) {
                        foreach ($transaction_type_arr as $key => $data) {
                            ?>
                            <option
                                value="<?= $key ?>" <?= (isset($search) && $search->transaction_type_id == $key) ? "selected='true'" : '' ?> >
                                <?= $data ?>
                            </option>
                        <?php
                        }
                    } ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã nhóm') ?>"
                       name="code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Tên nhóm') ?>"
                       name="name"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="status">
                    <option value="0"><?= Translate::get('Trạng thái') ?></option>
                    <option
                        value="1" <?= (isset($search) && $search != null && $search->status == 1) ? "selected='true'" : '' ?>>
                        <?= Translate::get('Đang hoạt động') ?>
                    </option>
                    <option
                        value="2" <?= (isset($search) && $search != null && $search->status == 2) ? "selected='true'" : '' ?>>
                        <?= Translate::get('Đã khóa') ?>
                    </option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                <a href="<?= Yii::$app->urlManager->createUrl('method/index') ?>"
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
            <?= Translate::get('Nhóm phương thức') ?>
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
                        <th class="text-center" width="35">ID</th>
                        <th class="text-center"><?= Translate::get('Mã') ?></th>
                        <th class="text-center"><?= Translate::get('Tên') ?></th>
                        <th class="text-center"><?= Translate::get('Loại giao dịch') ?></th>
                        <th class="text-center"><?= Translate::get('Ghi chú') ?></th>
                        <th class="text-center"><?= Translate::get('Vị trí') ?></th>
                        <th class="text-center"><?= Translate::get('Trạng thái') ?></th>
                        <th class="text-center"><?= Translate::get('Thao tác') ?></th>
                    </tr>
                    </thead>
                    <?php
                    if (is_array($page->data) && count($page->data) > 0) {
                        foreach ($page->data as $key => $data) {
                            ?>
                            <tbody>
                            <tr>
                                <th>
                                    <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                </th>
                                <td class="col col-sm-2">
                                    <?= isset($data['code']) && $data['code'] != null ? $data['code'] : '' ?>
                                </td>
                                <td class="col col-sm-2">
                                    <?= isset($data['name']) && $data['name'] != null ? Translate::get($data['name']) : '' ?>
                                </td>
                                <td class="col col-sm-2">
                                    <?= isset($data['transaction_type_name']) && $data['transaction_type_name'] != null ? Translate::get($data['transaction_type_name']) : '' ?>
                                </td>
                                <td class="col col-sm-2">
                                    <div class="small">
                                        <?= isset($data['description']) && $data['description'] != null ? $data['description'] : '' ?>
                                    </div>
                                </td>
                                <td class="col col-sm-1">
                                    <div class="small text-center">
                                        <?= isset($data['position']) && $data['position'] != null ? $data['position'] : '' ?>
                                    </div>
                                </td>

                                <td class="col col-sm-2">
                                    <?php if ($data['status'] == Method::STATUS_ACTIVE) { ?>
                                        <span class="label label-success"><?= Translate::get('Đang hoạt động') ?></span>
                                    <?php } elseif ($data['status'] == Method::STATUS_LOCK) { ?>
                                        <span class="label label-danger"><?= Translate::get('Đã khóa') ?></span>
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

                                <td>
                                    <div class="dropdown otherOptions fr">
                                        <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                           data-toggle="dropdown"
                                           role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                class="caret"></span></a>
                                        <ul class="dropdown-menu right" role="menu">
                                            <?php if ($data['status'] == 1) { ?>
                                                <li>
                                                    <a title="<?= Translate::get('Sửa') ?>" href="#Edit_Method" data-toggle="modal"
                                                       onclick="method.viewEditMethod(
                                                           '<?= $data['id'] ?>',
                                                           '<?= Yii::$app->urlManager->createUrl('method/view-edit') ?>'
                                                           );">
                                                        <?= Translate::get('Sửa') ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a title="<?= Translate::get('Cập nhật phương thức thanh toán') ?>"
                                                       href="<?= Yii::$app->urlManager->createUrl(['method/payment-method', 'id' => $data['id']]) ?>">
                                                        <?= Translate::get('Cập nhật phương thức thanh toán') ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a onclick="method.modalLockMethod('<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                       style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <a onclick="method.modalUnLockMethod('<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                       style="cursor: pointer "><?= Translate::get('Mở Khóa') ?></a>
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

<!-- Thêm ngân hàng -->
<div class="modal fade" id="Add_Method" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Thêm nhóm phương thức') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-method-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('method/add'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại giao dịch') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_method, 'transaction_type_id')->label(false)->dropDownList($transaction_type_arr,
                                [
                                    'id' => 'transaction_type_id',
                                    'class' => 'form-control',
                                ]); ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã nhóm') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_method, 'code')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên nhóm') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_method, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Vị trí') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_method, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_method, 'description')->label(false)
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
<!-- Sửa ngân hàng -->
<div class="modal fade" id="Edit_Method" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Sửa nhóm phương thức') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-method-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('method/edit'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <div class=form-group hidden="hidden">
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Loại giao dịch') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_method, 'transaction_type_id')->label(false)->dropDownList($transaction_type_arr,
                                [
                                    'id' => 'transaction_type_id',
                                    'class' => 'form-control',
                                    'readonly' => true
                                ]); ?>

                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã nhóm phương thức') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_method, 'code')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên nhóm phương thức') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_method, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Vị trí') ?></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_method, 'position')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?> </label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_method, 'description')->label(false)
                                ->textarea(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model_method, 'id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Khóa tài khoản-->
<div class="modal fade" id="LockMethod" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa nhóm phương thức thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-method-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('method/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn') ?> <strong><?= Translate::get('Khóa nhóm phương thức không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:method.submitLockMethod();"><?= Translate::get('Xác
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

<!-- Modal Mở khóa thông NCC-->
<div class="modal fade" id="UnlockMethod" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Mở khóa nhóm phương thức thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <form id="unlock-method-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('method/unlock') ?>">
                        <!-- content in modal, tinyMCE 4 texarea -->

                        <div class="alert alert-warning fade in" align="center">
                            Bạn có chắc chắn muốn <strong><?= Translate::get('Mở khóa nhóm phương thức') ?> <span
                                    id="unLockMName"></span></strong>
                            này?
                            <input name="id" type="hidden">
                        </div>
                        <!-- End .form-group  -->
                        <div class="form-group" align="center">
                            <a class="btn btn-primary" href="javascript:method.submitUnLockMethod();"><?= Translate::get('Xác
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


<!-- InstanceEndEditable -->
</div>

</div>
</div>