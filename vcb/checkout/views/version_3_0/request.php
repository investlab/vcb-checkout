<?php

use common\components\utils\ObjInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\utils\Translate;

/** @var array $checkout_order */


$this->title = Translate::get('Thanh toán đơn hàng');
$this->params['breadcrumbs'][] = $this->title;

$merchant = \common\models\db\Merchant::findOne($checkout_order['merchant_id']);
$btn_return = '';
$btn_cancel = '';
$colpayment = 4;
$colother = 3;
if ($merchant->exception == 'no_return') {
    $btn_return = 'none';
    $colpayment = 6;
    $colother = 4;
} elseif ($merchant->exception == 'no_cancel') {
    $btn_cancel = 'none';
    $colnumber = 6;
    $colother = 4;
}
// https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/vi/checkout/version_1_0/request/155944-CO77EA55DEE3/EXB-ATM-CARD
// https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/vi/checkout/version_1_0/verify/155944-CO77EA55DEE3?transaction_checksum=145105-5899d5ddb74


if ((str_contains($model->payment_method_code, '-ATM-CARD')
//        || str_contains($model->payment_method_code, '-CREDIT-CARD')
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

//echo strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code);die;
?>

<?php require_once('includes/header.php') ?>
<main>
    <div class="container">
        <div class="accordion box-collapse" id="accordionExample">
            <div class="card">
                <div id="collapseOne" class="collapse show card-form" aria-labelledby="headingOne"
                     data-parent="#accordionExample">
                    <!--                    --><?php
                    //                    $form = ActiveForm::begin([
                    //                        'id' => 'form-checkout',
                    //                        'action' => $model->getRequestActionForm(),
                    //                        'options' => ['class' => 'active p-0']
                    //                    ]);
                    //                    echo $form->field($model, 'payment_method_id')->hiddenInput()->label(false);
                    //                    echo $form->field($model, 'partner_payment_id')->hiddenInput()->label(false);
                    //                    ?>
                    <?php echo Yii::$app->view->renderFile('@app/views/' . Yii::$app->controller->id . '/includes/request/' . strtolower($model->partner_payment_code) . '/' . strtolower($model->payment_method_code) . '.php',
                        array('model' => $model, 'checkout_order' => $checkout_order)); ?>
                    <!--                    --><?php
                    //                    if (isset($form)) {
                    //                        ActiveForm::end();
                    //                    }
                    //                    ?>
                </div>
            </div>
        </div>
</main>
<?php require_once('includes/footer.php') ?>
<!-- Modal -->
<div class="modal fade" id="access-terms-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= Translate::get('Điều khoản') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_XNC'])): ?>
                    <?= Translate::get(
                        'Thị thực là Thủ tục quan trọng trong quá trình xuất nhập cảnh, do đó Quý khách cần kiểm tra thông tin cẩn thận trước khi nộp và lưu ý các quy định như sau: <br>
<strong>Lưu ý</strong>: Mã hồ sơ điện tử sẽ được gửi qua email mà Quý khách đăng ký. Do đó, vui lòng cung cấp email chính xác và hoàn toàn chịu trách nhiệm về thông tin đã cung cấp. <br>
1, Theo quy định, kết quả thị thực điện tử sẽ <strong>không được gửi qua email</strong>. Để biết kết quả này, Quý khách thực hiện theo các bước sau: <br>
- B1: Quý khách truy cập vào trang: <a href="https://evisa.xuatnhapcanh.gov.vn/vi_VN/tra-cuu-ho-so" class="link-point">https://evisa.xuatnhapcanh.gov.vn/vi_VN/tra-cuu-ho-so</a> <br>
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
<a href="https://evisa.xuatnhapcanh.gov.vn/vi_VN/web/guest/faq" class="link-point">
https://evisa.xuatnhapcanh.gov.vn/vi_VN/web/guest/faq
</a>  hoặc liên hệ trực tiếp với Cục quảnlý xuất nhập cảnh Việt Nam tại: 
<a href="https://evisa.xuatnhapcanh.gov.vn/vi_VN//web/guest/lien-he" class="link-point">
https://evisa.xuatnhapcanh.gov.vn/vi_VN//web/guest/lien-he
</a> <br>
<span class="text-danger">(*)</span> Ngày làm việc không bao gồm ngày nghỉ cuối tuần và các ngày lễ quốc gia. <br> <br>
<p class="text-danger">Nếu Quý khách tiếp tục nộp tiền, Quý khách xác nhận rằng đã đọc và hiểu toàn bộ các quy
định về cấp thị thực của Cục quản lý xuất nhập cảnh Việt Nam.</p>') ?>
                <?php elseif (in_array($checkout_order["merchant_id"], $GLOBALS['MERCHANT_BCA'])): ?>
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
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal"><?= Translate::get('Đóng') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-notify" tabindex="-1" role="dialog" aria-labelledby="modal-notify-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-notify-label"><?= Translate::get('Thông báo') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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

        //==== Xử lý nút quay lại -> redirect sang trang index
        var redirectURL = '<?= Yii::$app->urlManager->createAbsoluteUrl([
            "version_3_0/index",
            "token_code" => $checkout_order['token_code']
        ]) ?>';
        console.log('redirectURL', redirectURL);

        // Thêm trạng thái vào lịch sử trình duyệt
        var state = {page: "request"};
        var title = "Trang nhập thông tin thẻ";
        var currentURL = window.location.href;
        history.pushState(state, title, currentURL);

        // Xử lý sự kiện khi nhấn nút quay lại
        window.onpopstate = function (event) {
            window.location.href = redirectURL;
        };
        //==== END Xử lý nút quay lại của trình duyệt
    })
</script>