<?php


namespace common\models\form;


use merchant\models\form\LanguageBasicForm;

class LinkCardForm extends LanguageBasicForm
{
    public $id;
    public $token_cybersource;
    public $token_merchant;
    public $merchant_id;
    public $card_holder;
    public $card_number_mask;
    public $card_number_md5;
    public $card_type;
    public $customer_email;
    public $customer_mobile;
    public $bank;
    public $secure_type;
    public $partner_payment_id;
    public $verify_amount;
    public $status;
    public $time_created;
    public $time_updated;
    public $time_verified;
    public $user_action;
    public $iv;
    public $info;

    public function rules()
    {
        return [
            [['id', 'merchant_id', 'card_type', 'secure_type', 'partner_payment_id', 'verify_amount', 'status', 'time_created', 'time_updated', 'time_verified'], 'integer'],
            [['card_number_mask', 'customer_mobile'], 'string', 'max' => 20],
            [['token_cybersource', 'token_merchant'], 'string', 'max' => 30],
            [['card_number_md5', 'customer_email', 'iv'], 'string', 'max' => 50],
            [['card_holder', '	bank'], 'string', 'max' => 100],
            [['info'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token_cybersource' => 'Mã token Cybersource',
            'token_merchant' => 'Mã token Merchant',
            'merchant_id' => 'Mã Merchant',
            'card_holder' => 'Tên chủ thẻ',
            'card_number_mask' => 'Số thẻ đã được mark',
            'card_number_md5' => 'Số thẻ đã được mã hoá',
            'card_type' => 'Loại thẻ',
            'customer_email' => 'Email khách hàng',
            'customer_mobile' => 'Số điện thoại khách hàng',
            'bank' => 'Ngân hàng phát hành thẻ',
            'secure_type' => 'Loại thẻ visa/master',
            'partner_payment_id' => 'Mã nhà cung cấp',
            'verify_amount' => 'Số tiền xác thực',
            'status' => 'Trạng thái',
            'time_created' => 'Thời gian tạo',
            'time_updated' => 'Thời gian cập nhập',
            'time_verified' => 'Thời gian xác thực',
            'user_action' => 'Nhân viên xử lý',
        ];
    }
}