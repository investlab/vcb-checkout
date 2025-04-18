<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách ngân hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách ngân hàng') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons no-margin-mobile">
                <div class="addNew no-margin-mobile"><a href="#Add_Bank" data-toggle="modal" class="btn btn-sm btn-success"><i
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
                <input type="text" class="form-control" placeholder="<?= Translate::get("Mã ngân hàng") ?>"
                       name="code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get("Tên thương mại") ?>"
                       name="trade_name"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->trade_name) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get("Tên ngân hàng") ?>"
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
                <a href="<?= Yii::$app->urlManager->createUrl('bank/index') ?>" class="btn btn-default">
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
            <?= Translate::get('Ngân hàng') ?>
            &nbsp;|&nbsp; <?= Translate::get('Đã khóa') ?> <strong
                class="text-danger"><?= (isset($page->totalLock) ? $page->totalLock : '0') ?></strong>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <?php
            if (is_array($page->data) && count($page->data) == 0) {
                ?>

                <div class="alert alert-danger fade in">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong><?= Translate::get('Thông báo')?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp')?>.
                </div>
            <?php } ?>
            <div class="table-responsive">
                <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th width="35">ID</th>
                        <th><?= Translate::get('Mã NH') ?></th>
                        <th><?= Translate::get('Tên thương mại') ?></th>
                        <th><?= Translate::get('Tên đầy đủ') ?></th>
                        <th><?= Translate::get('Ghi chú') ?></th>
                        <th><?= Translate::get('Trạng thái') ?></th>
                        <th>
                            <div align="right"><?= Translate::get('Thao tác') ?></div>
                        </th>
                    </tr>
                    </thead>
                    <?php
                    if (is_array($page->data) && count($page->data) > 0) {
                        foreach ($page->data as $key => $data) {
                            ?>
                            <tbody>
                            <tr>
                                <th>
                                    <?= $data['id'] ?>
                                </th>
                                <td>
                                    <?= Translate::get($data->code) ?>
                                </td>
                                <td>
                                    <?= Translate::get($data->trade_name) ?>
                                </td>
                                <td>
                                    <?= Translate::get($data->name) ?>
                                </td>
                                <td>
                                    <?= Translate::get($data->description) ?>
                                </td>
                                <td>
                                    <?php if ($data->status == 1) { ?>
                                        <span class="label label-success"><?= Translate::get('Đang hoạt động') ?></span>
                                    <?php } elseif ($data->status == 2) { ?>
                                        <span class="label label-danger"><?= Translate::get('Đã khóa') ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="dropdown otherOptions fr">
                                        <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                           data-toggle="dropdown"
                                           role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span class="caret"></span></a>
                                        <ul class="dropdown-menu right" role="menu">
                                            <?php if ($data->status == 1) { ?>
                                                <li>
                                                    <a title="Sửa" href="#Edit_Bank" data-toggle="modal"
                                                       onclick="bank.viewEditBank(
                                                           '<?= $data->id ?>',
                                                           '<?= Yii::$app->urlManager->createUrl('bank/view-edit') ?>'
                                                           );">
                                            <?= Translate::get('Sửa') ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a onclick="bank.modalLockBank('<?= $data->id ?>','<?= $data->name?>')"
                                                       style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <a onclick="bank.modalUnLockBank('<?= $data->id ?>','<?= $data->name?>')"
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
<div class="modal fade" id="Add_Bank" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Thêm ngân hàng') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-bank-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('bank/create-bank'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã ngân hàng') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_bank, 'code')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên thương mại') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_bank, 'trade_name')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên đầy đủ') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_bank, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model_bank, 'description')->label(false)
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
<div class="modal fade" id="Edit_Bank" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Sửa ngân hàng') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-bank-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('bank/edit-bank'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã ngân hàng') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_bank, 'code')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên thương mại') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_bank, 'trade_name')->label(false)
                                ->textInput(array('class' => 'form-control text-uppercase')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên đầy đủ') ?> <span
                                class="text-danger">*</span></label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_bank, 'name')->label(false)
                                ->textInput(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?> </label>

                        <div class="col-lg-8 col-md-8">
                            <?= $form->field($model_bank, 'description')->label(false)
                                ->textarea(array('class' => 'form-control')) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model_bank, 'id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Khóa tài khoản-->
<div class="modal fade" id="LockBank" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa tài khoản ngân hàng') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-bank-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('bank/lock-bank') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            Bạn có chắc chắn muốn <strong><?= Translate::get('Khóa tài khoản') ?> <span id="lockBNumber"></span></strong> không?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:bank.submitLockBank();"><?= Translate::get('Xác
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
<div class="modal fade" id="UnlockBank" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Mở khóa tài khoản ngân hàng') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <form id="unlock-bank-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('bank/unlock-bank') ?>">
                        <!-- content in modal, tinyMCE 4 texarea -->

                        <div class="alert alert-warning fade in" align="center">
                            Bạn có chắc chắn muốn <strong><?= Translate::get('Mở khóa tài khoản') ?> <span id="unLockBNumber"></span></strong> này?
                            <input name="id" type="hidden">
                        </div>
                        <!-- End .form-group  -->
                        <div class="form-group" align="center">
                            <a class="btn btn-primary" href="javascript:bank.submitUnLockBank();"><?= Translate::get('Xác
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