<?php

use common\components\utils\Converts;
use common\components\utils\ObjInput;
use common\components\utils\Translate;

$this->title = Translate::get('Chi tiết thẻ liên kết');
$this->params['breadcrumbs'][] = $this->title;

$secure_types = [
    '' => 'Chọn loại thẻ visa/master',
    '1' => '2D',
    '2' => '3D',
];
?>
<!-- Start .content-wrapper -->
<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Chi tiết thẻ liên kết') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">
                    <div class="addNew">
                        <a class="btn btn-danger btn-sm"
                           href="<?= Yii::$app->urlManager->createUrl('link-card/index') ?>">
                            <i class="en-back"></i> <?= Translate::get('Quay lại') ?>
                        </a>
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

        <div class=row>
            <div class=col-lg-12>
                <!-- Start col-lg-12 -->
                <div class="panel panel-primary">
                    <!-- Start .panel -->
                    <div class=panel-heading>
                        <h3 class=panel-title><?= Translate::get('Thông tin chung') ?></h3>
                    </div>
                    <div class=panel-body>
                        <table class="table table-hover" width="100%">

                            <tr>
                                <th>Merchant</th>
                                <td><?= !empty($merchant_name)? $merchant_name: '' ?></td>
                                <th>Email khách hàng</th>
                                <td><?= !empty($link_card['customer_email'])? $link_card['customer_email']: '' ?></td>
                            </tr>

                            <tr>
                                <th>Token Merchant</th>
                                <td class="text-primary"><?= !empty($link_card['token_merchant'])? Converts::convertString($link_card['token_merchant']): '' ?></td>
                                <th><?= Translate::get('Tên chủ thẻ') ?></th>
                                <td><?= !empty($link_card['card_holder'])? $link_card['card_holder']: '' ?></td>
                            </tr>
                            <tr>
                                <th>Token Cybersource</th>
                                <td class="text-primary"><?= !empty($link_card['token_cybersource'])? Converts::convertString($link_card['token_cybersource']): '' ?></td>
                                <th><?= Translate::get('Số thẻ đã được mask') ?></th>
                                <td><?= !empty($link_card['card_number_mask'])? $link_card['card_number_mask']: '' ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Nhà cung cấp') ?></th>
                                <td><?= !empty($partner_payment_name)? $partner_payment_name: '' ?></td>
                                <th><?= Translate::get('Số thẻ qua md5') ?></th>
                                <td><?= !empty($link_card['card_number_mask'])? Converts::convertString($link_card['card_number_md5']): '' ?></td>
                            </tr>
                            <tr >
                                <th><?= Translate::get('Số tiền xác thực') ?></th>
                                <td><?= !empty($link_card['verify_amount'])? ObjInput::makeCurrency($link_card['verify_amount']): 0 ?> vnđ</td>
                                <th><?= Translate::get('Loại thẻ') ?></th>
                                <td><?= !empty($link_card['card_type'])? $card_types[$link_card['card_type']]: '' ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Trạng thái')?></th>
                                <td>
                                    <?php foreach ($arr_status as $key => $status) {
                                        if (!empty($link_card['status']) && $link_card['status'] == $key) {
                                            ?>
                                            <span class="<?= $status['class'] ?>"><?= Translate::get($status['name']) ?></span>
                                    <?php }} ?>
                                </td>
                                <th><?= Translate::get('Loại thẻ visa/master') ?></th>
                                <td><?= !empty($link_card['secure_type'])? $secure_types[$link_card['secure_type']]: '' ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Thời gian') ?></th>
                                <td>
                                    <div class="small">
                                        <?= Translate::get('TG tạo') ?>: <strong><?= intval($link_card['time_created']) > 0 ? date('H:i, d/m/Y', $link_card['time_created']) : '' ?></strong><br>
                                        <?= Translate::get('TG cập nhật') ?>: <strong><?= intval($link_card['time_updated']) > 0 ? date('H:i, d/m/Y', $link_card['time_updated']) : '' ?></strong><br>
                                        <?= Translate::get('TG xác thực') ?>: <strong><?= intval($link_card['time_verified']) > 0 ? date('H:i, d/m/Y', $link_card['time_verified']) : '' ?></strong><br>
                                    </div>
                                </td>
                                <th><?= Translate::get('Ngân hàng') ?></th>
                                <td><?= !empty($link_card['bank'])? $link_card['bank']: '' ?></td>
                            </tr>
                            <tr>
                                <th><?= Translate::get('Nhân viên xử lý') ?></th>
                                <td>
                                    <div class="small">
                                        <?php foreach ($arr_user_action as $key => $val) { ?>
                                            <p><?= ($key+1) .': '. $val['name'] .' - '. $val['action']?></p>
                                        <?php } ?>
                                    </div>
                                </td>
                                <th><?= Translate::get('Thông tin khác') ?></th>
                                <td><?= !empty($link_card['info'])? Converts::convertString($link_card['info']): '' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>