<?php
use common\components\utils\Translate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\db\CardType;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'View Log';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=content-wrapper>
    <div class=row>
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <h1 class=page-header>View Log</h1>
            </div>
        </div>
    </div>
    <div class=row>
        <div class=col-md-12>
            <ul class="nav nav-tabs">
                <?php foreach ($types as $key => $item): ?>
                    <li <?php if ($key == $type): ?>class="active"<?php endif; ?>>
                        <a data-toggle="tab" href="#<?= $key ?>">
                            <?= $item['name'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>    
            </ul>
            <div class="tab-content">
                <?php foreach ($types as $key => $value): ?>
                    <div id="<?= $key ?>" class="tab-pane fade in <?php if ($key == $type): ?>active<?php endif; ?>">
                        <div class=outlet>
                            <div class="well well-sm fillter">
                                <form class="form-horizontal" id="form-<?= $key ?>" action="<?= Yii::$app->urlManager->createUrl(['view-log/index']) ?>" method="get" role=form>
                                    <div class="row group-input-search">
                                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable">
                                            <input type="hidden" name="type" value="<?= $key ?>">
                                            <input type="text" class="form-control datepicker" placeholder="<?= Translate::get('Ngày tạo') ?>" name="date" value="<?= $date ?>">
                                            <i class="im-calendar s16 right-input-icon"></i>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center">
                                            <button class="btn btn-danger" type="submit" onclick="return viewLogContent('<?= $key ?>');">View Log</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class=row>
                                <div class="col-md-12" id="view-log-<?= $key ?>">
                                    <?php if ($key == $type): ?>
                                        <?php if ($date != '' && $file_path == false): ?>
                                            <div class="alert alert-danger"><?= Translate::get('Không có file log ngày') ?> <?= htmlentities($date) ?></div>
                                        <?php elseif ($date != '' && $file_path != false): ?>
                                            <div class="view-log" style="overflow-x: auto; overflow-y: scroll; border:1px solid #CCC; padding: 15px; background-color: #FFFACD; max-height: 500px;"><?= nl2br($content) ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>    
            </div>    
        </div>
    </div>
    <script type="text/javascript">
        function viewLogContent(key) {
            var $form = $('#form-' + key);
            if ($form.length) {
                $.post($form.attr('action'), $form.serialize(), function (data) {
                    if (data) {
                        var $view = $('#view-log-' + key);

                        if ($view.length) {
                            $view.html($(data).find('#view-log-' + key).html());
                        }
                    }
                });
            }
            return false;
        }
    </script>