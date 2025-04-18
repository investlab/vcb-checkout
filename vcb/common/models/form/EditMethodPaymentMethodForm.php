<?php


namespace common\models\form;

use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BankBusiness;
use common\models\business\MethodBusiness;
use common\models\db\Bank;
use common\models\db\Method;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class EditMethodPaymentMethodForm extends LanguageBasicForm
{

    public $payment_id;

    public function rules()
    {
        return [
            [['payment_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'payment_id' => 'ID hình thức thanh toán',
        ];
    }
}