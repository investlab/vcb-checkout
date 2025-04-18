<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 23/08/2017
 * Time: 4:37 CH
 */

namespace common\components\libs;
require_once LIBS . 'login-with-google/Google/Google_Client.php';
require_once LIBS . 'login-with-google/Google/contrib/Google_Oauth2Service.php';

use Google_Client;
use Google_Oauth2Service;
use Yii;

class GoogleApi
{
    public static $appId = GOOGLE_CLIENT_ID; //Google App ID
    public static $appSecret = GOOGLE_CLIENT_SECRET; //Google App Secret

    public static function getAuthUrl($redirectURL)
    {
        $gClient = new Google_Client();
        $gClient->setClientId(self::$appId);
        $gClient->setClientSecret(self::$appSecret);
        $gClient->setRedirectUri($redirectURL);

        $google_oauthV2 = new Google_Oauth2Service($gClient);
        return $gClient->createAuthUrl();
    }

    public static function checkCode($redirectURL)
    {
        $session = Yii::$app->session;
        $gClient = new Google_Client();
        $gClient->setClientId(self::$appId);
        $gClient->setClientSecret(self::$appSecret);
        $gClient->setRedirectUri($redirectURL);

        $google_oauthV2 = new Google_Oauth2Service($gClient);

        if (isset($_REQUEST['reset'])) {
            unset($session['token']);
            $gClient->revokeToken();
            return [
                'check_gg' => false,
                'url' => $redirectURL,
            ];
        }
        if (isset($_GET['code'])) {
            $gClient->authenticate($_GET['code']);
            $session['token'] = $gClient->getAccessToken();
            return [
                'check_gg' => true,
                'url' => $redirectURL,
            ];
        }
    }

    public static function getUser($redirectURL)
    {
        $session = Yii::$app->session;
        $gClient = new Google_Client();
        $gClient->setClientId(self::$appId);
        $gClient->setClientSecret(self::$appSecret);
        $gClient->setRedirectUri($redirectURL);

//        $gClient->setDeveloperKey(self::$google_client_id);

        $google_oauthV2 = new Google_Oauth2Service($gClient);

        if (isset($session['token'])) {
            $gClient->setAccessToken($session['token']);
        }

        if ($gClient->getAccessToken()) {
            //For logged in user, get details from google using access token
            $user = $google_oauthV2->userinfo->get();
            $session['token'] = $gClient->getAccessToken();
            return [
                'check' => true,
                'data' => $user,
            ];
        } else {
            //For Guest user, get google login url
            $authUrl = $gClient->createAuthUrl();
            return [
                'check' => false,
                'url' => $authUrl,
            ];
        }
    }

    public static function logoutUser($redirectURL)
    {
        $session = Yii::$app->session;
        $gClient = new Google_Client();
        $gClient->setClientId(self::$appId);
        $gClient->setClientSecret(self::$appSecret);
        $gClient->setRedirectUri($redirectURL);
//        $gClient->setScopes();
//        $gClient->setDeveloperKey(self::$google_client_id);

        $google_oauthV2 = new Google_Oauth2Service($gClient);
        $session['token'] = '';
        $gClient->revokeToken();
    }
}