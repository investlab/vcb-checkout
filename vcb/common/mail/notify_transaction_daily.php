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
    <table cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background:#0492aa">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td valign="middle"
                            style="padding:13px 0px 8px 0px;background-color:#ffffff;border-bottom:0px solid #333333; text-align:center">
                            <a href="<?= ROOT_URL ?>" style="text-decoration:none; color:#ffffff" target="_blank"
                               title="<?= DOMAIN ?>">
                                <img width="200" src="<?= ROOT_URL ?>logo.png" alt="<?= DOMAIN ?>" border="0"
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
                        <td style="padding:20px 20px 12px 20px">
                            <?= $body_content?>
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