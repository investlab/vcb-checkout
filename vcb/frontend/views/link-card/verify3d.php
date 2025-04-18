<?php 
use common\components\utils\Translate;
use common\payments\CyberSource;

$data = CyberSource::decryptSessionInfo($session_info);
?>
<div class="col-sm-12 brdRight">
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
    <div class="col-xs-12 col-sm-10 col-md-8 brdRightIner vcb">
        <h4 class=""><?= Translate::get('Xác thực thẻ liên kết') ?></h4>
        <div class="panel-group row" id="accordion">
            <div class="panel-heading rlv">
                <div class="logo-method">
                    <img src="<?= ROOT_URL ?>/frontend/web/images/credit_card.png" alt="loading...">
                </div>
                <h4 class="panel-title color-vcb"><strong><?=Translate::get('Thẻ Visa / MasterCard / JCB')?></strong></h4>
            </div>
            <div class="col-xs-12 col-sm-12">
                <div class="panel panel-default" id="content-verify">
                    <div class="panel-body">
                        <div class="media">
                            <span class="pull-left"><img src="https://upload.nganluong.vn/public/images/3dsecu.jpg" style="width:80px" /></span>
                            <div class="media-body">
                                <h4 class=" media-heading"><?= Translate::get('Thẻ 3D Secure') ?></h4>
                                <p><?= Translate::get('Thẻ của bạn đang sử dụng mật khẩu bảo vệ khi thanh toán trực tuyến. Đây là mật khẩu được cấp bởi ngân hàng phát hành thẻ đối với chủ thẻ đăng ký sử dụng dịch vụ Verified by VISA cho thẻ Visa và dịch vụ MasterCard® SecureCodeTM cho thẻ MasterCard®.') ?></p>
                                <p><?= Translate::get('Để hoàn tất quá trình thanh toán, bạn bấm vào nút "Xác nhận thanh toán" dưới đây và nhập chính xác mật khẩu do ngân hàng đã cấp cho bạn. Trường hợp bạn chưa rõ hoặc quên mật khẩu, vui lòng liên hệ với ngân hàng phát hành thẻ để biết thêm thông tin.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group noneMrbtm">
                    <form id="PAEnrollForm" name="PAEnrollForm" action="<?= $data['response_info']['acsURL']; ?>" method="post" />
                    <input type="hidden" name="PaReq" value="<?= $data['response_info']['paReq']; ?>"/>
                    <input type="hidden" name="TermUrl" value="<?= $verify3d_url; ?>" />
                    <input type="hidden" name="MD" value="<?= $data['response_info']['xid']; ?>" />
                    <button class="btn btn-success" type="submit">Xác nhận thanh toán</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-1 col-md-2"></div>
</div>
