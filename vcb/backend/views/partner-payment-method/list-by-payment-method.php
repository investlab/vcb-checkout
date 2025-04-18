<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\PaymentMethod;
use common\models\db\PartnerPaymentMethod;
use common\components\utils\ObjInput;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách kênh thanh toán hỗ trợ');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    PartnerPaymentMethod::STATUS_ACTIVE => 'bg bg-default',
    PartnerPaymentMethod::STATUS_LOCK => 'bg bg-danger',
);
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách kênh thanh toán hỗ trợ') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <a href="#Add" data-toggle="modal" class="btn btn-sm btn-success"><i
                            class="en-plus3"></i> <?= Translate::get('Thêm') ?></a>
                    <a href="<?= Yii::$app->urlManager->createUrl('payment-method/index') ?>"
                       data-toggle="modal" class="btn btn-sm btn-danger"><i
                            class="en-back"></i> <?= Translate::get('Quay lại') ?></a>
                </div>
            </div>
            <!-- InstanceEndEditable -->
        </div>
    </div>
    <!-- End .page-header -->
</div>
<!-- End .row -->
<div class=outlet>

    <div class=row>
        <div class=col-md-12>
            <div class="clearfix" style="border-bottom:1px solid #dcdcdc; margin-bottom:15px; padding-bottom:10px">
                <div class="col-md-6" style="margin-left:-15px"><?= Translate::get('Có') ?> <strong
                        class="text-danger"><?php echo count($partner_payment_method) ?></strong>
                    <?= Translate::get('Kênh') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    if (is_array($partner_payment_method) && count($partner_payment_method) == 0) {
                        ?>

                        <div class="alert alert-danger fade in">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <strong><?= Translate::get('Thông báo') ?></strong><?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
                        </div>
                    <?php } ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th width="35">ID</th>
                                <th><?= Translate::get('Kênh thanh toán') ?></th>
                                <th><?= Translate::get('Môi trường sử dụng') ?></th>
                                <th><?= Translate::get('Vị trí') ?></th>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <th><?= Translate::get('Trạng thái') ?></th>
                                <th>
                                    <div align="right"><?= Translate::get('Thao tác') ?></div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (is_array($partner_payment_method) && count($partner_payment_method) > 0) {
                                foreach ($partner_payment_method as $key => $data) {
                                    ?>                                    
                                    <tr class="<?=@$array_color[$data['status']]?>">
                                        <td>
                                            <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                        </td>
                                        <td class="col-md-3">
                                            <?= Translate::get('Mã') ?> :
                                            <strong><?= isset($data['partner_payment_code']) && $data['partner_payment_code'] != null ? $data['partner_payment_code'] : '' ?></strong>
                                            <br> <?= Translate::get('Tên') ?> :
                                            <strong><?= isset($data['partner_payment_name']) && $data['partner_payment_name'] != null ? $data['partner_payment_name'] : '' ?></strong>
                                        </td>
                                        <td class="col-md-2">
                                            <?= isset($data['enviroment']) && $data['enviroment'] != null ? $data['enviroment'] : '' ?>
                                        </td>
                                        <td class="col-md-2">
                                            <?= isset($data['position']) && $data['position'] != null ? $data['position'] : '' ?>
                                        </td>
                                        <td class="col-md-2">
                                            <div class="small">
                                                <?= Translate::get('Tạo') ?>
                                                : <?= isset($data['time_created']) && $data['time_created'] != null ? date('H:i,d-m-Y', $data['time_created']) : '' ?>
                                                <br>
                                                <?= Translate::get('Cập nhật') ?>
                                                : <?= isset($data['time_updated']) && $data['time_updated'] != null ? date('H:i,d-m-Y', $data['time_updated']) : '' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($data['status'] == PartnerPaymentMethod::STATUS_ACTIVE) { ?>
                                                <span class="label label-success"><?= Translate::get('Đang sử dụng') ?></span>
                                            <?php } elseif ($data['status'] == PartnerPaymentMethod::STATUS_LOCK) { ?>
                                                <span class="label label-danger"><?= Translate::get('Bị khóa') ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <div class="dropdown otherOptions fr">
                                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                   data-toggle="dropdown"
                                                   role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                        class="caret"></span></a>
                                                <ul class="dropdown-menu right" role="menu">
                                                    <li>
                                                        <a title=<?= Translate::get('Sửa') ?> href="#Edit" data-toggle="modal"
                                                           onclick="partner_payment_method.viewEdit(
                                                               '<?= $data['id'] ?>',
                                                               '<?= Yii::$app->urlManager->createUrl('partner-payment-method/view-edit') ?>'
                                                               );">
                                                            <?= Translate::get('Sửa') ?>
                                                        </a>
                                                    </li>
                                                    <?php if ($data['status'] == PartnerPaymentMethod::STATUS_ACTIVE) { ?>
                                                        <li>
                                                            <a onclick="partner_payment_method.modalLock(
                                                                '<?= $data['id'] ?>','<?= $data['payment_method_id'] ?>')"
                                                               style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                        </li>

                                                    <?php } elseif ($data['status'] == PartnerPaymentMethod::STATUS_LOCK) { ?>
                                                        <li>
                                                            <a onclick="partner_payment_method.modalUnLock(
                                                                '<?= $data['id'] ?>','<?= $data['payment_method_id'] ?>')"
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
                <h4 class="modal-title"><?= Translate::get('Khóa kênh thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-partner-payment-method-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('partner-payment-method/lock-in-payment-method') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa kênh thanh toán này không') ?>?
                            <input name="id" type="hidden">
                            <input name="payment_method_id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:partner_payment_method.submitLock();"><?= Translate::get('Xác
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
                <h4 class="modal-title"><?= Translate::get('Mở khóa kênh thanh toán') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="unlock-partner-payment-method-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('partner-payment-method/unlock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Mở khóa kênh thanh toán này không') ?>?
                            <input name="id" type="hidden">
                            <input name="payment_method_id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:partner_payment_method.submitUnLock();"><?= Translate::get('Xác
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
                <h4 class="modal-title"><?= Translate::get('Thêm kênh thanh toán hỗ trợ') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'add-partner-payment-method-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('partner-payment-method/add'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kênh thanh toán hỗ trợ') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'partner_payment_id')->label(false)
                                ->dropDownList($partner_payment_arr) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Môi trường sử dụng') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'enviroment')->label(false)
                                ->dropDownList($enviroments_arr) ?>
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
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Thêm') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model, 'payment_method_id')->label(false)
                    ->hiddenInput(array('class' => 'form-control')) ?>
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
                <h4 class="modal-title"><?= Translate::get('Cập nhật kênh thanh toán hỗ trợ') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'edit-partner-payment-method-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('partner-payment-method/edit'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Kênh thanh toán hỗ trợ') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'partner_payment_id')->label(false)
                                ->dropDownList($partner_payment_arr) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Môi trường sử dụng') ?><span
                                class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'enviroment')->label(false)
                                ->dropDownList($enviroments_arr) ?>
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
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($model, 'payment_method_id')->label(false)
                    ->hiddenInput(array('class' => 'form-control')) ?>
                <?= $form->field($model, 'id')->label(false)
                    ->hiddenInput(array('class' => 'form-control')) ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
