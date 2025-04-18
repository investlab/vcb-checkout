<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\utils\Translate;

/* @var $this yii\web\View */

/* @var $form ActiveForm */
$this->title = Translate::get('Danh sách quyền hệ thống');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-wrapper index">
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header><?= Translate::get('Danh sách quyền hệ thống') ?></h1>
                <div class="option-buttons">
                    <div class="addNew"><a href="<?= Yii::$app->urlManager->createUrl(['right/add'])?>" data-toggle="modal" class="btn btn-sm btn-success">
<i class="en-plus3"></i> <?= Translate::get('Thêm') ?></a></div>
                </div>
            </div>
        </div>
    </div>
    <div class=outlet>
        <div class="well well-sm fillter">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Yii::$app->urlManager->createUrl(['right']), 'enableAjaxValidation' => true, 'options' => ['class' => 'form-horizontal', 'role' => 'form']]); ?>
            <div class="row group-input-search">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                    <?php echo $form->field($model, 'keyword')->label(false)->textInput(array('class' => 'form-control', 'placeholder' => Translate::get('Tên/Mã quyền'))) ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                    <?php echo $form->field($model, 'status')->label(false)->dropDownList(array_merge(array(0 => Translate::get('Trạng thái')), $model->getStatus()), ['class' => 'form-control']) ?>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center"><?= Html::submitButton(Translate::get('Tìm kiếm'), ['class' => 'btn btn-danger']) ?>&nbsp;&nbsp;<a class="btn btn-default" href="<?= Yii::$app->urlManager->createUrl('right') ?>"><?= Translate::get('Bỏ lọc') ?></a></div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="table-responsive">
        <form method="post" action="<?= Yii::$app->urlManager->createUrl(['right/update-position'])?>">
            <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th width="35" class="text-center"><?= Translate::get('STT')?></th>
                        <th><?= Translate::get('Tên quyền') ?></th>
                        <th><?= Translate::get('Mã quyền') ?></th>
                        <th><?= Translate::get('Tiêu đề') ?></th>
                        <th width="85"><?= Translate::get('Vị trí') ?> <button type="submit"><i class="fa-refresh"></i></button></th>
                        <th><?= Translate::get('Trạng thái') ?></th>
                        <th><div align="right"><?= Translate::get('Thao tác') ?></div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row) { ?>
                    <tr>
                        <th class="text-center"><?= $row["index"] ?></th>
                        <td><?= Translate::get($row["name"]) ?></td>
                        <td><?= $row['code']?></td>
                        <td><?= Translate::get($row["title"]) ?></td>
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
                                        <li><a href="<?= Yii::$app->urlManager->createUrl(['right/' . $key, 'id' => $row['id']]) ?>" onclick="
                                                confirm('<?= $operator['title'] ?> <?=Translate::get('quyền hệ thống')?>', '<?= Yii::$app->urlManager->createUrl(['right/' . $key, 'id' => $row['id']]) ?>');return false;"><?= $operator['title'] ?></a></li>
                                    <?php else:?>
                                        <li><a href="<?= Yii::$app->urlManager->createUrl(['right/' . $key, 'id' => $row['id']]) ?>"><?= $operator['title'] ?></a></li>
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
                        <?= Translate::get('Bạn có chắc chắn muốn')?> <strong class="title"><span id="orgLName"></span></strong> không?
                    </div>
                    <div class="form-group" align="center">
                        <a class="btn btn-primary btn-accept" href="#" onclick="document.location.href=this.href;"><?= Translate::get('Xác nhận') ?></a>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= Translate::get('Bỏ qua') ?></button>
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