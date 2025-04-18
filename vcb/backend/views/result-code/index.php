<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Danh sách ngân hàng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
<div class=row>
    <!-- Start .row -->
    <!-- Start .page-header -->
    <div class="col-lg-12 heading">
        <div id="page-heading" class="heading-fixed">
            <!-- InstanceBeginEditable name="EditRegion1" -->
            <h1 class=page-header><?= Translate::get('Danh sách mã trả về') ?></h1>
            <!-- Start .option-buttons -->
            <div class="option-buttons">
                <div class="addNew">
				<a href="<?=Yii::$app->urlManager->createUrl(['result-code/add'])?>" class="btn btn-sm btn-success">
				<i class="en-plus3"></i> <?= Translate::get('Thêm') ?></a></div>
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
            <div class="col-md-2 ui-sortable">
                <input type="text" class="form-control" placeholder="<?= Translate::get('Mã trả về') ?>"
                       name="code"
                       value="<?= (isset($search) && $search != null) ? Html::encode($search->code) : '' ?>">
            </div>
            <div class="col-md-3">
                <button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>
                &nbsp;
                <a href="<?= Yii::$app->urlManager->createUrl('result-code/index') ?>" class="btn btn-default">
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
                    <strong><?= Translate::get('Thông báo') ?></strong> <?= Translate::get('Không tìm thấy kết quả nào phù hợp') ?>.
                </div>
            <?php } ?>
            <div class="table-responsive">
                <table class="table table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th width="35">ID</th>
                        <th><?= Translate::get('Mã trả về') ?></th>
                        <th><?= Translate::get('Mô tả') ?></th>
                        <th>
                            <div align="right"><?= Translate::get('Thao tác') ?></div>
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
                                    <?= $data['id'] ?>
                                </th>
                                <td>
                                    <?= $data->code ?>
                                </td>
                                <td>
                                    <?= $data->description ?>
                                </td>
                                <td>
                                    
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
