<?php

use common\components\utils\Converts;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Merchant;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Merchant';
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    Merchant::STATUS_ACTIVE => 'bg bg-default',
    Merchant::STATUS_LOCK => 'bg bg-danger',
);
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header>Merchant</h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
                    <?php if (!empty($check_all_operators)) { ?>
                        <?php foreach ($check_all_operators as $key => $operator) {
                            $router = isset($operator['router']) ? $operator['router'] : 'merchant/' . $key;
                            ?>
                            <?php if ($key == 'add') { ?>
                                <a href="<?= Yii::$app->urlManager->createUrl('merchant/add') ?>"
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
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Tên merchant') ?>"
                       name="name"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->name) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Merchant ID') ?>"
                       name="merchant_code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->merchant_code) : '' ?>">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="branch_id">
                    <?php foreach ($branchs as $key => $branch) {?>
                        <option value="<?= $key?>" <?= (isset($search) && $search->branch_id == $key)? 'selected': ''?>><?= $branch?></option>
                    <?php }?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="status">
                    <option value="0"><?= Translate::get('Chọn trạng thái merchant') ?></option>
                    <?php
                    foreach ($status_arr as $key => $data) {
                        ?>
                        <option value="<?= $key ?>" <?= (isset($search) && $search->status == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($data) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="active3D">
                    <option value="" selected = 'true'><?= Translate::get('Chọn trạng thái Token 3D-Secure') ?></option>
                    <?php
                    foreach ($active3D_arr as $key => $data) {
                        ?>
                        <option value="<?= $key ?>" <?= (isset($search->active3D) && $search->active3D != '' && $search->active3D == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($data) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                <select class="form-control" name="payment_flow">
                    <option value="" selected = 'true'><?= Translate::get('Chọn luồng thanh toán') ?></option>
                    <?php
                    foreach ($payment_arr as $key => $data) {
                        ?>
                        <option value="<?= $key ?>" <?= (isset($search->payment_flow) && $search->payment_flow != '' && $search->payment_flow == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($data) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 no-padding-left group-btn-search mobile-flex-middle-center">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                <a href="<?= Yii::$app->urlManager->createUrl('merchant/index') ?>"
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
                merchant
                &nbsp;|&nbsp;
                <?= Translate::get('Kích hoạt') ?> <strong
                    class="text-danger"><?= (isset($page->count_active) ? $page->count_active : '0') ?></strong>
                &nbsp;|&nbsp;
                <?= Translate::get('Đang khóa') ?> <strong
                    class="text-danger"><?= (isset($page->count_lock) ? $page->count_lock : '0') ?></strong>
            </div>
            <br><br>
            <div class="col-md-12" style="margin-left:-15px">
                <?= Translate::get('Tổng số dư khả dụng') ?> : <strong
                    class="text-danger"><?= ObjInput::makeCurrency(@$page->total_balance) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
                &nbsp;|&nbsp;
                <?= Translate::get('Tổng số dư chờ chuyển') ?>: <strong
                    class="text-danger"><?= ObjInput::makeCurrency(@$page->total_balance_pending) ?></strong> <?= $GLOBALS['CURRENCY']['VND'] ?>
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
                <div class="table-responsive">
                    <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th width="35">ID</th>
                            <th>Merchant</th>
                            <th><?= Translate::get('Tài khoản báo có')?></th>
                            <th><?= Translate::get('Số dư') ?></th>
                            <th>Logo</th>
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
                                        <?= @$data['id']?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= Translate::get('Tên') ?> :<strong><?= @$data['name'] ?></strong>
                                            <?php if (@$data['merchant_code'] != null) { ?>
                                                <hr><?= Translate::get('Merchant ID') ?> :
                                                <strong><?= @$data['merchant_code'] ?></strong>
                                            <?php
                                            }
                                            if (@$data['website'] != null) { ?>
                                                <hr>Website: <strong>
                                                <a target="_blank" style="cursor: pointer"><?= Converts::convertString(@$data['website']) ?></a></strong>
                                            <?php
                                            }
                                            if (@$data['mobile_notification'] != null) { ?>
                                                <hr><?= Translate::get('Số điện thoại') ?> :
                                                <strong><?= @$data['mobile_notification'] ?></strong>
                                            <?php
                                            }
                                            if (@$data['email_notification'] != null) { ?>
                                                <hr>Email :
                                                <strong><?= @$data['email_notification'] ?></strong>
                                            <?php }
                                            if (@$data['active3D'] != null){?>
                                                <hr>Token 3D-Secure :
                                                <strong><?= (@$data['active3D'] == 0) ? 'Bật' : 'Tắt' ?></strong>
                                            <?php }
                                            if (@$data['payment_flow'] != null){?>
                                                <hr>Luồng thanh toán:
                                                <strong><?= (@$data['payment_flow'] == 0) ? '3Ds' : '3Ds2' ?></strong>
                                            <?php }?>
                                        </div>
                                       
                                    </td>
                                    <td>
                                        <?php if (@$data['credit_account'] != null) { ?>
                                            <?= Translate::get('Mã chi nhánh')?>:
                                            <strong><?= @$data['credit_account']['branch_code'] ?></strong>
                                            <hr>
                                            <?= Translate::get('Số tk báo có')?>:
                                            <strong><?= @$data['credit_account']['account_number'] ?></strong>
                                        <?php }?>
                                    </td>
                                    <td class="text-right">
                                         <?= Translate::get('Khả dụng') ?> : <strong class="text-primary"><?= ObjInput::makeCurrency(@$data['account']['balance'])?></strong> <?= $GLOBALS['CURRENCY']['VND']?>
                                        <hr>
                                        <?= Translate::get('Chờ chuyển') ?>: <strong class="text-danger"><?= ObjInput::makeCurrency(@$data['account']['balance_pending'])?></strong> <?= $GLOBALS['CURRENCY']['VND']?>
                                    </td>
                                    <td>
                                        <img
                                            src="<?= @$data['logo'] != null ? $logo_url . @$data['logo'] : $logo_url . 'no-image.jpg' ?>" height="80">
                                    </td>

                                    <td class="text-right">
                                        <?php if ($data['status'] == Merchant::STATUS_ACTIVE) { ?>
                                            <span class="label label-success"><?= Translate::get('Kích hoạt') ?></span>
                                        <?php } elseif ($data['status'] == Merchant::STATUS_LOCK) { ?>
                                            <span class="label label-danger"><?= Translate::get('Đang khóa') ?></span>
                                        <?php } ?>
                                       
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
                                    <td>
                                        <?php if (!empty($data["operators"])) { ?>
                                            <div class="dropdown otherOptions fr">
                                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                   data-toggle="dropdown"
                                                   role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                        class="caret"></span></a>
                                                <ul class="dropdown-menu right" role="menu">
                                                    <?php foreach ($data["operators"] as $key => $operator) {
                                                        $router = isset($operator['router']) ? $operator['router'] : 'merchant/' . $key;
                                                        $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                        ?>
                                                        <?php if ($operator['confirm'] == true) { ?>
                                                            <li>
                                                                <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                   onclick="confirm('<?= $operator['title'] ?>', '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                       return false;"><?= Translate::get($operator['title']) ?></a>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <a <?php if ($key != 'view-update'): ?>class="ajax-link"<?php endif; ?>
                                                                   href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                                    <?= Translate::get($operator['title']) ?>
                                                                </a>
                                                            </li>
                                                        <?php
                                                        }
                                                    } ?>
                                                    <li>
                                                        <a title="<?= Translate::get('Cấu hình tài khoản báo có') ?>" href="#ConfigCreditAccount" data-toggle="modal"
                                                           onclick="merchant.viewEdit(
                                                                   '<?= $data['id'] ?>',
                                                                   '<?= Yii::$app->urlManager->createUrl('merchant/credit-account') ?>'
                                                                   );">
                                                            <?= Translate::get('Cấu hình tài khoản báo có') ?>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a href="<?= Yii::$app->urlManager->createUrl(['user-login/index', 'merchant_id' => $data['id']]) ?>">
                                                            <?= Translate::get('Danh sách tài khoản') ?>
                                                        </a>
                                                    </li>
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

<!--Cấu hình tài khoản báo có-->
<div class="modal fade" id="ConfigCreditAccount" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?= Translate::get('Cấu hình tài khoản báo có') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <?php
                $form = ActiveForm::begin(['id' => 'config-credit-account-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('merchant/credit-account'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-sm-3 control-label"><?= Translate::get('Số tài khoản') ?></label>

                        <div class="col-sm-9">
                            <?= $form->field($credit_account, 'account_number')->label(false)
                                ->textInput(array('class' => 'form-control', 'placeholder' => 'Số tài khoản nhận báo cáo')) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-sm-3 control-label"><?= Translate::get('Mã chi nhánh') ?></label>

                        <div class="col-sm-9">
                            <?= $form->field($credit_account, 'branch_code')->label(false)
                                ->textInput(array('class' => 'form-control', 'placeholder' => 'Mã chi nhánh quản lý Merchant', 'maxlength' => 6)) ?>
                        </div>
                    </div>

                    <div class="col-sm-offset-3 col-sm-9 ui-sortable">
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
                    </div>

                    <!-- End .form-group  -->
                </div>
                <?= $form->field($credit_account, 'merchant_id')->label(false)
                    ->hiddenInput() ?>
                <?php ActiveForm::end() ?>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script language="javascript" type="text/javascript">
    <?php echo Yii::$app->view->renderFile('@app/web/js/ajax.js', array()); ?>
    function confirm(title, url) {
        $('#confirm-dialog .title').html(title);
        $('#confirm-dialog').modal('show');
        $('#confirm-dialog .btn-accept').click(function () {
            document.location.href = url;
        });
    }

</script>

