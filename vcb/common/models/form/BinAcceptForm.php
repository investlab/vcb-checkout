<?php


namespace common\models\form;


use common\components\utils\Strings;
use common\components\utils\Validation;
use common\models\business\BinAcceptBusiness;
use common\models\db\BinAccept;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class BinAcceptForm extends LanguageBasicForm
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_UPDATE = 'update';

    public $id;
    public $bin_code;
    public $card_type;
    public $status;
    public $scenario;

    public function rules()
    {
        return [
            [['bin_code'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['card_type', 'status'], 'required', 'message' => 'Bạn phải chọn {attribute}.'],
            [['id', 'status'], 'integer'],
            [['card_type'], 'string', 'max' => 50],
            ['bin_code', 'checkExits'],
            ['bin_code', 'checkBinCode'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bin_code' => 'Đầu bin',
            'card_type' => 'Loại thẻ',
            'status' => 'Trạng thái'
        ];
    }

    public function checkExits($attribute, $param)
    {
        if ($this->scenario == self::SCENARIO_UPDATE) {
            $bin_accept = BinAcceptBusiness::getById($this->id);

            if (!empty($bin_accept)) {
                if ($bin_accept->$attribute != $this->$attribute) {
                    $bin = BinAccept::findOne(['bin_code' => $this->$attribute]);
                    if (!empty($bin)) {
                        $this->addError($attribute, 'Đầu bin đã tồn tại.');
                    }
                }
            }
        } elseif ($this->scenario == self::SCENARIO_ADD) {
            $bin_accept = BinAccept::findOne(['bin_code' => $this->$attribute]);

            if (!empty($bin_accept)) {
                $this->addError($attribute, 'Đầu bin đã tồn tại.');
            }
        }
    }

    public function checkBinCode($attribute, $param)
    {
         $count_number = strlen($this->bin_code);

        if ($count_number < 6 || $count_number >8) {
            $this->addError($attribute, 'Đầu bin không hợp lệ( từ 6 đến 8 số).');
        }
    }

}