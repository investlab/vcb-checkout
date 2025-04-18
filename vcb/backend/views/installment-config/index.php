<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\Merchant;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Cấu hình trả góp Merchant';
$this->params['breadcrumbs'][] = $this->title;
$array_color = array(
    ACTIVE_STATUS => 'bg bg-default',
    LOCK_STATUS => 'bg bg-danger',
);
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header>Cấu hình trả góp Merchant</h1>
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
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã Merchant') ?>"
                               name="merchant_id"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->merchant_id) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <input type="text" class="form-control" placeholder="<?= Translate::get('Mã Ngân Hàng') ?>"
                               name="card_accept"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->card_accept) : '' ?>">
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('installment-config/index') ?>"
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
                        cấu hình trả góp merchant
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
                                    <th width="35" class="text-center">ID</th>
                                    <th class="text-center">Merchant ID</th>
                                    <th class="text-center"><?= Translate::get('Thẻ hỗ trợ') ?></th>
                                    <th class="text-center"><?= Translate::get('Kỳ hạn') ?></th>
                                    <th class="text-center"><?= Translate::get('Thao tác') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (is_array($page->data) && count($page->data) > 0 && $page->errors == null) {
                                    foreach ($page->data as $key => $data) {
                                        ?>
                                        <tr class="<?=@$array_color[$data['status']]?>">
                                            <td class="text-center">
                                                <?= @$data['id']?>
                                            </td>
                                            <td class="text-center">
                                                <?= @$data['merchant_id'] ?>
                                            </td>
                                            <td>
                                                <div class="fix-height-scroll">
                                                    <?php if (is_array($data['card_accept'])){ ?>
                                                        <?php foreach ($data['card_accept'] as $key => $card) : ?>
                                                            <p><strong><?= $key ?></strong><?= ' áp dụng thẻ: ' . (empty($card)? '': implode(', ', $card)) ?></p>
                                                        <?php endforeach; ?>
                                                    <?php }?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fix-height-scroll">
                                                    <?php if (is_array($data['card_accept'])){ ?>

                                                        <?php foreach ($data['cycle_accept'] as $key => $cycle) : ?>
                                                            <p><strong><?= $key ?></strong> <?= Translate::get('áp dụng')?>
                                                                <?php foreach ($page->period as $key_period => $period) : ?>
                                                                    <span class="btn <?= !empty($cycle)? (in_array($period, $cycle)? 'btn-success': 'btn-danger'): 'btn-danger' ?>" style="padding: 3px 12px;"><?= $period ?></span>
                                                                <?php endforeach; ?>
                                                            </p>
                                                        <?php endforeach; ?>
                                                    <?php }?>
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
                                                                $router = isset($operator['router']) ? $operator['router'] : 'installment-config/' . $key;
                                                                $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                                ?>
                                                                <?php if ($operator['confirm'] == true) { ?>
                                                                    <?php if ($key == 'view-installment') {$router = 'merchant/' . $key;} ?>
                                                                    <li>
                                                                        <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['merchant_id']]) ?>"
                                                                           onclick="confirm('<?= $operator['title'] ?>', '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['merchant_id']]) ?>');
                                                                               return false;"><?= Translate::get($operator['title']) ?></a>
                                                                    </li>
                                                                <?php } else { ?>
                                                                    <li>
                                                                        <a <?php if ($key != 'view-update'): ?>class="ajax-link"<?php endif; ?>
                                                                           href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['merchant_id']]) ?>">
                                                                            <?= Translate::get($operator['title']) ?>
                                                                        </a>
                                                                    </li>
                                                                    <?php
                                                                }
                                                            } ?>
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

