<?php
namespace common\components\utils;
/*
 * Chuyển từ số thành chữ
 * @input: number
 * 
 * function: readNumber
 * 
 * @return string, ex: một trăm ngàn đồng
 */
class Converts
{

    //Day so
    protected $sequence = array('không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín');

    //Doc hang chuc
    protected function readDozens($number, $ranged)
    {
        $string = '';
        $dozen = floor($number / 10); //hang chuc
        $units = $number % 10; //Don vi
        if ($dozen > 1) {
            $string = " " . $this->sequence[$dozen] . " mươi";
            if ($units == 1) {
                $string .= " mốt";
            }
        } else if ($dozen == 1) {
            $string = " mười";
            if ($units == 1) {
                $string .= " một";
            }
        } else if ($ranged && $units > 0) {
            $string = " lẻ";
        }
        if ($units == 5 && $dozen >= 1) {
            $string .= " lăm";
        } elseif ($units == 4 && $dozen >= 1) {
            $string .= " tư";
        } else if ($units > 1 OR ($units == 1 && $dozen == 0)) {
            $string .= " " . $this->sequence[$units];
        }
        return $string;
    }

    //So va day du
    protected function readBlock($number, $ranged)
    {
        $string = "";
        $hundreds = floor($number / 100); //Hang tram
        $number = $number % 100;
        if ($ranged OR $hundreds > 0) {
            $string = " " . $this->sequence[$hundreds] . " trăm";
            $string .= $this->readDozens($number, true);
        } else
            $string = $this->readDozens($number, false);

        return $string;
    }

    //So va day du
    protected function readMillion($number, $ranged)
    {
        $string = "";
        $million = floor($number / 1000000); //Trieu
        $number = $number % 1000000;
        if ($million > 0) {
            $string = $this->readBlock($million, $ranged) . " triệu";
            $ranged = true;
        }
        $thousands = floor($number / 1000); //Hang nghin
        $number = $number % 1000;
        if ($thousands > 0) {
            $string .= $this->readBlock($thousands, $ranged) . " nghìn";
            $ranged = true;
        }
        if ($number > 0)
            $string .= $this->readBlock($number, $ranged);

        return $string;
    }

    public function readNumber($number)
    {
        if ($number == 0) {
            return $this->sequence[0];
        }
        $string = "";
        $postfix = ""; //Hậu tố
        do {
            $billion = $number % 1000000000; //Tỷ
            $number = floor($number / 1000000000);

            if ($number > 0)
                $string = $this->readMillion($billion, true) . $postfix . $string;
            else
                $string = $this->readMillion($billion, false) . $postfix . $string;

            $postfix = " tỷ";
        } while ($number > 0);

        return $string;
    }

    public static function convertString($string) {
        $text = '';
        for ($i = 0; $i < strlen($string); $i += 2) {
            $text .= substr($string, $i, 2) . "<wbr>";
        }
        return $text;
    }
}
