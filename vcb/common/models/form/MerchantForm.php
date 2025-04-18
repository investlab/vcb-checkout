<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;
use yii\base\Model;
use common\models\db\Merchant;

class MerchantForm extends LanguageBasicForm
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_UPDATE = 'update';
    
    public $id;
    public $name;
    public $partner_id;
    public $password;
    public $logo;
    public $website;
    public $email_notification;
    public $mobile_notification;
    public $url_notification;
    public $merchant_code;
    public $branch_id;
    public $active3D;
    public $payment_flow;

    public function rules()
    {
        return [
            [['name', 'password', 'merchant_code','active3D','payment_flow'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'partner_id', 'branch_id'], 'integer'],
            [['name', 'merchant_code'], 'checkExisted', 'on' => [self::SCENARIO_ADD, self::SCENARIO_UPDATE]],
            [['merchant_code'], 'checkMerchantCode'],
            [['website', 'email_notification', 'url_notification'], 'string'],
            [['password'], 'string', 'max' => 50, 'tooLong' => '{attribute} không hợp lệ(nhỏ hơn 50 kí tự)'],
            [['email_notification'], 'email', 'message' => '{attribute} không đúng định dạng.'],
            [['mobile_notification'], 'number', 'message' => '{attribute} không hợp lệ.'],
            [['mobile_notification'], 'string', 'min' => 10, 'max' => 11,
                'tooLong' => '{attribute} không hợp lệ.',
                'tooShort' => '{attribute} không hợp lệ.'],
            [['logo'], 'file', 'extensions' => ['jpg', 'jpge', 'png', 'gif']],
            [['email_notification'], 'email','message' => '{attribute} không hợp lệ'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên merchant',
            'merchant_code' => 'Mã Merchant ID',
            'partner_id' => 'Partner ID',
            'password' => 'Mật khẩu kết nối',
            'website' => 'Website',
            'email_notification' => 'Email',
            'mobile_notification' => 'Điện thoại',
            'logo' => 'Logo',
            'branch_id' => 'Mã chi nhánh',
            'active3D' => 'Bật tắt 3D',
            'payment_flow' => 'Chọn luồng thanh toán'
        ];
    }
    
    public function checkExisted($attribute, $params)
    {
        if ($this->scenario == self::SCENARIO_ADD) {
            $check_existed = Merchant::find()->where([$attribute => $this->$attribute])->one();
            if (!is_null($check_existed)) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' đã tồn tại');
            }
        } elseif ($this->scenario == self::SCENARIO_UPDATE) {
            $check_existed = Merchant::find()->where([$attribute=> $this->$attribute])->andWhere(['not', ['id' => $this->id]])->one();
            if (!is_null($check_existed)) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' đã tồn tại');
            }
        }
    }

    public function checkMerchantCode($attribute, $params) {
        if (!is_numeric($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (chỉ được nhập chữ số)');
        } else {
            if (strlen($this->$attribute) > 11) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' không đúng định dạng (không được nhập quá 11 chữ số)');
            }
        }
    }

} 