<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PartnerPayment;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách kênh thanh toán');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách kênh thanh toán') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons no-margin-mobile">
                    <div class="addNew no-margin-mobile">
                        <a href="#Add_Partner_Payment" data-toggle="modal" class="btn btn-sm btn-success">
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
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã kênh') ?>"
                               name="code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Tên kênh') ?>"
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
                        <a href="<?= Yii::$app->urlManager->createUrl('partner-payment/index') ?>"
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
                        <?= Translate::get('Kênh thanh toán') ?>
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
                                    <th>STT</th>
                                    <th><?= Translate::get('Mã kênh') ?></th>
                                    <th><?= Translate::get('Tên kênh') ?></th>
                                    <th><?= Translate::get('Ghi chú') ?></th>
                                    <th><?= Translate::get('Trạng thái') ?></th>
                                    <th>
                                        <div align="right"><?= Translate::get('Thao tác')?></div>
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
                                                <?= ($key + ($page->pagination->page * $page->pagination->pageSize)) + 1 ?>
                                            </th>
                                            <td>
                                                <?= isset($data['code']) && $data['code'] != null ? $data['code'] : '' ?>
                                            </td>
                                            <td>
                                                <?= isset($data['name']) && $data['name'] != null ? Translate::get($data['name']) : '' ?>
                                            </td>
                                            <td>
                                                <?= isset($data['description']) && $data['description'] != null ? $data['description'] : '' ?>
                                            </td>
                                            <td>
                                                <?php if ($data['status'] == PartnerPayment::STATUS_ACTIVE) { ?>
                                                    <span class="label label-success"><?= Translate::get('Đang hoạt động') ?></span>
                                                <?php } elseif ($data['status'] == PartnerPayment::STATUS_LOCK) { ?>
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
                                                                <a title="Sửa" href="#Edit_Partner_Payment" data-toggle="modal"
                                                                   onclick="partner_payment.viewEdit(
                                                                       '<?= $data['id'] ?>',
                                                                       '<?= Yii::$app->urlManager->createUrl('partner-payment/view-edit') ?>'
                                                                       );">
                                                                    <?= Translate::get('Sửa') ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a onclick="partner_payment.modalLock('<?= $data['id'] ?>','<?= $data['name'] ?>')"
                                                                   style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <a onclick="partner_payment.modalUnLock('<?= $data['id'] ?>','<?= $data['name'] ?>')"
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
            <div class="modal fade" id="Add_Partner_Payment" tabindex=-1 role=dialog aria-hidden=true>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?= Translate::get('Thêm kênh') ?></h4>
                        </div>
                        <div class="modal-body">
                            <!-- content in modal, tinyMCE 4 texarea -->
                            <?php
                            $form = ActiveForm::begin(['id' => 'add-partner-payment-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('partner-payment/add'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="form-horizontal" role=form>

                                <!-- End .form-group  -->
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Mã kênh') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-9 col-md-9">
                                        <?= $form->field($model, 'code')->label(false)
                                            ->textInput(array('class' => 'form-control text-uppercase')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Tên kênh') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-9 col-md-9">
                                        <?= $form->field($model, 'name')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?></label>

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
            <!-- Sửa ngân hàng -->
            <div class="modal fade" id="Edit_Partner_Payment" tabindex=-1 role=dialog aria-hidden=true>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?= Translate::get('Cập nhật kênh') ?></h4>
                        </div>
                        <div class="modal-body">
                            <!-- content in modal, tinyMCE 4 texarea -->
                            <?php
                            $form = ActiveForm::begin(['id' => 'edit-partner-payment-form',
                                'enableAjaxValidation' => true,
                                'action' => Yii::$app->urlManager->createUrl('partner-payment/edit'),
                                'options' => ['enctype' => 'multipart/form-data']])
                            ?>
                            <div class="form-horizontal" role=form>

                                <!-- End .form-group  -->
                                <div class=form-group>
                                    <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã kênh') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'code')->label(false)
                                            ->textInput(array('class' => 'form-control text-uppercase')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Tên kênh') ?> <span
                                            class="text-danger">*</span></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'name')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã token') ?></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'token_key')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Mã checksum') ?></label>

                                    <div class="col-lg-8 col-md-8">
                                        <?= $form->field($model, 'checksum_key')->label(false)
                                            ->textInput(array('class' => 'form-control')) ?>
                                    </div>
                                </div>
                                <div class=form-group>
                                    <label class="col-lg-4 col-md-4 col-sm-12 control-label"><?= Translate::get('Ghi chú') ?> </label>

                                    <div class="col-lg-8 col-md-8">
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
            <!--Khóa tài khoản-->
            <div class="modal fade" id="LockPartnerPayment" tabindex=-1 role=dialog aria-hidden=true>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?= Translate::get('Khóa kênh thanh toán') ?></h4>
                        </div>
                        <div class="modal-body">
                            <!-- content in modal, tinyMCE 4 texarea -->
                            <div class="form-horizontal" role="form">
                                <form id="lock-partner-payment-form" method="post"
                                      action="<?= Yii::$app->urlManager->createUrl('partner-payment/lock') ?>">
                                    <div class="alert alert-warning fade in" align="center">
                                       <?= Translate::get('Bạn có chắc chắn muốn Khóa kênh này không') ?>?
                                        <input name="id" type="hidden">
                                    </div>
                                </form>
                                <!-- End .form-group  -->

                                <div class="form-group" align="center">
                                    <a class="btn btn-primary" href="javascript:partner_payment.submitLock();"><?= Translate::get('Xác
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
            <div class="modal fade" id="UnlockPartnerPayment" tabindex=-1 role=dialog aria-hidden=true>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?= Translate::get('Mở khóa kênh thanh toán') ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-horizontal" role="form">
                                <form id="unlock-partner-payment-form" method="post"
                                      action="<?= Yii::$app->urlManager->createUrl('partner-payment/unlock') ?>">
                                    <!-- content in modal, tinyMCE 4 texarea -->

                                    <div class="alert alert-warning fade in" align="center">
                                        <?= Translate::get('Bạn có chắc chắn muốn Mở khóa kênh này') ?>?
                                        <input name="id" type="hidden">
                                    </div>
                                    <!-- End .form-group  -->
                                    <div class="form-group" align="center">
                                        <a class="btn btn-primary" href="javascript:partner_payment.submitUnLock();"><?= Translate::get('Xác
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