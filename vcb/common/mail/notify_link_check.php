<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title><?= DOMAIN ?></title>
</head>
<body>
<div style="max-width:800px">
    <table cellpadding="0" cellspacing="0"   width="100%">
        <tbody>
        <tr>
            <td style="background:#63aa42">
                <table cellpadding="0" cellspacing="0"  width="100%">
                    <tr>
                        <td valign="middle"
                            style="padding:13px 0px 8px 0px;background-color:#ffffff;border-bottom:0px solid #333333; text-align:center">
                            <a href="<?= ROOT_URL ?>" style="text-decoration:none; color:#ffffff" target="_blank"
                               title="<?= DOMAIN ?>">
                                <img width="200" src="https://vietcombank.nganluong.vn/logo.png" alt="<?= DOMAIN ?>" border="0"
                                     height="auto">
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <p><strong  style="color: red !important;font-size: 18px">GẤP GẤP GẤP!!</strong> Xuất hiện nhiều notify_url không hoạt động. Đề nghị anh/chị vui lòng kiểm tra!!!</p>
        <tr>

            <td style="border-collapse:collapse;border:1px solid #0492aa;">
                <!--begin Conten-->
                <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="padding:18px 20px 20px 20px;vertical-align:middle;line-height:20px;font-family:Arial;background-color:#63aa42; text-align:center">
                            <span style="font-size: 115%; color: #ffffff; text-transform: capitalize;">Danh sách các link không hoạt động</span>
                        </td>
                    </tr>
                    <tr>

                    </tr>
                    <tr>

                        <td style="padding:20px 20px 12px 20px">
                            <table style="border: 1px solid rgba(143,143,143,0.29);"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="border: 1px solid rgba(143,143,143,0.29);"><strong>Notify_url</strong></td>
                                    <td align="center" style="border: 1px solid rgba(143,143,143,0.29);"><strong>Merchant tương ứng</strong></td>
                                </tr>
                                <?php foreach ($links as $item):?>
                                <tr>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif;
                                     border: 1px solid rgba(143,143,143,0.29);
                                     font-size:12px; color:#2525253;"
                                        align="center"><strong><?= $item->url_check ?></strong></td>
                                    <td align="center" style="border: 1px solid rgba(143,143,143,0.29);">
                                        <?= \common\models\db\Merchant::getNameById($item->merchant_id)  ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>


                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>

</body>
</html>
<style>
    table, th,td{
        border-collapse: collapse;
    }
</style>
