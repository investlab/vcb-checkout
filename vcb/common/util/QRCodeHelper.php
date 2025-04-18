<?php

namespace common\util;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeHelper
{
    public static function generateFromText($string)
    {
        if ($string != "") {
            $qr = new  QrCode($string);

            $writer = new PngWriter();

            $result = $writer->write($qr);

            return base64_encode($result->getString());

        } else {
            return "Error: input not empty";
        }
    }
}