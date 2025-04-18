<?php
/**
 * Created by PhpStorm.
 * User: ndang
 * Date: 23/08/2017
 * Time: 4:37 CH
 */

namespace common\components\libs;
require_once ROOT_PATH . '/vendor/facebook/graph-sdk/src/Facebook/autoload.php';

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Yii;


class FacebookApi
{
    public static function getLoginUrl($type)
    {
        $appId = FACEBOOK_APP_ID; //Facebook App ID
        $appSecret = FACEBOOK_APP_SECRET; //Facebook App Secret
        $redirectURL = FACEBOOK_REDIRECT_URL; //Callback URL
        $fbPermissions = array('email', 'public_profile', 'user_friends');  //Optional permissions
        if ($type == 'Bill') {
            $redirectURL = $redirectURL . '?check_return=1';
        }
        if ($type == 'Frontend') {
            $redirectURL = $redirectURL . '?check_return=0';
        }
        $fb = new Facebook(array(
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.10',
        ));
        $helper = $fb->getRedirectLoginHelper();

        return $helper->getLoginUrl($redirectURL, $fbPermissions);
    }

    public static function getUser()
    {
        $session = Yii::$app->session;

        $appId = FACEBOOK_APP_ID; //Facebook App ID
        $appSecret = FACEBOOK_APP_SECRET; //Facebook App Secret
        $redirectURL = FACEBOOK_REDIRECT_URL; //Callback URL
        $fbPermissions = array('email', 'public_profile', 'user_friends');  //Optional permissions
        $fb = new Facebook(array(
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.10',
        ));
        $helper = $fb->getRedirectLoginHelper();

        $errormsg = '';
        $check_code = false;

        try {
            if (isset($session['facebook_access_token'])) {
                $accessToken = $session['facebook_access_token'];
            } else {
                $accessToken = $helper->getAccessToken();
            }
        } catch (FacebookResponseException $e) {
            $errormsg = 'Graph returned an error: ' . $e->getMessage();
//            exit;
        } catch (FacebookSDKException $e) {
            $errormsg = 'Facebook SDK returned an error: ' . $e->getMessage();
//            exit;
        }

        if (isset($accessToken)) {
            if (isset($session['facebook_access_token'])) {
                $fb->setDefaultAccessToken($session['facebook_access_token']);
            } else {
                // Put short-lived access token in session
                $session['facebook_access_token'] = (string)$accessToken;

                // OAuth 2.0 client handler helps to manage access tokens
                $oAuth2Client = $fb->getOAuth2Client();

                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($session['facebook_access_token']);
                $session['facebook_access_token'] = (string)$longLivedAccessToken;

                // Set default access token to be used in script
                $fb->setDefaultAccessToken($session['facebook_access_token']);
            }

            // Redirect the user back to the same page if url has "code" parameter in query string
            if (isset($_GET['code'])) {
                $check_code = true;
            }

            // Getting user facebook profile info
            try {
                $profileRequest = $fb->get('/me?fields=name,first_name,birthday,last_name,email,link,gender,locale,picture');
                $fbUserProfile = $profileRequest->getGraphNode()->asArray();

                return [
                    'check_code' => $check_code,
                    'url' => $redirectURL,
                    'check_data' => true,
                    'data' => $fbUserProfile,
                ];
            } catch (FacebookResponseException $e) {
                $errormsg = 'Graph returned an error: ' . $e->getMessage();
                $session['facebook_access_token'] = '';
                // Redirect user back to app login page
                return [
                    'check_code' => $check_code,
                    'check_data' => false,
                    'errormsg' => $errormsg,
                ];
            } catch (FacebookSDKException $e) {
                $errormsg = 'Facebook SDK returned an error: ' . $e->getMessage();
                return [
                    'check_code' => $check_code,
                    'check_data' => false,
                    'errormsg' => $errormsg,
                ];
            }

            // Insert or update user data to the database
//            $fbUserData = array(
//                'oauth_provider'=> 'facebook',
//                'oauth_uid'     => $fbUserProfile['id'],
//                'first_name'    => $fbUserProfile['first_name'],
//                'last_name'     => $fbUserProfile['last_name'],
//                'email'         => $fbUserProfile['email'],
//                'gender'        => $fbUserProfile['gender'],
//                'locale'        => $fbUserProfile['locale'],
//                'picture'       => $fbUserProfile['picture']['url'],
//                'link'          => $fbUserProfile['link']
//            );

        } else {
            // Get login url
            $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
            return [
                'check_code' => $check_code,
                'check_data' => false,
                'errormsg' => $errormsg,
            ];
        }
    }

    public static function logoutUser()
    {
        $session = Yii::$app->session;
        $session['facebook_access_token'] = '';
    }
}