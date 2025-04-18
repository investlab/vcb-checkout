<?php


namespace common\models\form;

use common\models\db\Branch;
use merchant\models\form\LanguageBasicForm;
use yii\base\Model;

class BranchForm extends LanguageBasicForm
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_UPDATE = 'update';

    public $id;
    public $name;
    public $city;
    public $status;
    public $time_created;
    public $time_updated;
    public $user_created;
    public $user_updated;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','city'], 'required', 'message' => 'Bạn phải nhập {attribute}.'],
            [['id', 'status', 'time_created', 'time_updated', 'user_created', 'user_updated'], 'integer', 'message' => '{attribute} không đúng định dạng.'],
            [['name', 'city'], 'string', 'max' => 255, 'message' => '{attribute} không đúng định dạng.'],
            [['name'], 'checkExisted', 'on' => [self::SCENARIO_ADD, self::SCENARIO_UPDATE]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên chi nhánh',
            'city' => 'Tỉnh/Thành phố',
            'status' => 'Trạng thái',
            'time_created' => 'Thời gian tạo',
            'time_updated' => 'Thời gian cập nhật',
            'user_created' => 'Người tạo',
            'user_updated' => 'Người cập nhật',
        ];
    }

    public function checkExisted($attribute, $params)
    {
        if ($this->scenario == self::SCENARIO_ADD) {
            $check_existed = Branch::find()->where([$attribute => $this->$attribute])->one();
            if (!is_null($check_existed)) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' đã tồn tại');
            }
        } elseif ($this->scenario == self::SCENARIO_UPDATE) {
            $check_existed = Branch::find()->where([$attribute=> $this->$attribute])->andWhere(['not', ['id' => $this->id]])->one();
            if (!is_null($check_existed)) {
                $this->addError($attribute, $this->getAttributeLabel($attribute) . ' đã tồn tại');
            }
        }
    }
}