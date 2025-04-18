<?php
namespace common\util;
class TextUtil
{

    public static function generateRandomString($length = 10)
    {
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function cookie($name, $value = null, $expire = null)
    {
        if (false === $value)
            \Yii::$app->response->cookies->remove($name);
        elseif ($value == null) {
            return \Yii::$app->request->cookies->getValue($name);
        }
        $options['name'] = $name;
        $options['value'] = $value;
        $options['expire'] = $expire ?: time() + 86400 * 30;
        $cookie = new \yii\web\Cookie($options);
        \Yii::$app->response->cookies->add($cookie);
    }


    /**
     * @param int $amount
     * @param $interestRate
     * @param $month
     * @return float
     *
     * Tính số tiền phải trả hàng tháng của khách hàng
     *
     */
    public static function calcFee($amount = 0, $interestRate, $month)
    {
        $temp = 1;
        for ($i = 1; $i <= $month; $i++) {
            $temp *= (1 + $interestRate / 100);
        }
        return round(($amount * $interestRate / 100) / (1 - 1 / $temp) / 1000) * 1000;

    }


    /**
     * Tao 1 link excel
     */
    public static function buidLinkExcel($action)
    {
        $url = \Yii::$app->request->url;
        $params = explode('?', $url);
        $params = isset($params[1]) && !empty($params) ? '?' . $params[1] : '';
        return 'excel/' . $action . '/' . $params;
    }


    /**
     * Show var_dump
     */
    public static function deBug($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '<pre>';
        die;
    }

} 