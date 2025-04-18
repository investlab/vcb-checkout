<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;
use common\components\utils\Converts;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách ngân hàng');
$this->params['breadcrumbs'][] = $this->title;

$secure_types = [
    '' => 'Chọn loại thẻ visa/master',
    '1' => '2D',
    '2' => '3D',
];

?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách thẻ liên kết') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">

                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <div class="well well-sm fillter">
            <form class="form-horizontal" role=form>
                <div class="row group-input-search">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get("Email khách hàng") ?>"
                               name="customer_email"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->customer_email) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <select class="form-control" name="merchant_id" id="merchant_id">
                            <?php
                            foreach ($merchants as $key => $merchant) {
                                ?>
                                <option value="<?= $key ?>" <?= (isset($search) && $search->merchant_id == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($merchant) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="partner_payment_id" id="partner_payment_id">
                            <?php
                            foreach ($partner_payments as $key => $partner_payment) {
                                ?>
                                <option value="<?= $key ?>" <?= (isset($search) && $search->partner_payment_id == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($partner_payment) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="status">
                            <option value="">Chọn trạng thái</option>
                            <?php
                            foreach ($arr_status as $key => $status) {
                                ?>
                                <option value="<?= $key ?>" <?= (isset($search) && $search->status == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($status['name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="card_type">
                            <?php
                            foreach ($card_types as $key => $card_type) {
                                ?>
                                <option value="<?= $key ?>" <?= (isset($search) && $search->card_type == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($card_type) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="secure_type">
                            <?php
                            foreach ($secure_types as $key => $secure_type) {
                                ?>
                                <option value="<?= $key ?>" <?= (isset($search) && $search->secure_type == $key) ? "selected='true'" : '' ?> >  <?= Translate::get($secure_type) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('link-card/index') ?>" class="btn btn-default">
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
                        <?= Translate::get('Thẻ liên kết') ?>
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
                                    <th><?= Translate::get('Merchant') ?></th>
                                    <th><?= Translate::get('Thông tin thẻ') ?></th>
                                    <th><?= Translate::get('Token') ?></th>
                                    <th><?= Translate::get('Nhà cung cấp') ?></th>
                                    <th><?= Translate::get('Số tiền') ?></th>
                                    <th><?= Translate::get('Trạng thái') ?></th>
                                    <th><?= Translate::get('Thao tác') ?></th>
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
                                                <strong><?= (!empty($data['merchant_id']))? $merchants[$data['merchant_id']]: '' ?></strong>
                                                <?php if (!empty($data['customer_email'])) { ?>
                                                <hr>
                                                Email: <strong><?= $data['customer_email']?></strong>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?= Translate::get('Chủ thẻ')?>: <strong><?= (!empty($data['card_holder']))? $data['card_holder']: '' ?></strong>
                                                <?php if (!empty($data['card_number_mask'])) {?>
                                                    <hr>
                                                    <?= Translate::get('Số thẻ đã mask')?>: <strong><?= $data['card_number_mask'] ?></strong>
                                                <?php } if (!empty($data['card_number_md5'])) { ?>
                                                    <hr>
                                                    <?= Translate::get('Số thẻ mã hoá')?>: <strong class="text-primary"><?= Converts::convertString($data['card_number_md5']) ?></strong>
                                                <?php } if (!empty($data['card_type'])) { ?>
                                                    <hr>
                                                    <?= Translate::get('Loại thẻ')?>: <strong><?= $card_types[$data['card_type']] .' - '. $secure_types[$data['secure_type']] ?></strong>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?= Translate::get('Token cybersource')?>: <strong class="text-primary"><?= (!empty($data['token_cybersource']))? Converts::convertString($data['token_cybersource']): '' ?></strong>
                                                <?php if (!empty($data['token_merchant'])) { ?>
                                                    <hr>
                                                    <?= Translate::get('Tokent merchant')?>: <strong class="text-primary"><?= Converts::convertString($data['token_merchant']) ?></strong>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?= (!empty($data['partner_payment_id']))? $partner_payments[$data['partner_payment_id']]: '' ?>
                                            </td>
                                            <td class="text-right">
                                                <?= (!empty($data['verify_amount']))? number_format($data['verify_amount']): 0 ?>
                                            </td>
                                            <td class="text-center">
                                                <?php foreach ($arr_status as $key => $status) {
                                                    if ($data['status'] == $key) {
                                                        ?>
                                                        <span class="<?= $status['class'] ?>"><?= Translate::get($status['name']) ?></span>
                                                <?php }} ?>
                                            </td>
                                            <td>
                                                <div class="dropdown otherOptions fr">
                                                    <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                       data-toggle="dropdown"
                                                       role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span class="caret"></span></a>
                                                    <ul class="dropdown-menu right" role="menu">
                                                        <?php
                                                        foreach ($data["operators"] as $items => $operator) {
                                                            $router = isset($operator['router']) ? $operator['router'] : 'link-card/' . $items;
                                                            $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                            ?>
                                                            <?php if ($operator['confirm'] == true) { ?>
                                                                <li>
                                                                    <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                       onclick="confirm(
                                                                               '<?= $operator['title'] ?>',
                                                                               '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                               return false;"><?= Translate::get($operator['title']) ?></a>
                                                                </li>
                                                            <?php } else { ?>
                                                                <li>
                                                                    <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
                                                                        <?= Translate::get($operator['title']) ?>
                                                                    </a>
                                                                </li>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
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
                        Bạn có chắc chắn muốn <strong class="title"> </strong> cho thẻ liên kết này không?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#">Xác nhận</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#merchant_id').select2();
    <?php echo Yii::$app->view->renderFile('@app/web/js/ajax.js', array()); ?>
    function confirm(title, url) {
        $('#confirm-dialog .title').html(title);
        $('#confirm-dialog').modal('show');
        $('#confirm-dialog .btn-accept').click(function () {
            document.location.href = url;
        });
    }

</script>