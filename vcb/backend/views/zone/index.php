<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\db\ProductCategory */
/* @var $form ActiveForm */
$this->title = 'Danh sách Tỉnh-Thành/Quận-Huyện';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper index">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header>Danh sách Tỉnh-Thành/Quận-Huyện</h1>

                <div class="option-buttons">
                    <div class="addNew"><a href="<?= Yii::$app->urlManager->createUrl(['zone/add']) ?>"
                                           data-toggle="modal" class="btn btn-sm btn-success"><i class="en-plus3"></i>
                            Thêm</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class="well well-sm fillter">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Yii::$app->urlManager->createUrl(['zone']), 'enableAjaxValidation' => true, 'options' => ['class' => 'form-horizontal', 'role' => 'form']]); ?>
            <div class="row">
                <div class="col-md-2 ui-sortable">
                    <?php echo $form->field($model, 'time_created_from', ['template' => '{input}<i class="im-calendar s16 left-input-icon"></i>{error}{hint}'])->label(false)->textInput(array('class' => 'form-control left-icon datepicker', 'placeholder' => 'Ngày tạo: từ')) ?>
                </div>
                <div class="col-md-2 ui-sortable">
                    <?php echo $form->field($model, 'time_created_to', ['template' => '{input}<i class="im-calendar s16 left-input-icon"></i>{error}{hint}'])->label(false)->textInput(array('class' => 'form-control left-icon datepicker', 'placeholder' => 'đến')) ?>
                </div>
                <div class="col-md-2">
                    <?php echo $form->field($model, 'keyword')->label(false)->textInput(array('class' => 'form-control', 'placeholder' => 'Tên tỉnh thành/ quận huyện')) ?>
                </div>
                <div class="col-md-2">
                    <?php echo $form->field($model, 'status')->label(false)->dropDownList(array_merge(array(0 => 'Trạng thái'), $model->getStatus()), ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-2"><?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-danger']) ?>&nbsp;&nbsp;<a
                        class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl('zone') ?>">Bỏ
                        lọc</a></div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <form method="post" action="<?= Yii::$app->urlManager->createUrl(['zone/update-position']) ?>">
            <?php
            if (is_array($data) && count($data) == 0) {
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
                        <th width="35">STT</th>
                        <th>Tên</th>
                        <th width="85">Vị trí
                            <button type="submit"><i class="fa-refresh"></i></button>
                        </th>
                        <th>Trạng thái</th>
                        <th>
                            <div align="right">Thao tác</div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row) { ?>
                        <tr>
                            <th><?= $row["index"] ?></th>
                            <td><?= $row["name"] ?></td>
                            <td><input class="form-control col-sm-1 text-center" name="positions[]"
                                       value="<?= $row["position"] ?>"/>
                                <input type="hidden" name="ids[]" value="<?= $row["id"] ?>"/></td>
                            <td><span class="<?= $row["status_class"] ?>"><?= $row["status_name"] ?></span></td>
                            <td>
                                <?php if (!empty($row["operators"])) { ?>
                                    <div class="dropdown otherOptions fr">
                                        <a href="#" class="dropdown-toggle btn btn-primary btn-sm"
                                           data-toggle="dropdown" role="button" aria-expanded="false">Thao tác <span
                                                class="caret"></span></a>
                                        <ul class="dropdown-menu right" role="menu">
                                            <?php
                                            foreach ($row["operators"] as $key => $operator) :
                                                if ($operator['confirm'] == true) :
                                                    ?>
                                                    <li>
                                                        <a href="<?= Yii::$app->urlManager->createUrl(['zone/' . $key, 'id' => $row['id']]) ?>"
                                                           onclick="confirm('<?= $operator['title'] ?> tỉnh thành/ quận huyện', '<?= Yii::$app->urlManager->createUrl(['zone/' . $key, 'id' => $row['id']]) ?>');return false;"><?= $operator['title'] ?></a>
                                                    </li>
                                                <?php else: ?>
                                                    <li>
                                                        <a href="<?= Yii::$app->urlManager->createUrl(['zone/' . $key, 'id' => $row['id']]) ?>"><?= $operator['title'] ?></a>
                                                    </li>
                                                <?php
                                                endif;
                                            endforeach;
                                            ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
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
                        Bạn có chắc chắn muốn <strong class="title"><span id="orgLName"></span></strong> không?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#" onclick="document.location.href=this.href;">Xác
                            nhận</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script language="javascript" type="text/javascript">
    function confirm(title, url) {
        $('#confirm-dialog .title').html(title);
        $('#confirm-dialog .btn-accept').attr('href', url);
        $('#confirm-dialog').modal('show');
    }
</script>