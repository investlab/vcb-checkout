<?php
use yii\helpers\Html;
use common\components\utils\Strings;
use common\components\utils\ObjInput;

?>
    Chào <?= $user_login_info['fullname'] ?>,<br>
    <br>
    Bạn vừa gửi yêu cầu lấy lại mật khẩu đăng nhập. Để thiết đặt lại mật khẩu hãy click vào link dưới đây để đặt lại mật khẩu đăng nhập tài khoản của bạn:
    <br>
    <a href="<?= $url_verify ?>" target="blank" title="Link đặt lại mật khẩu đăng nhập tài khoản"><?= $url_verify ?></a>
    <br>
    BQT <?= DOMAIN ?>