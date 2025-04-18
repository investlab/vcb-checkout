<?php

namespace common\components\utils;

/*
 * update           : 201
 * author update    : thong.tnv@gmail.com
 *
 */

class FormatDateTime
{

    //Ham lay ngay hien tai
    static function getDate()
    {
        return date("d/m/Y");
    }

    //Ham lay gio hien tai
    static function getTime()
    {
        return date('H:m:s');
    }

    //Ham lay ngay tu timestamp
    static function getDateFromTimestamp($ts)
    {
        return date("d/m/Y", $ts);
    }

    //Ham lay gio tu timestamp
    static function getTimeFromTimestamp($ts)
    {
        return date('H:i:s', $ts);
    }

    //Ham chuyen ngay : dd/mm/yyyy sang timestamp
    static function dateToTimestamp($date)
    {
        return self::toTime($date);
    }

    //Ham chuyen gio hh:mm:ss sang giay
    static function timeToTimestamp($time)
    {
        $h = explode(':', $time);
        //Sửa đổi: TuấnNK (27-09-06)
        if (!is_numeric($h['0']))
            $h['0'] = 0;
        if (!is_numeric($h['1']))
            $h['1'] = 0;
        if (!is_numeric($h['2']))
            $h['2'] = 0;
        //Hết đoạn sửa

        if ($h[0] && $h[1] && $h[2]) {
            return ($h[0] * 3600) + ($h[1] * 60) + $h[2];
        } elseif ($h[0] && $h[1]) {
            return ($h[0] * 3600) + ($h[1] * 60);
        } elseif ($h[0])
            return ($h[0] * 3600);
        else
            return 0;
    }

    //Ham chuyen gio d/m/Y-HH:i:s sang timestamp
    static function toTimestamp($time)
    {
        $t = explode('-', $time);

        if ($t['0']) {
            $day_stamp = self::toTime($t['0']);
//            var_dump($day_stamp);die;
            $h = explode(':', $t['1']);
            //Sửa đổi: TuấnNK (27-09-06)
            if (!isset($h['0']) || !is_numeric($h['0'])) {
                $h['0'] = 0;
            }
            if (!isset($h['1']) || !is_numeric($h['1'])) {
                $h['1'] = 0;
            }

            if (!isset($h['2']) || !is_numeric($h['2']))
                $h['2'] = 0;
            //Hết đoạn sửa

            if ($h[0] && $h[1] && $h[2]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $h[2] + $day_stamp;
            } elseif ($h[0] && $h[1]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $day_stamp;
            } elseif ($h[0]) {
                return ($h[0] * 3600) + $day_stamp;
            } else
                return $day_stamp;
        } else
            return false;
    }

    //Ham chuyen gio H:i:s d/m/Y sang timestamp
    static function dateToTimes($time)
    {
        $t = explode(' ', $time);

        if ($t['1']) {
            $day_stamp = self::toTime($t['1']);

            $h = explode(':', $t['0']);
            //Sửa đổi: TuấnNK (27-09-06)
            if (!is_numeric($h['0']))
                $h['0'] = 0;
            if (!is_numeric($h['1']))
                $h['1'] = 0;
            if (!is_numeric($h['2']))
                $h['2'] = 0;
            //Hết đoạn sửa

            if ($h[0] && $h[1] && $h[2]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $h[2] + $day_stamp;
            } elseif ($h[0] && $h[1]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $day_stamp;
            } elseif ($h[0]) {
                return ($h[0] * 3600) + $day_stamp;
            } else
                return $day_stamp;
        } else
            return false;
    }

    //Ham chuyen gio d/m/Y H:i:s sang timestamp
    static function datetimeToTimes($time)
    {
        $t = explode(' ', $time);

        if ($t['0']) {
            $day_stamp = self::toTime($t['0']);

            $h = explode(':', $t['1']);
            //Sửa đổi: TuấnNK (27-09-06)
            if (!is_numeric($h['0']))
                $h['0'] = 0;
            if (!is_numeric($h['1']))
                $h['1'] = 0;
            if (!isset($h['2']) || !is_numeric($h['2']))
                $h['2'] = 0;
            //Hết đoạn sửa

            if ($h[0] && $h[1] && $h[2]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $h[2] + $day_stamp;
            } elseif ($h[0] && $h[1]) {
                return ($h[0] * 3600) + ($h[1] * 60) + $day_stamp;
            } elseif ($h[0]) {
                return ($h[0] * 3600) + $day_stamp;
            } else
                return $day_stamp;
        } else
            return false;
    }

    static function dateVn2eng($date)
    {
        $a = explode('/', $date);
        if (sizeof($a) == 3 and checkdate($a[1], $a[0], $a[2])) {

            return ($a[1] . '/' . $a[0] . '/' . $a[2]);
        }
    }

    //Ham chuyen doi ngay mm/dd/yyyy sang timestamp
    static function toTime($date)
    {
        $date = str_replace(' ', '', $date);
        $date = str_replace('-', '/', $date);
        $a = explode('/', $date);
        //Hết đoạn sửa
        #Edited by Haptt, September 4th, 2009:
        if (!is_numeric((int)$a[0]))
            $a[0] = 0;
        if (!is_numeric((int)$a[1]))
            $a[1] = 0;
        if (!is_numeric((int)$a[2]))
            $a[2] = 0;
        #End edit
        if (sizeof($a) == 3 and checkdate($a[1], $a[0], $a[2])) {
            return strtotime($a[1] . '/' . $a[0] . '/' . $a[2]);
        } else {
            return false;
        }
    }

    /* created by diendc */

    //Ham chuyen doi ngay dd/mm/yyyy sang timestamp
    static function toTimeBegin($date)
    {
        $date = str_replace(' ', '', $date);
        $date = str_replace('-', '/', $date);
        $a = "";
        if (stripos($date, '/') !== false) {
            $a = explode('/', $date);
        } elseif (stripos($date, '-') !== false) {
            $a = explode('-', $date);
        }

        if (!isset($a['0']) || !is_numeric($a['0']))
            $a['0'] = 0;
        if (!isset($a['1']) || !is_numeric($a['1']))
            $a['1'] = 0;
        if (!isset($a['2']) || !is_numeric($a['2']))
            $a['2'] = 0;
        if (sizeof($a) == 3 and checkdate($a[1], $a[0], $a[2])) {
            return mktime(0, 0, 0, $a['1'], $a['0'], $a['2']);
        } else {
            return false;
        }
    }

    /* created by diendc */

    //Ham chuyen doi ngay dd/mm/yyyy sang timestamp
    static function toTimeEnd($date)
    {
        $date = str_replace(' ', '', $date);
        $date = str_replace('-', '/', $date);
        $a = "";
        if (stripos($date, '/') !== false) {
            $a = explode('/', $date);
        } elseif (stripos($date, '-') !== false) {
            $a = explode('-', $date);
        }
        if (!isset($a['0']) || !is_numeric($a['0']))
            $a['0'] = 0;
        if (!isset($a['1']) || !is_numeric($a['1']))
            $a['1'] = 0;
        if (!isset($a['2']) || !is_numeric($a['2']))
            $a['2'] = 0;
        if (sizeof($a) == 3 and checkdate($a[1], $a[0], $a[2])) {
            return mktime(23, 59, 59, $a['1'], $a['0'], $a['2']);
        } else {
            return false;
        }
    }

    // ham nay cho phep chuyen kieu
    // ngay dd/mm/yyyy sang yyyy-mm-dd de luu vao co sso du lieu
    static function convertDateToMysql($date)
    {
        if (!empty($date)) {
            $patterns = array("/(\d{1,2})\/(\d{1,2})\/(19|20)(\d{2})/");
            $replace = array("\\3\\4-\\2-\\1");
            return preg_replace($patterns, $replace, $date);
        }
        return "";
    }

    // ham nay cho phep chuyen kieu
    // ngay yyyy-mm-dd sang dd/mm/yyyy de hient thi
    static function convertDateFromMysql($date, $break = false, $format = "\\4/\\3/\\1\\2")
    {
        if (!empty($date)) {
            $patterns = array("/(19|20)(\d{2})-(\d{1,2})-(\d{1,2})/",
                "/(\d{1,2}):(\d{1,2}):(\d{1,2})/");
            $replace = array($format, ($break ? "<br>" : "") . "\\1h \\2' \\3\"");
            return preg_replace($patterns, $replace, $date);
        }
        return "";
    }

    //Ham tinh so ngay , gio , phut thong qua so giay

    static function duration($duration)
    {
        $time = $duration;
        $day = floor($time / (3600 * 24));
        $hour = floor(($time % (3600 * 24)) / (3600));
        $minute = floor(($time % (3600)) / (60));
        if ($minute != 0) {
            $time = $minute . '\'';
        } else {
            $time = '';
        }

        if ($hour != 0) {
            $time = $hour . 'h' . $time;
        }
        if ($day != 0) {
            $time = $day . ' ng&#224;y ' . $time;
        }
        return $time;
    }

    /*
     * input: $time = "20:30", $date = "30/4/2013"
     * output: (int) 1433421000
     */

    static function toDateTime($time, $date)
    {
        $date = str_replace(' ', '', $date);
        $date = str_replace('-', '/', $date);
        $time = explode(':', $time);
        $date = explode('/', $date);

        $minis = '00';
        if (isset($time[2]))
            $minis = $time[2];

        return mktime($time[0], $time[1], $minis, $date[1], $date[0], $date[2]);
    }


    static function getFormatDateFromTimestamp($timestamp, $format = 'd-m-Y, H:i:s')
    {
        return date($format, $timestamp);
    }

    static function getMonthBetweenTwoTimestamp($ts1)
    {
        $year1 = date('Y', $ts1);
        $year2 = date('Y', time());

        $month1 = date('m', $ts1);
        $month2 = date('m', time());

        $diff = (($year2 - $year1) * 12) + ($month2 - $month1) + 1;
        return $diff;
    }

    static function getViewDateTime($date)
    {
        $date = str_replace(' ', '', $date);
        $date = str_replace('-', '/', $date);
        $a = "";
        $time = '';
        if (stripos($date, '/') !== false) {
            $a = explode('/', $date);
        } elseif (stripos($date, '-') !== false) {
            $a = explode('-', $date);
        }
        if (!isset($a['0']) || !is_numeric($a['0']))
            $a['0'] = 0;
        if (!isset($a['1']) || !is_numeric($a['1']))
            $a['1'] = 0;
        if (!isset($a['2']) || !is_numeric($a['2']))
            $a['2'] = 0;
        if (sizeof($a) == 3 and checkdate($a[1], $a[0], $a[2])) {
//            return mktime(23, 59, 59, $a['1'], $a['0'], $a['2']);
            $time = 'Ngày ' . $a['0'] . ' tháng ' . $a['1'] . ' năm ' . $a['2'];
        }

        return $time;
    }

    static function getDayBetweenTwoTimestamp($ts1)
    {
        $datediff = time() - $ts1;
        $day = ceil($datediff / (60 * 60 * 24));
        return $day;
    }

    static function getSubDateFromNow($sub_day)
    {
        $d = date('d', time());
        $m = date('m', time());
        $y = date('Y', time());
        if ($d == 1) {
            if ($m == 1) {
                $sub_m = 12;
                $sub_y = $y - 1;
            } else {
                $sub_m = $m - 1;
                $sub_y = $y;
            }
            $sub_d = $d - $sub_day;
        } else {
            $sub_d = $d - $sub_day;
            if ($sub_d > 0) {
                $sub_m = $m;
                $sub_y = $y;
            } else {
                $sub_m = $m - 1;
                if ($sub_m < 0) {
                    $sub_y = $y - 1;
                    $sub_m = 12;
                } else {
                    $sub_y = $y;
                }
            }
        }
//        var_dump($sub_d);
        $num_day = cal_days_in_month(CAL_GREGORIAN, $sub_m, $sub_y);
        if ($sub_d < 0) {
            $sub_d = $num_day + $sub_day;
        }
        $day = $sub_d + 1 . '/' . $sub_m . '/' . $sub_y;
        return $day;
    }
}

?>