<?php

use common\components\utils\ObjInput;
use common\components\libs\Tables;

$payment_method_info = Tables::selectOneDataTable("payment_method", ["id = :id", "id" => $cashout_info['payment_method_id']]);
$status_name = common\models\db\Cashout::getStatus();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title><?= DOMAIN ?></title>
</head>
<body>
<div style="max-width:650px">
    <table cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background:#0492aa">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td valign="middle"
                            style="padding:13px 20px 8px 0px;background-color:#ffffff;border-bottom:0px solid #333333; text-align:left">
                            <a href="<?= ROOT_URL ?>" style="text-decoration:none; color:#ffffff" target="_blank"
                               title="<?= DOMAIN ?>">
                                <img width="120" src="<?= ROOT_URL ?>logo.png" alt="<?= DOMAIN ?>" border="0"
                                     height="auto">
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border-collapse:collapse;border-left:1px solid #0492aa;border-right:1px solid #0492aa;">
                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="padding:18px 20px 20px 20px;vertical-align:middle;line-height:20px;font-family:Arial;background-color:#0492aa; text-align:center">
                            <span style="font-size: 115%; color: #ffffff">Thông báo từ chối yêu cầu rút tiền</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 20px 12px 20px">
                            <table style="border-left:1px solid #dcdcdc;border-right:1px solid #dcdcdc" border="0"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Mã yêu cầu</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= $cashout_info['id'] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Thời gian</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= date('H:i, d/m/Y', $cashout_info['time_begin']) ?>
                                            đến <?= date('H:i, d/m/Y', $cashout_info['time_end']) ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Số tiền rút</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= ObjInput::makeCurrency($cashout_info['amount']) ?> <?= $cashout_info['currency'] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Phí rút</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= ObjInput::makeCurrency($cashout_info['receiver_fee']) ?> <?= $cashout_info['currency'] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Số tiền nhận được</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= ObjInput::makeCurrency($cashout_info['amount'] - $cashout_info['receiver_fee']) ?> <?= $cashout_info['currency'] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Hình thức rút tiền</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= $payment_method_info['name'] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Trạng thái</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= $status_name[$cashout_info['status']] ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Thời gian tạo</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= date('H:i, d/m/Y', $cashout_info['time_created']) ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 10px 8px 20px; font-family:Arial, Helvetica, sans-serif; color:#666666; font-size:12px;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="right">
                                        <span>Thời gian từ chối</span>
                                    </td>
                                    <td style="padding:8px 20px 8px 10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#2525253;border-bottom:1px solid #dcdcdc;border-top:1px solid #dcdcdc"
                                        align="left">
                                        <strong><?= date('H:i, d/m/Y', $cashout_info['time_reject']) ?></strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:4px 20px 0px 20px" align="center">
                            <a href="<?= $url_detail ?>" title="Xem chi tiết yêu cầu"
                               style="display: block; background-color:#3071a9; color:#FFF; border:1px solid #285e8e; height: 25px; font-size: 13px; font-weight: 400; line-height: 25px; text-align: center; cursor: pointer; width: 150px; text-decoration: none;">Xem
                                chi tiết</a>
                        </td>
                    </tr>
                    <!--tr>
                        <td style="padding:20px 20px 0px 20px"><span
                                style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #252525">Thông tin liên hệ:</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0px 20px 12px 20px">
                            <ul style="font-size:12px;color:#252525; font-family:Arial, Helvetica, sans-serif; line-height:18px">
                                <li><?= $GLOBALS['FRONTEND_PAGE']['COMPANY'] ?></li>
                                <li>Trụ sở Hà Nội: <?= $GLOBALS['FRONTEND_PAGE']['ADDRESS']['HN'] ?></li>
                                <li>Văn phòng Tp.HCM: <?= $GLOBALS['FRONTEND_PAGE']['ADDRESS']['HCM'] ?></li>
                                <li>Điện thoại: <?= $GLOBALS['FRONTEND_PAGE']['PHONE'] ?></li>
                                <li>Hotline:  <?= $GLOBALS['FRONTEND_PAGE']['HOTLINE'] ?></li>
                                <li>Email: <a
                                        href="mailto:<?= $GLOBALS['FRONTEND_PAGE']['EMAIL'] ?>"><?= $GLOBALS['FRONTEND_PAGE']['EMAIL'] ?></a>
                                </li>
                            </ul>
                        </td>
                    </tr-->
                    <tr>
                        <td valign="middle"
                            style="background-color:#1d3153;font-size:11px;vertical-align:middle;text-align:center;padding:10px 20px 10px 20px;line-height:18px;border:1px solid #252525;font-family:Arial; color:#CCCCCC">
                            <?= $GLOBALS['FRONTEND_PAGE']['COMPANY'] ?>
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
