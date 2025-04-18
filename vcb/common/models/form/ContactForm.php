<?php


namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class ContactForm extends LanguageBasicForm
{

    public $fullname;
    public $email;
    public $address;
    public $phone;
    public $title;
    public $content;
    public $time_created;
    public $verifyCode;

    public function rules()
    {
        return [
            [['fullname', 'phone', 'email', 'content', 'verifyCode'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['time_created'], 'integer'],
            [['content', 'title'], 'string'],
            [['phone'], 'string', 'min' => 10, 'max' => 11, 'tooLong' => 'Số di động không hợp lệ.', 'tooShort' => 'Số di động không hợp lệ.'],
            [['phone'], 'match', 'pattern' => '/^(01)([0-9]{9})|(09)([0-9]{8})$/', 'message' => '{attribute} không hợp lệ.'],
            [['email'], 'email', 'message' => 'Email không hợp lệ.'],
            ['verifyCode', 'captcha', 'captchaAction' => 'site/captcha', 'message' => '{attribute} không đúng.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fullname' => 'Họ tên',
            'title' => 'Tiêu đề',
            'phone' => 'Số di động',
            'email' => 'Email',
            'address' => 'Địa chỉ',
            'content' => 'Nội dung liên hệ',
            'time_created' => 'Ngày liên hệ',
            'verifyCode' => 'Mã xác thực'
        ];
    }

} 