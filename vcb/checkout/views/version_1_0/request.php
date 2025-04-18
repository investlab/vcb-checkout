<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;
$merchant = \common\models\db\Merchant::findOne($checkout_order['merchant_id']);
$btn_return = '';
$btn_cancel = '';
$colpayment = 4;
$colother = 3;
$re_select_payment_method = true;
if ($merchant->exception == 'no_return') {
    $btn_return = 'none';
    $colpayment = 6;
    $colother = 4;
} elseif ($merchant->exception == 'no_cancel') {
    $btn_cancel = 'none';
    $colnumber = 6;
    $colother = 4;
}
if ((str_contains($model->payment_method_code, '-ATM-CARD')
    )
    && in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_CLICK_TO_ACCEPT_V2'])) {
    $check_atm_card = true;
} else {
    $check_atm_card = 0;
}
if (in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_CLICK_TO_ACCEPT_V2'])) {
    $enable_merchant_click_to_accept = true;
} else {
    $enable_merchant_click_to_accept = 0;
}
if ($checkout_order['customer_field'] != '') {
    $customer_field = json_decode($checkout_order['customer_field'], true);
    if (isset($customer_field['set-installment']) && $customer_field['set-installment']) {
        $re_select_payment_method = false;
    }
}


?>
<div class="panel panel-default wrapCont">
    <div class="row mdevice">
        <!--begin hoa don-->
        <!--header-->
        <?php require_once('includes/header.php') ?>
        <!--main-->
        <div class="col-span-12 brdRight">
            <?php
            $form = ActiveForm::begin(['id' => 'form-checkout', 'action' => $model->getRequestActionForm(), 'options' => ['class' => 'active']]);
            echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
            echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
            ?>
            <input type="hidden" id="enable-confirm"
                   value="<?= in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_CLICK_TO_ACCEPT_V2']) ?>">

            <?php if ($check_atm_card): ?>
                <input type="hidden" id="check-atm-card" value="<?= $check_atm_card ?>">
            <?php endif; ?>

            <input type="hidden" id="enable-merchant-confirm" value="<?= $enable_merchant_click_to_accept ?>">

            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">

                <div class="panel-group methods row" id="accordion">
                    <?php echo Yii::$app->view->renderFile('@app/views/' . Yii::$app->controller->id . '/includes/request/' . strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code) . '.php', array('model' => $model, 'checkout_order' => $checkout_order)); ?>
                </div>
                <?php if (empty($model->checkout_order['qrcode'])) { ?>
                    <div class="text-center" id="btn-payment">
                        <div class="col-sm-1">
                        </div>
                        <div class="col-sm-10 text-center text-uppercase">
                            <?php if ($check_atm_card): ?>
                                <div class="col-sm-12 col-md-<?= $colpayment ?> col-xs-12">
                                    <button class="btn" type="button"
                                            name="pay-button" id="pay-button"
                                            style="width: 100%"><?= Translate::get('TIẾP TỤC') ?></button>
                                </div>
                            <?php else: ?>
                                <div class="col-sm-12 col-md-<?= $colpayment ?> col-xs-12">
                                    <button class="btn" type="submit"
                                            name="pay-button" id="pay-button"
                                            style="width: 100%"><?= Translate::get('TIẾP TỤC') ?></button>
                                </div>
                            <?php endif; ?>
                            <?php if ($model->checkout_order['merchant_id'] != 75) { ?>

                                <div class="col-sm-12 col-md-<?= $colpayment ?> col-xs-12" id="return-btn">
                                    <?php
                                    $params_mc =
                                        [
                                            "version_1_0/index",
                                            "token_code" => $checkout_order['token_code'],
                                        ];
                                    if ($checkout_order["merchant_id"] == 19 /*FWD*/) {
                                        $params_mc['method_code'] = $model->info['method_code'];
                                    }
                                    if (!$re_select_payment_method) {
                                        $params_mc = [
                                            "version_1_0/request",
                                            "token_code" => $checkout_order['token_code'],
                                            "payment_method_code" => $model->payment_method_code
                                        ];
                                    }
                                    ?>

                                    <a href="<?= Yii::$app->urlManager->createAbsoluteUrl($params_mc, HTTP_CODE) ?>"
                                       style="display: <?= $btn_return ?>">
                                        <span class="btn" style="width: 100%"><?= Translate::get('CHỌN LẠI') ?></span>
                                    </a>

                                </div>

                            <?php } ?>

                            <div class="col-sm-12 col-md-<?= $colpayment ?> col-xs-12" id="cancel-btn">
                                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/transaction-destroy", "token_code" => $checkout_order['token_code']], HTTP_CODE) ?>"
                                   style="display: <?= $btn_cancel ?>">
                                    <span style="color: #c7254e;width: 100%"
                                          class="btn"><?= Translate::get('HỦY THANH TOÁN') ?></span>
                                </a>
                            </div>

                        </div>
                        <div class="col-sm-1"></div>
                    </div>
                <?php } else { ?>
                    <div class="text-center" id="btn-payment">
                        <div class="col-sm-5"></div>
                        <div class="col-sm-6 text-center text-uppercase">
                            <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(["version_1_0/transaction-destroy", "token_code" => $checkout_order['token_code']], HTTP_CODE) ?>"
                               style="display: <?= $btn_cancel ?>">
                                <div class="col-sm-12 col-md-<?= $colother ?> col-xs-12 btn" id="cancel-btn">
                                    <span style="color: #c7254e"><?= Translate::get('HỦY THANH TOÁN') ?></span>
                                </div>
                            </a>
                        </div>

                    </div>
                <?php } ?>
            </div>
            <div class="col-xs-12 col-sm-1 col-md-2"></div>
            <?php ActiveForm::end(); ?>
        </div>
        <!--footer-->

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <?php if ($device == 'desktop'): ?>
    <div class="modal-dialog " role="document" style="width: 50%">
        <?php else: ?>
        <div class="modal-dialog " role="document" style="width: 95%">
            <?php endif; ?>
            <div class="modal-content">
                <div class="modal-header" style="padding: 10px 30px">
                    <strong class="modal-title"
                            id="exampleModalLabel"><?= Translate::get('Xác nhận điều khoản') ?></strong>
                    <!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
                    <!--                    <span aria-hidden="true">&times;</span>-->
                    <!--                </button>-->
                </div>
                <div class="modal-body" style="padding: 15px 30px">
                    <?php /**  'Visa is an <strong> IMPORTANT PROCEDURE IN THE IMMIGRATION PROCESS</strong>, you need to carefully check the application information before submitting and note the following regulations:<br>
                     * <strong>Notice:</strong> Registration code will be sent via your registered email, therefore, please kindly provide the correct email, and take full responsibility for the provided information. <br>
                     * 1. According to regulations, your e-Visa result will <strong>NOT BE PROVIDED VIA EMAIL</strong>. To check your e-Visa result, follow these steps:<br>
                     * • Step 1: Visit the page:  <a class="link-point" href="https://evisa.xuatnhapcanh.gov.vn/en_US/tra-cuu-ho-so">
                     * https://evisa.xuatnhapcanh.gov.vn/en_US/tra-cuu-ho-so
                     * </a> <br>
                     * • Step 2: Provide the required information, including registration code, email and date of birth to check.<br>
                     * • Step 3: Download, print out your e-Visa and bring it along your entry. <br>
                     * 2. According to <strong>Article 5 at Resolution 30/2016/QH14</strong> of the Socialist Republic of Vietnam, the e-Visa fee once submitted is <strong>NON-REFUNDABLE BY ANY REASONS</strong> as the fee is for processing of the application and is <strong>NOT DEPENDENT ON EITHER GRANTING OR REJECTION</strong> of visa. <br>
                     *
                     * 3. The update process of e-Visa fee payment status <strong>MAY TAKE UP TO 2 HOURS</strong> due to technical reasons/network delays. You need to check your <strong>APPLICATION RESULT </strong> and <strong> PAYMENT RESULT </strong>(according to section 1) before RE-APPLYING. If you submit and pay for THE SECOND APPLICATION, which is the same as the previous application, <strong>THE IMMIGRATION DEPARTMENT WILL PRESUME THAT THESE ARE TWO DIFFERENT APPLICATIONS</strong> and that the previous fee will <strong>NOT BE REFUNDED</strong>. If both applications are granted a Visa, they are equally effective. <br>
                     *
                     * 4. If your information is not sufficient and valid, you will be requested to provide further information or you can be rejected based on the policy of the Vietnam Immigration Department. When you have to provide the information again, the processing time will be started when you resubmit the VALID information. In this case, take note of the time of entry. <br>
                     *
                     * 5. The e-Visa is normally processed within 03 working days <span class="text-danger">(*)</span>  after the e-Visa fee has been paid with sufficient and valid information. <br>
                     *
                     * 6. In case of the application to be further verified by <strong>The Ministry of Public Security</strong>, you will receive the result from 03 to 15 working days <span class="text-danger">(*)</span> <br>
                     *
                     * 7. For e-Visa support, please check for more information at <a class="link-point"
                     * href="https://evisa.xuatnhapcanh.gov.vn/en_US/web/guest/faq">https://evisa.xuatnhapcanh.gov.vn/en_US/web/guest/faq</a>
                     * or directly contact Vietnam Immigration Department at: <a class="link-point" href="https://evisa.xuatnhapcanh.gov.vn/en_US//web/guest/lien-he">https://evisa.xuatnhapcanh.gov.vn/en_US//web/guest/lien-he</a>  <br>
                     *
                     * <span class="text-danger">(*)</span> The working days exclude the weekend and national public holidays.
                     * ' **/ ?>

                    <?php if ($checkout_order["merchant_id"] == 91) { //  live: 91 ?>
                        <?= Translate::get(
                            'Thị thực là Thủ tục quan trọng trong quá trình xuất nhập cảnh, do đó Quý khách cần kiểm tra thông tin cẩn thận trước khi nộp và lưu ý các quy định như sau: <br>
<strong>Lưu ý</strong>: Mã hồ sơ điện tử sẽ được gửi qua email mà Quý khách đăng ký. Do đó, vui lòng cung cấp email chính xác và hoàn toàn chịu trách nhiệm về thông tin đã cung cấp. <br>
1, Theo quy định, kết quả thị thực điện tử sẽ <strong>không được gửi qua email</strong>. Để biết kết quả này, Quý khách thực hiện theo các bước sau: <br>
- B1: Quý khách truy cập vào trang: <a href="https://evisa.gov.vn/e-visa/search" class="link-point" target="_blank">https://evisa.gov.vn/e-visa/search</a> <br>
- B2: Quý khách cần nhập các thông tin sau: mã hồ sơ điện tử , email, và ngày tháng năm sinh theo hướng dẫn để kiểm tra kết quả. <br>
- B3: Tải kết quả, in thị thực điện tử và trình ra khi nhập cảnh. <br> <br>
2, Theo điều 5 Nghị quyết số 30/2016/QH14 của nước Cộng hoà Xã hội chủ nghĩa Việt Nam, phí cấp thị thực <strong>không được hoàn trả trong bất kỳ trường hợp nào</strong>, vì đây là phí xử lý hồ sơ và không phụ thuộc vào việc được cấp hay từ chối thị thực. <br> <br>
3, Việc cập nhật tình trạng thanh toán phí cấp thị thực có thể mất tới 2 giờ đồng hồ do thời gian xử lý kỹ thuật hoặc kết nối hệ thống. Quý khách cần kiểm tra kết quả đăng ký và kết quả
thanh toán (theo mục 1) trước khi đăng ký lại. Nếu Quý khách nộp và thanh toán cho đơn
đăng ký thứ hai giống với đơn đăng ký trước đó, Cục Quản lý xuất nhập cảnh sẽ giả định
rằng đây là hai đơn khác nhau và khoản phí trước đó sẽ không được hoàn lại. Nếu cả hai hồ
sơ đều được cấp Visa thì đều có giá trị sử dụng như nhau. <br> <br>
4, Nếu thông tin của Quý khách chưa đầy đủ và hợp lệ, Quý khách sẽ được yêu cầu cung cấp
thêm thông tin hoặc bị từ chối dựa trên chính sách của Cục quản lý xuất nhập cảnh Việt Nam.
Trường hợp Quý khách phải cung cấp lại thông tin, thời gian xử lý sẽ được bắt đầu khi Quý
khách gửi lại thông tin HỢP LỆ. Trong trường hợp này, hãy lưu ý thời điểm nhập cảnh. <br> <br>
5, Thị thực điện tử thường được xử lý trong vòng 03 ngày làm việc <span class="text-danger">(*)</span>  sau khi phí thị thực
điện tử đã được thanh toán với các thông tin đầy đủ và hợp lệ. <br> <br>
6, Trường hợp hồ sơ cần được xác minh Bộ bởi Công an, Quý khách sẽ nhận được kết quả
trong thời gian từ 03 đến 15 ngày làm việc <span class="text-danger">(*)</span>
<br> <br>
7, Để được hỗ trợ về Thị thực điện tử, vui lòng xem thêm thông tin tại:
<a href="https://evisa.gov.vn/faq" class="link-point" target="_blank">
https://evisa.gov.vn/faq
</a>  hoặc liên hệ trực tiếp với Cục quảnlý xuất nhập cảnh Việt Nam tại: 
<a href="https://evisa.gov.vn/support" class="link-point" target="_blank">
https://evisa.gov.vn/support
</a> <br>
<span class="text-danger">(*)</span> Ngày làm việc không bao gồm ngày nghỉ cuối tuần và các ngày lễ quốc gia. <br> <br>
<p class="text-danger">Nếu Quý khách tiếp tục nộp tiền, Quý khách xác nhận rằng đã đọc và hiểu toàn bộ các quy
định về cấp thị thực của Cục quản lý xuất nhập cảnh Việt Nam.</p>') ?>
                    <?php } else { ?>
                        <?= Translate::get('<p>1. Bạn xác nhận đã đọc và đồng ý với điều kiện, điều khoản của dịch vụ đang thực hiện trên cổng dịch vụ cổng Bộ công an tại website <a data-fr-linked="true" href="https://dichvucong.bocongan.gov.vn">https://dichvucong.bocongan.gov.vn</a></p>
<p>2. Theo quy định kết quả xử lý hồ sơ sẽ không được cung cấp qua email. Để kiểm tra trạng thái hồ sơ, bạn làm theo các bước sau:</p>
<p>&nbsp; &nbsp;- Bước 1: Truy cập trang <a data-fr-linked="true" href="https://dichvucong.bocongan.gov.vn/bocongan/tracuu">https://dichvucong.bocongan.gov.vn/bocongan/tracuu</a></p>
<p>&nbsp; &nbsp;- Bước 2: Điền thông tin mã đơn hàng</p>
<p>3. Theo điều 6 của thông tư 25/2021/25/2021/TT-BTC nước CHXHCN VN người nộp lệ phí đã nộp phí nhưng không đủ điều kiện cấp thị thực và các giấy tờ khác có giá trị xuất cảnh, nhập cảnh, cư trú cho người nước ngoài hoặc từ chối nhận kết quả xử lý hồ sơ, tổ chức thu phí không phải hoàn trả số tiền phí đã thu.</p>
<p>4. Nếu thông tin của bạn không đầy đủ và hợp lệ, bạn sẽ được yêu cầu cung cấp thông tin hoặc có
thể bị từ chối theo quy định của Pháp luật Việt Nam. Khi phải cung cấp lại thông tin, thời gian xử
lý sẽ được tính kể từ khi Cục QLXNC nhận được thông tin đầy đủ và hợp lệ.</p>
<p>5. Thời gian xử lý hồ sơ trong vòng 5 ngày làm việc theo giờ Việt Nam (không bao gồm ngày cuối
tuần và các ngày nghỉ lễ quốc gia) sau khi phí được thanh toán đầy đủ và hồ sơ đầy đủ hợp lệ.
Trong trường hợp hồ sơ cần được tiếp tục xác minh bởi Bộ Công an, thời gian xử lý hồ sơ thực tế
có thể kéo dài từ 5-30 ngày làm việc.</p>
<p>6. Ngoài thời gian trên, để biết thêm thông tin chi tiết về tình trạng hồ sơ hoặc khiếu nại, bạn vui
lòng liên hệ Cơ quan QLXNC có thẩm quyền xử lý hồ sơ theo thông tin sau:</p>
') ?><p>
                        - <?= Translate::get('Địa chỉ') ?>  <?= Translate::getV1(@$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['area'] . ':' . @$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['address']) ?></p>
                        <p>- Email: <a data-fr-linked="true" href="mailto:contact@immigration.gov.vn">contact@immigration.gov.vn</a>
                        </p>
                        <p>- <?= Translate::get('Số điện thoại') ?>:
                            <span> <?= @$GLOBALS['BCA_ALL_CITIES'][$checkout_order['merchant_id']]['phone_number'] ?></span>
                        </p>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-confirm-check-in" data-link="false"
                                id="btn-confirm"><?= Translate::get('Tôi đồng ý với điều khoản trên') ?> </button>
                        <button type="button" class="btn btn-secondary btn-close-check-in"
                                data-dismiss="modal"><?= Translate::get('Đóng lại') ?></button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <style>
        .link-point {
            color: #3cafd5;
            text-decoration: none;
        }
    </style>
</div>

<!--END Modal-->


<div class="modal fade" id="modal-notify" tabindex=-1 role=dialog aria-hidden=true>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;
                </button>
                <h4 class="modal-title"><?= Translate::get('Thông báo') ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal" role="form">
                    <div class="alert alert-warning fade in" align="center">
                        <span id="error_message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // console.log($('#check-atm-card').val());
        // console.log($('#enable-merchant-confirm').val());
        if ($('#check-atm-card').val()) { // nếu PT là ATM-CARD
            $("#pay-button").click(function () {
                // console.log($(this));return;
                if ($("#enable-confirm").val()) {
                    $("#modal-confirm").modal("show");
                    $('#btn-confirm').click(function () {
                        $('#form-checkout').submit();
                    })
                } else {
                    // window.location = $(this).data("link");
                }

            })
            // $("#btn-confirm").click(function () {
            //     window.location = $(this).data("link");
            // })
        }
    })
</script>