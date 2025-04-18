<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách người dùng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách người dùng') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew"><a href="<?= Yii::$app->urlManager->createUrl('user/create') ?>"
                                           class="btn btn-sm btn-success"><i
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
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Họ và tên') ?>"
                               id="fullname" name="fullname"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->fullname) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Tên đăng nhập') ?>"
                               id="username" name="username"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->username) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="Email"
                               id="email" name="email"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->email) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="user_group">
                            <?php
                            foreach ($user_group as $key => $ug) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->user_group == $key) ? "selected='true'" : '' ?> >
                                    <?= Translate::get($ug) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="status">
                            <option value="0"><?= Translate::get('Trạng thái') ?></option>
                            <?php
                            foreach ($user_status as $key => $us) {
                                ?>
                                <option
                                    value="<?= $key ?>" <?= (isset($search) && $search->status == $key) ? "selected='true'" : '' ?> >
                                    <?= Translate::get($us) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Lọc') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('user/index') ?>"
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
                            class="text-danger"><?= $page->pagination->totalCount; ?></strong> <?= Translate::get('Quản trị') ?>
                        &nbsp;|&nbsp;
                        <strong
                            class="text-danger"><?= (isset($page->totalLock) ? $page->totalLock : '0') ?></strong>
                        <?= Translate::get('Bị khóa') ?>
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
                        <div class="table table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= Translate::get('Thông tin') ?></th>
                                    <th><?= Translate::get('Tên đăng nhập') ?></th>
                                    <th><?= Translate::get('Nhóm quyền') ?></th>
                                    <th><?= Translate::get('Trạng thái') ?></th>
                                    <th>
                                        <div align="center"><?= Translate::get('Thao tác') ?></div>
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
                                                <?= @$data['id'] ?>
                                            </td>
                                            <td class="col-sm-3">

                                                <?= Translate::get('Họ và tên') ?> : <strong><?= @$data['fullname'] ?></strong>
                                                <hr>Email : <strong><?= @$data['email'] ?></strong>
                                                <hr><?= Translate::get('SĐT') ?> : <strong><?= @$data['mobile'] ?></strong>
                                                <?php if (isset($data['branch_id']) && $data['branch_id'] != '') {?>
                                                <hr><?= Translate::get('Chi nhánh') ?> : <strong><?= @$data['branch_name'] ?></strong>
                                            <?php } ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?= @$data['username'] ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?= Translate::get(@$data['group_codes']) ?>
                                            </td>
                                            <td class="col-sm-2">
                                                <?php if (isset($data['status']) && $data['status'] == 1) { ?>
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
                                                        <li>
                                                            <a href="<?= Yii::$app->urlManager->createUrl(["user/detail", "id" => $data['id']]); ?>">
                                                                <?= Translate::get('Chi tiết') ?>
                                                            </a>
                                                        </li>
                                                        <?php if (@$data['status'] == 1) { ?>
                                                            <li>
                                                                <a href="<?= Yii::$app->urlManager->createUrl(['user/view-info-update', 'id' => $data['id']]) ?>">
                                                                    <?= Translate::get('Sửa') ?> </a>
                                                            </li>
                                                            <li>
                                                                <a onclick="user.modalLock('<?= $data['id'] ?>','<?= $data['fullname'] ?>')"
                                                                   style="cursor: pointer "><?= Translate::get('Khóa') ?></a>
                                                            </li>
                                                            <li>
                                                                <a onclick="user.modalReset(
                                                                    '<?= $data['id'] ?>',
                                                                    '<?= $data['username'] ?>'
                                                                    )"
                                                                   style="cursor: pointer "><?= Translate::get('Reset mật khẩu') ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="<?= Yii::$app->urlManager->createUrl(['user/roles', 'id' => $data['id']]) ?>">
                                                                    <?= Translate::get('Phân quyền') ?></a>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <a onclick="user.modalUnLock('<?= $data['id'] ?>','<?= $data['fullname'] ?>')"
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
<!--Khóa -->
<div class="modal fade" id="Lock" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Khóa quản trị') ?></h4>
            </div>
            <div class="modal-body">
                <!-- content in modal, tinyMCE 4 texarea -->
                <div class="form-horizontal" role="form">
                    <form id="lock-user-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('user/lock') ?>">
                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Khóa tài khoản này không') ?>?
                            <input name="id" type="hidden">
                        </div>
                    </form>
                    <!-- End .form-group  -->

                    <div class="form-group" align="center">
                        <a class="btn btn-primary" href="javascript:user.submitLock();"><?= Translate::get('Xác
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
                <h4 class="modal-title"><?= Translate::get('Mở khóa Quản trị') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <form id="unlock-user-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('user/unlock') ?>">
                        <!-- content in modal, tinyMCE 4 texarea -->

                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn Mở khóa tài khoản này') ?>?
                            <input name="id" type="hidden">
                        </div>
                        <!-- End .form-group  -->
                        <div class="form-group" align="center">
                            <a class="btn btn-primary" href="javascript:user.submitUnLock();"><?= Translate::get('Xác
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

<!-- Modal Reset -->
<div class="modal fade" id="Reset" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Reset mật khẩu') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <form id="user-reset-form" method="post"
                          action="<?= Yii::$app->urlManager->createUrl('user/reset-pass') ?>">
                        <!-- content in modal, tinyMCE 4 texarea -->

                        <div class="alert alert-warning fade in" align="center">
                            <?= Translate::get('Bạn có chắc chắn muốn reset mật khẩu của tài khoản này') ?>?
                            <input name="id" type="hidden">
                        </div>
                        <!-- End .form-group  -->
                        <div class="form-group" align="center">
                            <a class="btn btn-primary" href="javascript:user.submitReset();"><?= Translate::get('Xác
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
