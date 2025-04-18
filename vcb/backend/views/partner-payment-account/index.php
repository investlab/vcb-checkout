<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Merchant;
use common\models\db\PartnerPaymentAccount;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Tài khoản kênh thanh toán');
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    PartnerPaymentAccount::STATUS_ACTIVE => 'bg bg-default',
    PartnerPaymentAccount::STATUS_LOCK => 'bg bg-danger',
);
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Tài khoản kênh thanh toán') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <?php if (!empty($check_all_operators)) { ?>
                            <?php
                            foreach ($check_all_operators as $key => $operator) {
                                $router = isset($operator['router']) ? $operator['router'] : 'partner-payment-account/' . $key;
                                ?>
                                <?php if ($key == 'add') { ?>
                                    <a href="<?= Yii::$app->urlManager->createUrl('partner-payment-account/add') ?>"
                                       class="btn btn-sm btn-success">
                                        <i class="en-plus3"></i><?= Translate::get($operator['title']) ?>
                                    </a>
                                    <?php
                                }
                            }
                        }
                        ?>
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
                        <select class="form-control" name="merchant_id">
                            <option value="0"><?= Translate::get('Chọn Merchant') ?></option>
                            <?php
                            if (isset($merchant_search_arr) && count($merchant_search_arr) > 0) {
                                foreach ($merchant_search_arr as $key => $data) {
                                    ?>
                                    <option
                                        value="<?= $data['id'] ?>" <?= (isset($search) && $search->merchant_id == $data['id']) ? "selected='true'" : '' ?> >
                                            <?= Translate::get($data['name']) ?>
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="partner_payment_id">
                            <option value="0"><?= Translate::get('Chọn kênh thanh toán') ?></option>
                            <?php
                            if (isset($partner_payment_search_arr) && count($partner_payment_search_arr) > 0) {
                                foreach ($partner_payment_search_arr as $keyPP => $dataPP) {
                                    ?>
                                    <option
                                        value="<?= $dataPP['id'] ?>" <?= (isset($search) && $search->partner_payment_id == $dataPP['id']) ? "selected='true'" : '' ?> >
                                            <?= Translate::get($dataPP['name']) ?>
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </select>

                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Tên tài khoản') ?>"
                               name="partner_payment_account"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->partner_payment_account) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
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

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('partner-payment-account/index') ?>"
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
                            <?= Translate::get('Tài khoản') ?>
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
                                    <strong><?= Translate::get('Thông báo') ?>!!</strong> <?= Translate::get($data) ?>.<br>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th width="35">ID</th>
                                        <th>Merchant</th>
<!--                                        <th><?= Translate::get('Kênh thanh toán') ?></th>-->
    <!--                                    <th><?= Translate::get('Tên tài khoản') ?></th>-->
    <!--                                    <th><?= Translate::get('Số dư') ?>  VND</th>-->
                                        <th><?= Translate::get('Thông tin tài khoản') ?></th>
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
                                            <tr class="<?= @$array_color[$data['status']] ?>">
                                                <td>
                                                    <?= isset($data['id']) && $data['id'] != null ? $data['id'] : '' ?>
                                                </td>
                                                <td>
                                                    <?= isset($data['merchant_info']['name']) && $data['merchant_info']['name'] != null ? $data['merchant_info']['name'] : '' ?>
                                                </td>
        <!--                                                <td>
                                                <?= isset($data['partner_payment_info']['name']) && $data['partner_payment_info']['name'] != null ? $data['partner_payment_info']['name'] : '' ?>
                                                </td>-->
        <!--                                            <td>
                                                <?= isset($data['partner_payment_account']) && $data['partner_payment_account'] != null ? $data['partner_payment_account'] : '' ?>
                                                </td>-->
        <!--                                            <td>
                                                <?= isset($data['balance']) && $data['balance'] != null ? \common\components\utils\ObjInput::makeCurrency($data['balance']) : '0' ?>
                                                </td>-->
                                                <td>
                                                    <?= isset($data['partner_payment_info']['name']) && $data['partner_payment_info']['name'] != null ? $data['partner_payment_info']['name'] : '' ?>

                                                    <hr>
                                                    <strong>Account: <?= @$data['partner_payment_account'] ?> </strong>
                                                    <hr>
                                                    <strong>MID: <?=( $data['partner_payment_info']['id'] == 9 ? @$data['partner_payment_id'] : @$data['partner_merchant_id'] ) ?></strong>
                                                    <hr>
                                                </td>
                                                <td>
                                                    <?php if ($data['status'] == PartnerPaymentAccount::STATUS_ACTIVE) { ?>
                                                        <span class="label label-success"><?= Translate::get('Kích hoạt') ?></span>
                                                    <?php } elseif ($data['status'] == PartnerPaymentAccount::STATUS_LOCK) { ?>
                                                        <span class="label label-danger"><?= Translate::get('Đang khóa') ?></span>
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
                                                <td>
                                                    <?php if (!empty($data["operators"])) { ?>
                                                        <div class="dropdown otherOptions fr">
                                                            <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                               data-toggle="dropdown"
                                                               role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                                    class="caret"></span></a>
                                                            <ul class="dropdown-menu right" role="menu">
                                                                <?php
                                                                foreach ($data["operators"] as $key => $operator) {
                                                                    $router = isset($operator['router']) ? $operator['router'] : 'partner-payment-account/' . $key;
                                                                    $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                                    ?>
                                                                    <?php if ($operator['confirm'] == true) { ?>
                                                                        <li>
                                                                            <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                               onclick="confirm('<?= $operator['title'] ?>', '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                                       return false;"><?= Translate::get($operator['title']) ?></a>
                                                                        </li>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>

                                                            </ul>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="box-control">
                            <div class="pagination-router">
                                <?=
                                \yii\widgets\LinkPager::widget([
                                    'pagination' => $page->pagination,
                                    'nextPageLabel' => Translate::get('Tiếp'),
                                    'prevPageLabel' => Translate::get('Sau'),
                                    'maxButtonCount' => 5
                                ]);
                                ?>
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

