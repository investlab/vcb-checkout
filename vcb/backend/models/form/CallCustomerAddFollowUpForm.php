<?php

namespace backend\models\form;

use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;
use common\models\business\CallFollowUpPoolBusiness;

class CallCustomerAddFollowUpForm extends CallCustomerBasicForm {

    public $time_appointment = null;
    public $note = null;
    public $title = 'Đặt hẹn';

    public function rules() {
        return [
            [['time_appointment', 'note'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['time_appointment'], 'isDateTime', 'message' => '{attribute} không hợp lệ.'],
            [['note'], 'string', 'message' => '{attribute} không hợp lệ.'],
        ];
    }

    public function attributeLabels() {
        return array(
            'time_appointment' => 'Thời gian hẹn',
            'note' => 'Nội dung hẹn',
        );
    }

    public function isDateTime($attribute, $params) {
        if (!preg_match('/^\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}:\d{1,2}$/', $this->$attribute)) {
            $this->addError($attribute, 'Thời gian hẹn không hợp lệ');
        }
    }

    public function submit() {
        $time_appointment = Yii::$app->formatter->asTimestamp($this->time_appointment);
        $inputs = array(
            'call_history_id' => $this->call_history_info['id'],
            'time_appointment' => $time_appointment,
            'note' => $this->note,
            'user_id' => Yii::$app->user->getId(),
        );
        $result = CallFollowUpPoolBusiness::addByCallHistoryAndUpdateQueue($inputs);
        if ($result['error_message'] == '') {
            $this->addMessage('Đặt hẹn thành công');
            $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index', 'option' => $this->key, 'call_history_id' => $this->call_history_info['id'], 'customer_id' => $this->customer_info['id']]);
            header('Location:' . $url);
            die();
        } else {
            $this->error = $result['error_message'];
        }
    }

}
