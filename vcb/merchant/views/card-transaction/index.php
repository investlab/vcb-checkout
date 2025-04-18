<?php

/**
 * Created by PhpStorm.
 * User: THU
 * Date: 6/8/2018
 * Time: 14:35
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\ObjInput;
use common\models\db\UserLogin;
use common\models\db\CheckoutOrder;
use common\components\utils\Strings;
use common\components\utils\Translate;
use common\components\utils\Utilities;

$this->title = Translate::get('Thống kê sản lượng thẻ cào');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bodyCont">
    <h1 class="titlePage"><?= Translate::get('Thống kê sản lượng thẻ cào') ?></h1>

    <p class="descrip"><?= Translate::get('Hệ thống mặc định thống kê sản lượng trong 2 tháng gần đây. Nếu bạn muốn thống kê từ các tháng trước, vui lòng liên hệ số hotline') ?>
        <strong
            class="text-danger"><?= $GLOBALS['FRONTEND_PAGE']['HOTLINE'] ?></strong> <?= Translate::get('để được hỗ trợ') ?>
        .
    </p>
    <!--begin search-->
    <form method="get">
        <div class="dropSrchbox clearfix">
            <div class="relt fix1">
                <input name="date_begin" value="<?= $date_begin ?>" class="form-control datepicker"
                       placeholder="Từ ngày" type="text">
                <i class="date"></i>
            </div>
            <div class="relt fix1">
                <input name="date_end" value="<?= $date_end ?>" class="form-control datepicker" placeholder="đến ngày"
                       type="text">
                <i class="date"></i>
            </div>
            <div class="searchbtn fl"><input value="" class="Thống kê" type="submit"><i class="search-icon"></i>
            </div>
            <div class="relt fix1">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl('card-transaction/index') ?>"
                   class="btn btn-default">
                    <?= Translate::get('Bỏ lọc') ?>
                </a>
            </div>

        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover" cellspacing="0" cellpadding="0" border="0">
            <thead>
            <tr class="active">
                <th><?= Translate::get('Loại thẻ') ?></th>
                <th>
                    <div align="right"><?= Translate::get('Số lượng thẻ đúng') ?></div>
                </th>
                <th>
                    <div align="right"><?= Translate::get('Sản lượng') ?></div>
                </th>
                <th>
                    <div align="right"><?= Translate::get('Phí giao dịch') ?></div>
                </th>
                <th>
                    <div align="right"><?= Translate::get('Thực nhận') ?></div>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            $sum_total_card = 0;
            $sum_total_price = 0;
            $sum_total_amount = 0;
            foreach ($data as $row) :
                $sum_total_card += $row['total_card'];
                $sum_total_price += $row['total_price'];
                $sum_total_amount += $row['total_amount'];
                ?>
                <tr>
                    <td>
                        <img style="width:50px" src="images/card/<?= strtolower($row['code']) ?>.png"
                             title="<?= Translate::get($row['name']) ?>">
                    </td>
                    <td align="right"><?= $row['total_card'] ?></td>
                    <td align="right"><?= ObjInput::makeCurrency($row['total_price']) ?></td>
                    <td align="right"><?= ObjInput::makeCurrency($row['total_price'] - $row['total_amount']) ?></td>
                    <td align="right"><?= ObjInput::makeCurrency($row['total_amount']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="active">
                <td><?= Translate::get('TỔNG CỘNG') ?></td>
                <td align="right"><strong class="text-danger"><?= $sum_total_card ?></strong></td>
                <td align="right"><strong class="text-danger"><?= ObjInput::makeCurrency($sum_total_price) ?></strong>
                </td>
                <td align="right"><strong
                        class="text-danger"><?= ObjInput::makeCurrency($sum_total_price - $sum_total_amount) ?></strong>
                </td>
                <td align="right"><strong class="text-danger"><?= ObjInput::makeCurrency($sum_total_amount) ?></strong>
                </td>
            </tr>
            <tr>
                <td colspan="5"></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
