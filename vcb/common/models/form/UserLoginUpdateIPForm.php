<?php
/**
 * Created by PhpStorm.
 * User: THUY
 * Date: 5/29/2018
 * Time: 14:56
 */

namespace common\models\form;


use common\models\db\UserLogin;

class UserLoginUpdateIPForm extends UserLogin
{
    public $user_login_id;
    public $ips;

    public function rules()
    {
        return [
            [['user_login_id'], 'integer'],
            [['ips'], 'safe'],
            [['ips'], 'checkValidate'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'merchant_id' => 'Merchant',
            'new_password' => 'Mật khẩu mới'
        ];
    }

    public function checkValidate($attribute, $param)
    {
        switch ($attribute) {
            case "ips":
                $ips = str_replace(' ', '', $this->ips);
                $ips_arr = explode(',', $ips);
                $check_ips = true;

                foreach ($ips_arr as $k => $v) {
                    if (is_int(intval($v)) == false) {
                        $check_ips = false;
                    }
                }

                if ($check_ips == false) {
                    $this->addError('ips', 'Dải IP không hợp lệ');
                }
                break;
        }
    }

} 