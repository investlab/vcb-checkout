<?php

/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 3/30/2016
 * Time: 4:21 PM
 */

namespace common\models\business;

use Yii;

//use yii\swiftmailer\Mailer;
class SendMailBussiness
{

    public static function send($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
    }

    public static function sendBCC($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array(), $bcc = [])
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setBcc($bcc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
    }

    public static function sendWithResult($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
        return $a;
    }

    public static function sendSuccess($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
        return $a;

    }

    public static function sendBCA($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom(['no-reply-vietcombank@nganluong.vn' => 'Vietcombank Payment Gateway'])
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
    }

    public static function sendBCAWithResult($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom(['no-reply-vietcombank@nganluong.vn' => 'Vietcombank Payment Gateway'])
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
        return $a;
    }

    public static function sendSuccessBCA($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom(['no-reply-vietcombank@nganluong.vn' => 'Vietcombank Payment Gateway'])
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
        return $a;
    }

    public static function sendApp($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array())
    {
        $a = Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
    }

    public static function sendTo($from, $subject, $template, $vars = array(), $layout = 'layouts/basic')
    {
        Yii::$app->mailer->compose()
            ->setTo("noreply@nganluong.vn")
            ->setFrom($from)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->send();
    }

    public static function sendByContent($to, $from, $subject, $content, $cc = array())
    {
        Yii::$app->mailer->compose()
            ->setTo($to)
            ->setFrom($from)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody($content)
            ->send();
    }

    public static function render($template, $vars, $layout)
    {
        //$mailer = new Mailer();
        Yii::$app->mailer->setViewPath("@common/mail");
        return Yii::$app->mailer->render($template, $vars, $layout);
    }

    public static function sendAttach($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $file_attach, $cc = array())
    {
        return Yii::$app->mailer->compose()
            ->setTo($to)
            ->setCc($cc)
//            ->setFrom([MAILER_USERNAME => "Cổng thanh toán Vietcombank"])
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->attach($file_attach)
            ->send();
    }

    public static function sendCDTG($to, $subject, $template, $vars = array(), $layout = 'layouts/basic', $cc = array(), $file_attach)
    {
        return Yii::$app->mailer->compose()
            ->setfrom('no-reply-vietcombank@nganluong.vn', 'Cổng thanh toán Vietcombank')
            ->setTo($to)
            ->setCc($cc)
            ->setSubject($subject)
            ->setHtmlBody(self::render($template, $vars, $layout))
            ->attach($file_attach)
            ->send();
    }

}
