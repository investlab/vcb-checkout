<?php
/** @var $error integer */
/** @var $link_list array */

/** @var $profile_code_list array */
/** @var $missingCode array */

use common\models\business\ReceiptBussiness;
use kartik\select2\Select2;
use yii\helpers\Url;
$flag1 = $flag2 = false; // Cờ hiển thị nút tải xuống
?>
<div class="panel panel-default wrapCont">
    <br>

    <div class="row">
        <div class="col-md-2" style="margin-left: 15px;">
            <h4>Tool in hóa đơn 3C VCB_NL</h4>
        </div>
        <div class="col-md-8">
            <form action="version_1_0/get-many-receipts" method="post">
                <p class="text-danger">CHỈ CHỌN LOẠI CHƯA KÍ KHI BẠN ĐÃ CÓ PHƯƠNG THỨC ĐỂ LẤY CHỮ KÍ RIÊNG </p>
                <label for="token" class="text-primary">
                    <strong>
                        Chọn loại chữ kí số
                    </strong>
                </label>
                <div style="width: 30% !important;">
                    <?= Select2::widget([
                        'name'          => 'sign_type',
                        'data'          => [
                            ReceiptBussiness::SIGN => 'Có kí số',
                            ReceiptBussiness::NOT_SIGN => 'Không kí số'
                        ],
                        'options'       => [
                            'placeholder' => 'Chọn loại hóa đơn...',
                            'id'          => 'invoice-type-select',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>

                </div>
                <br>

                <label for="token" class="text-primary">
                    <strong>
                        Nhập mã hồ sơ của các đơn hàng cần lấy biên lai (dạng: G01.839.108.000-240529-0122...)
                    </strong>
                </label>
                <input type="text" id="token" name="code_list" required class="form-control"
                       placeholder="Danh sách mã hồ sơ, ngăn cách bởi dấu phẩy">
                <br>



                <br>

                <button type="submit" class="btn btn-primary"><i class="fa fa-key"></i> Get link</button>
                <br>
                <br>
                <?php if (isset($error) && $error == 1): ?>
                    <p class="text-danger"><strong>Cảnh báo: </strong>Dữ liệu truyền vào không hợp lệ!!!</p>
                <?php endif; ?>

                <?php if (isset($error) && $error == 2): ?>
                    <p class="text-danger"><strong>Cảnh báo: </strong>Loại kí số không hợp lệ!!!</p>
                <?php endif; ?>


                <?php if (isset($missingCode) && !empty($missingCode)): ?>
                    <p class="text-danger"><strong>Hồ sơ không tìm thấy!!! </strong><?= implode(',', $missingCode)?></p>
                <?php endif; ?>

                <?php if (isset($link_list) && !empty($link_list) && is_array($link_list)): ?>
                    <?php $flag1 = true; ?>
                    <p class="text-primary"><strong>Danh sách link hóa đơn: </strong><?= implode(' | ', $link_list) ?>
                    </p>
                    <div class="container">
                        <input type="hidden" class="form-control" id="copyLink"
                               value="<?= implode(' | ', $link_list) ?>">
                        <a class="btn btn-primary" href="javascript:" id="copyLinkButton" data-clipboard-target="#copyLink">
                            <i class="fa fa-copy"></i> Sao chép link hóa đơn</a>
                        <span id="copyLinkSuccess" style="display:none; color:green; margin-left: 10px;">Đã sao chép!</span>
                    </div>
                    <hr>

                <?php endif; ?>


                <?php if (isset($profile_code_list) && !empty($profile_code_list) && is_array($profile_code_list)): ?>
                    <?php $flag2 = true; ?>

                    <p class="text-primary"><strong>Danh sách mã hồ sơ: </strong><?= implode(' | ',
                            $profile_code_list) ?></p>
                    <div class="container">
                        <input type="hidden" class="form-control" id="copyHoso"
                               value="<?= implode(' | ',
                                   $profile_code_list) ?>">
                        <a class="btn btn-primary" href="javascript:" id="copyHosoButton" data-clipboard-target="#copyHoso">
                            <i class="fa fa-copy"></i> Sao chép mã hồ sơ</a>
                        <span id="copySuccess" style="display:none; color:green; margin-left: 10px;">Đã sao chép!</span>
                    </div>
                    <hr>
                <?php endif; ?>



                <?php if($flag1 && $flag2):  ?>
                    <a target="_blank" href="<?= Url::toRoute(['pdf/download']) ?>" class="btn btn-primary">
                        <i class="fa fa-download"></i> Tải xuống hóa đơn</a></p>
                    <br>
                <?php endif;?>
                <?php if (isset($arr_links_as_token) && !is_null($arr_links_as_token)): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <th>Mã hồ sơ</th>
                        <th>Token_code</th>
                        <th>Link Success1</th>
                        <th>Link lấy hóa đơn 3C</th>
                        </thead>
                        <tbody>
                        <?php foreach ($arr_links_as_token as $item): ?>
                            <tr>
                                <td>
                                    <?php echo $item['profile_code']; ?><br>
                                </td>
                                <td>
                                    <?php echo $item['token_code']; ?><br>
                                </td>
                                <td>
                                    <?php echo $item['url']; ?><br>
                                </td>
                                <td>
                                    <a href="<?= $item['result_success1'] ?>" target="_blank">
                                        <?= $item['result_success1'] ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>

                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
<script>
    $(document).ready(function () {
        var clipboardLink = new ClipboardJS('#copyLinkButton');

        clipboardLink.on('success', function (e) {
            $('#copyLinkSuccess').show().delay(2000).fadeOut(); // Hiển thị thông báo "Đã sao chép!"
            $('#copyLinkButton').text('Đã sao chép').removeClass('btn-primary').addClass('btn-success'); // Thay đổi nội dung nút
            e.clearSelection();
        });

        clipboardLink.on('error', function (e) {
            alert('Không thể sao chép. Vui lòng thử lại.');
        });

        var clipboardHoso = new ClipboardJS('#copyHosoButton');

        clipboardHoso.on('success', function (e) {
            $('#copyHosoSuccess').show().delay(2000).fadeOut(); // Hiển thị thông báo "Đã sao chép!"
            $('#copyHosoButton').text('Đã sao chép').removeClass('btn-primary').addClass('btn-success'); // Thay đổi nội dung nút
            e.clearSelection();
        });

        clipboardHoso.on('error', function (e) {
            alert('Không thể sao chép. Vui lòng thử lại.');
        });
    });

</script>

<style>
    /* Tùy chỉnh chung cho Select2 */
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        background-color: #f9f9f9;
        color: #333;
    }

    /* Tùy chỉnh khi có nội dung trong dropdown */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }


</style>