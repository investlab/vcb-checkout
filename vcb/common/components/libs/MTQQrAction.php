<?php

namespace common\components\libs;

use Yii;
use yii\base\Action;
use common\components\libs\qrcode\QrCode;
use common\components\libs\qrcode\lib\Enum;

class MTQQrAction extends Action
{

    public function run()
    {
        $code = Yii::$app->request->get('code');
        if (trim($code) != '') {
            $size = intval(Yii::$app->request->get('size'));
            if ($size == 0) {
                $size = 4;
            }
            return QrCode::png($code, false, Enum::QR_ECLEVEL_L, $size);
        }
        return false;
    }
}