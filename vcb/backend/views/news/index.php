<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 14/03/2018
 * Time: 10:01 SA
 */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách tin tức';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>Danh sách tin tức</h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a href="<?= $create_link ?>" data-toggle="modal" class="btn btn-sm btn-success">
                            <i class="en-plus3"></i> Thêm
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
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" class="form-control" placeholder="Tiêu đề"
                               name="title"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->title) : '' ?>">
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
                        <a href="<?= Yii::$app->urlManager->createUrl('news/index') ?>"
                           class="btn btn-default">
                            Bỏ lọc
                        </a>
                    </div>
                </div>

            </form>
        </div>
        <div class=row>
            <div class=col-md-12>
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        if (is_array($page->data) && count($page->data) == 0) {
                            ?>

                            <div class="alert alert-danger fade in">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>Thông báo</strong> Không tìm thấy kết quả nào phù hợp.
                            </div>
                        <?php } ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th width="35">ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Ảnh</th>
                                    <th>Mô tả</th>
                                    <th>Thời gian đăng</th>
                                    <th>Trạng thái</th>
                                    <th>
                                        <div align="right">Thao tác</div>
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
                                                <?= @$data['id'] ?>
                                            </th>
                                            <td>
                                                <?= @$data['title'] ?>
                                            </td>
                                            <td>
                                                <img
                                                    src="<?= $data['image'] != null ? $image_url . $data['image'] : $image_url . 'no-image.jpg' ?>"
                                                    width="80" height="80">
                                            </td>
                                            <td>
                                                <?= @$data['description'] ?>
                                            </td>
                                            <td>
                                                <?= $data['time_publish'] > 0 ? date('d-m-Y', $data['time_publish']) : '' ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="label <?= $data['status_class'] ?>"><?= $data['status_name'] ?></span>
                                            </td>
                                            <td>
                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button" aria-expanded="false">Thao tác <span
                                                            class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <?php if ($data['check_lock'] != true) { ?>
                                                            <li>
                                                                <a title="Sửa"
                                                                   href="<?= $update_link . '?id=' . $data['id'] ?>">
                                                                    Sửa
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a onclick="news.modalLock('<?= $data['id'] ?>')"
                                                                   style="cursor: pointer ">Khóa</a>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <a onclick="news.modalUnLock('<?= $data['id'] ?>')"
                                                                   style="cursor: pointer ">Mở Khóa</a>
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
                                    'nextPageLabel' => 'Tiếp',
                                    'prevPageLabel' => 'Sau',
                                    'maxButtonCount' => 5
                                ]); ?>
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
                            <h4 class="modal-title">Khóa tin tức</h4>
                        </div>
                        <div class="modal-body">
                            <!-- content in modal, tinyMCE 4 texarea -->
                            <div class="form-horizontal" role="form">
                                <form id="lock-news-form" method="post"
                                      action="<?= Yii::$app->urlManager->createUrl('news/lock') ?>">
                                    <div class="alert alert-warning fade in" align="center">
                                        Bạn có chắc chắn muốn <strong>Khóa tin tức này </strong>
                                        không?
                                        <input name="id" type="hidden">
                                    </div>
                                </form>
                                <!-- End .form-group  -->

                                <div class="form-group" align="center">
                                    <a class="btn btn-primary" href="javascript:news.submitLock();">Xác
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

            <!-- Modal Mở khóa -->
            <div class="modal fade" id="Unlock" tabindex=-1 role=dialog aria-hidden=true>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Mở khóa tin tức</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-horizontal" role="form">
                                <form id="unlock-news-form" method="post"
                                      action="<?= Yii::$app->urlManager->createUrl('news/active') ?>">
                                    <!-- content in modal, tinyMCE 4 texarea -->

                                    <div class="alert alert-warning fade in" align="center">
                                        Bạn có chắc chắn muốn <strong>Mở khóa tin tức</strong> này?
                                        <input name="id" type="hidden">
                                    </div>
                                    <!-- End .form-group  -->
                                    <div class="form-group" align="center">
                                        <a class="btn btn-primary" href="javascript:news.submitUnLock();">Xác
                                            nhận</a>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua
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