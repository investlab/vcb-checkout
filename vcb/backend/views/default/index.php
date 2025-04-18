<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Translate::get('Trang chủ');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class="page-header title-page-header"><?= Translate::get('Xin chào') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew"></div>
                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>

    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->
        <div class="row no-margin">
              <p><?= Translate::get('Chúc bạn một ngày làm việc hiệu quả') ?> !</p>
            </div>
        </div>
</div>

