<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách đầu bin sử dụng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Danh sách đầu bin sử dụng') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons no-margin-mobile">
                    <div class="addNew no-margin-mobile"><a href="#add-bin-accept" data-toggle="modal" onclick="resetFormData()" class="btn btn-sm btn-success"><i
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
                        <select class="form-control" name="card_type">
                            <?php foreach ($card_type as $key => $type) { ?>
                            <option value="<?= $key?>" <?= (isset($search) && $search != null && $search->card_type == $key) ? "selected='true'" : '' ?>>
                                <?= Translate::get($type) ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                        <input type="text" class="form-control" placeholder="<?= Translate::get("Đầu bin") ?>"
                               name="bin_code"
                               value="<?= (isset($search) && $search != null) ? Html::encode($search->bin_code) : '' ?>">
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="status">
                            <?php foreach ($status as $key => $status_name) { ?>
                                <option value="<?= $key?>" <?= (isset($search) && $search != null && $search->status == $key) ? "selected='true'" : '' ?>>
                                    <?= Translate::get($status_name) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                        <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('bin-accept/index') ?>" class="btn btn-default">
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
                            class="text-danger"><?php echo $page->count_active; ?></strong>
                        <?= Translate::get('đầu bin đang sử dụng') ?>
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
                                    <th><?= Translate::get('Đầu bin') ?></th>
                                    <th><?= Translate::get('Loại thẻ') ?></th>
                                    <th><?= Translate::get('Trạng thái') ?></th>
                                    <th><?= Translate::get('Ngày tạo') ?></th>
                                    <th><?= Translate::get('Thao tác') ?></th>
                                </tr>
                                </thead>
                                <?php
                                if (is_array($page->data) && count($page->data) > 0) {
                                    foreach ($page->data as $key => $data) {
                                        ?>
                                        <tbody>
                                        <tr>
                                            <th class="text-center">
                                                <?= $data['id'] ?>
                                            </th>
                                            <td class="text-center">
                                                <?= Translate::get($data['bin_code']) ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Translate::get($data['card_type']) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($data['status'] == 1) { ?>
                                                    <span class="label label-success"><?= Translate::get('Đang hoạt động') ?></span>
                                                <?php } elseif ($data['status'] == 2) { ?>
                                                    <span class="label label-danger"><?= Translate::get('Đã khóa') ?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-center" width="200px">
                                                <?= date('H:i d/m/Y', $data['time_created']) ?>
                                            </td>
                                            <td width="120px">
                                                <?php if (!empty($data["operators"])) { ?>
                                                    <div class="dropdown otherOptions fr">
                                                        <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                                           data-toggle="dropdown"
                                                           role="button" aria-expanded="false"><?= Translate::get('Thao tác') ?> <span
                                                                    class="caret"></span></a>
                                                        <ul class="dropdown-menu right" role="menu">
                                                            <?php foreach ($data["operators"] as $key_opera => $operator) {
                                                                $router = isset($operator['router']) ? $operator['router'] : 'bin-accept/' . $key_opera;
                                                                $id_name = isset($operator['id_name']) ? $operator['id_name'] : 'id';
                                                                ?>
                                                                <?php if ($operator['confirm'] == true) { ?>
                                                                    <li>
                                                                        <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>"
                                                                           onclick="confirm('<?= $operator['title'] ?>', '<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>');
                                                                                   return false;"><?= Translate::get($operator['title']) ?></a>
                                                                    </li>
                                                                <?php } else if($key_opera == 'view-update'){ ?>
                                                                    <li>
                                                                        <a href="#<?= $key_opera ?>" data-toggle="modal" onclick="getData('<?= $key_opera ?>', <?= $data['id'] ?>, '<?= $router ?>')">
                                                                            <?= Translate::get($operator['title']) ?>
                                                                        </a>
                                                                    </li>
                                                                <?php } else {?>
                                                                    <li>
                                                                        <a href="<?= Yii::$app->urlManager->createUrl([$router, $id_name => $data['id']]) ?>">
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
            <!-- InstanceEndEditable -->
        </div>

    </div>
</div>

<!-- Thêm đầu bin sử dụng -->
<div class="modal fade" id="add-bin-accept" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Thêm đầu bin sử dụng') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin(['id' => 'bin-accept-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('bin-accept/create-bin'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Đầu bin') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'bin_code')->label(false)
                                ->textInput(array('class' => 'form-control', 'maxlength'=>8)) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại thẻ') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'card_type')->label(false)
                                ->dropDownList($card_type) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Trạng thái') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'status')->label(false)
                                ->dropDownList($status) ?>
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

<!-- Update đầu bin sử dụng -->
<div class="modal fade" id="view-update" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?= Translate::get('Cập nhật đầu bin sử dụng') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin(['id' => 'update-bin-accept-form',
                    'enableAjaxValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('bin-accept/update-bin'),
                    'options' => ['enctype' => 'multipart/form-data']])
                ?>
                <div class="form-horizontal" role=form>

                    <!-- End .form-group  -->
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Đầu bin') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'bin_code')->label(false)
                                ->textInput(array('class' => 'form-control', 'maxlength'=>8)) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Loại thẻ') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'card_type')->label(false)
                                ->dropDownList($card_type) ?>
                        </div>
                    </div>
                    <div class=form-group>
                        <label class="col-lg-3 col-md-3 col-sm-12 control-label"><?= Translate::get('Trạng thái') ?> <span
                                    class="text-danger">*</span></label>

                        <div class="col-lg-9 col-md-9">
                            <?= $form->field($model, 'status')->label(false)
                                ->dropDownList($status) ?>
                        </div>
                    </div>
                    <div class="col-sm-offset-3 col-lg-9 col-md-9 ui-sortable">
                        <?= $form->field($model, 'id')->label(false)->hiddenInput() ?>
                        <button type="submit" class="btn btn-primary"><?= Translate::get('Cập nhật') ?></button>
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

    function getData(func, id, url) {
        resetFormData();

        if (func === 'view-update') {
            $.ajax({
                type: 'post',
                url: url,
                data: {
                    id: id
                }, success: function (res) {
                    var result = JSON.parse(res);
                    if (!result.error) {
                        $('#view-update #binacceptform-bin_code').val(result.data.bin_code);
                        $('#view-update #binacceptform-card_type').val(result.data.card_type);
                        $('#view-update #binacceptform-status').val(result.data.status);
                        $('#view-update #binacceptform-id').val(result.data.id);
                    }
                }
            });
        }
    }

    function resetFormData() {
        $('#binacceptform-bin_code').val('');
        $('#binacceptform-card_type').val('');
        $('#binacceptform-status').val('');
        $('p.help-block-error').html('');
        $('p.help-block-error').parent().removeClass('has-error');
    }

</script>