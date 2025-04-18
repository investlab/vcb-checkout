<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\db\ProductCategory */
/* @var $form ActiveForm */
$this->title = 'Danh sách menu';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper index">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header>Danh sách menu</h1>
                <div class="option-buttons">
                    <div class="addNew"><a href="<?= Yii::$app->urlManager->createUrl(['menu/add'])?>" data-toggle="modal" class="btn btn-sm btn-success"><i class="en-plus3"></i> Thêm</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class="well well-sm fillter">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Yii::$app->urlManager->createUrl(['menu']), 'enableAjaxValidation' => true, 'options' => ['class' => 'form-horizontal', 'role' => 'form']]); ?>
            <div class="row">
                <div class="col-md-2">
                    <?php echo $form->field($model, 'keyword')->label(false)->textInput(array('class' => 'form-control', 'placeholder' => 'Tiêu đề')) ?>
                </div>
                <div class="col-md-2">
                    <?php echo $form->field($model, 'status')->label(false)->dropDownList(array_merge(array(0 => 'Trạng thái'), $model->getStatus()), ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-danger']) ?>&nbsp;&nbsp;
                    <a class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl('menu') ?>">Bỏ lọc</a>                    
                </div>
                <div class="col-md-2">
                    <a class="btn btn-primary pull-right" href="<?= Yii::$app->urlManager->createUrl('menu/make-cache') ?>">Tạo cache</a>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <form method="post" action="<?= Yii::$app->urlManager->createUrl(['menu/update-position'])?>">
            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th width="35" class="text-center">STT</th>
                        <th>Tiêu đề</th>
                        <th class="col-lg-5">Liên kết</th>
                        <th width="85">Vị trí <button type="submit"><i class="fa-refresh"></i></button></th>
                        <th>Trạng thái</th>                    
                        <th><div align="right">Thao tác</div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row) { ?>
                    <tr>
                        <th class="text-center"><?= $row["index"] ?></th>
                        <td><?= $row["title"] ?></td>
                        <td style="text-wrap: normal;"><?= $row['link']?></td>
                        <td><input class="form-control col-sm-1 text-center" name="positions[]" value="<?= $row["position"] ?>" />
                            <input type="hidden" name="ids[]" value="<?= $row["id"] ?>" /></td>
                        <td><span class="<?= $row["status_class"] ?>"><?= $row["status_name"] ?></span></td>                    
                        <td>
                            <?php if (!empty($row["operators"])) { ?>                        
                            <div class="dropdown otherOptions fr">
                                <a href="#" class="dropdown-toggle btn btn-primary btn-sm" data-toggle="dropdown" role="button" aria-expanded="false">Thao tác <span class="caret"></span></a>
                                <ul class="dropdown-menu right" role="menu">
                                    <?php 
                                    foreach ($row["operators"] as $key => $operator) :
                                        if ($operator['confirm'] == true) : 
                                    ?>
                                        <li><a href="<?= Yii::$app->urlManager->createUrl(['menu/' . $key, 'id' => $row['id']]) ?>" onclick="confirm('<?= $operator['title'] ?> quyền hệ thống', '<?= Yii::$app->urlManager->createUrl(['menu/' . $key, 'id' => $row['id']]) ?>');return false;"><?= $operator['title'] ?></a></li>
                                    <?php else:?>
                                        <li><a href="<?= Yii::$app->urlManager->createUrl(['menu/' . $key, 'id' => $row['id']]) ?>"><?= $operator['title'] ?></a></li>
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
                        <a class="btn btn-primary btn-accept" href="#" onclick="document.location.href=this.href;">Xác nhận</a>
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