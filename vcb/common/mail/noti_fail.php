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
    <table cellpadding="0" cellspacing="0" border="0"  width="100%">
        <tbody>
        <tr>
            <td style="background:#63aa42">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
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
        <tr>
            <td style="border-collapse:collapse;border:1px solid #0492aa;">
                <!--begin Conten-->
                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding:18px 20px 20px 20px;vertical-align:middle;line-height:20px;font-family:Arial;background-color:#63aa42; text-align:center">
                            <span style="font-size: 115%; color: #ffffff; text-transform: capitalize;">Thông báo giao dịch thất bại</span>
                            <p style="margin: 5px 0 0 0;color: white;">Mã giao dịch: <?= $transaction_id ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 20px 12px 20px">
                            <table style="border-left:1px solid #dcdcdc;border-right:1px solid #dcdcdc" border="0"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Nội dung thanh toán:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $order_description ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Mã đơn hàng:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $order_code ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Số tiền:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= number_format($amount) .' '. $currency ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Thời gian thanh toán:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= date('H:i d/m/Y', $time_paid) ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Người thanh toán:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $payment_name ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Email người thanh toán:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $email ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Địa chỉ:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $address ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Phương thức thanh toán:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $payment_method ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Lý do thất bại:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $reason ?></strong></td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div style="max-width:800px">
    <table cellpadding="0" cellspacing="0" border="0"  width="100%">
        <tbody>
        <tr>
            <td style="background:#63aa42">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td valign="middle"
                            style="padding:13px 0px 8px 0px;background-color:#ffffff;border-bottom:0px solid #333333; text-align:center">

                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="border-collapse:collapse;border:1px solid #0492aa;">
                <!--begin Conten-->
                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding:18px 20px 20px 20px;vertical-align:middle;line-height:20px;font-family:Arial;background-color:#63aa42; text-align:center">
                            <span style="font-size: 115%; color: #ffffff; text-transform: capitalize;">Failed transaction notification</span>
                            <p style="margin: 5px 0 0 0;color: white;">Transaction ID: <?= $transaction_id ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 20px 12px 20px">
                            <table style="border-left:1px solid #dcdcdc;border-right:1px solid #dcdcdc" border="0"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Order description:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $order_description ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Order code:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $order_code ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Amount:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= number_format($amount) .' '. $currency ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Time paid:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= date('H:i d/m/Y', $time_paid) ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Payment name:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $payment_name ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Buyer email:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $email ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Address:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= $address ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Payment method:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?=  \common\components\utils\Translate::get($payment_method,'en-US') ?> </strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right"><span>Reason:</span></td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left"><strong><?= \common\components\utils\Translate::get($reason,'en-US') ?></strong></td>
                                </tr>

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
