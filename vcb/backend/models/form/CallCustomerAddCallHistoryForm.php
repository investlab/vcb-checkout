<?php
namespace backend\models\form;
use yii\base\Model;
use Yii;
use common\models\db\Customer;
use common\components\libs\Tables;
use common\models\business\CallHistoryBusiness;
use common\models\business\CallFollowUpPoolBusiness;
use common\models\business\CallPlanPoolBusiness;
use common\models\db\CallHistory;

class CallCustomerAddCallHistoryForm extends CallCustomerBasicForm {
    
    public $title = 'Gọi tư vấn';
    
    public function submit() {
        if ($this->call_follow_up_pool_info != false) {
            $inputs = array(
                'id' => $this->call_follow_up_pool_info['id'], 
                'call_user_id' => Yii::$app->user->getId(), 
                'user_id' => Yii::$app->user->getId(), 
            );
            $result = CallFollowUpPoolBusiness::calling($inputs);
            if ($result['error_message'] == '') {
                $id = $result['call_history_id'];
                $this->addMessage('Thực hiện cuộc gọi thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index','option'=>'form_update_call_history', 'call_history_id'=>$id]);
                header('Location:'.$url);
                die();
            } else {
                $this->error = $result['error_message'];
            }
        } elseif ($this->call_plan_pool_info != false) {
            $inputs = array(
                'id' => $this->call_plan_pool_info['id'], 
                'call_user_id' => Yii::$app->user->getId(), 
                'user_id' => Yii::$app->user->getId(), 
            );
            $result = CallPlanPoolBusiness::calling($inputs);
            if ($result['error_message'] == '') {
                $id = $result['call_history_id'];
                $this->addMessage('Thực hiện cuộc gọi thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index','option'=>'form_update_call_history', 'call_history_id'=>$id]);
                header('Location:'.$url);
                die();
            } else {
                $this->error = $result['error_message'];
            }
        } else {
            $inputs = array(
                'customer_id' => $this->customer_info['id'], 
                'call_plan_type_id' => 1, 
                'call_user_id' => Yii::$app->user->getId(), 
                'user_id' => Yii::$app->user->getId(), 
            );
            $result = CallHistoryBusiness::addAndCallForCustomer($inputs);
            if ($result['error_message'] == '') {
                $id = $result['id'];
                $this->addMessage('Thực hiện cuộc gọi thành công');
                $url = Yii::$app->urlManager->createAbsoluteUrl(['call-customer/index','option'=>'form_update_call_history', 'call_history_id'=>$id]);
                header('Location:'.$url);
                die();
            } else {
                $this->error = $result['error_message'];
            }
        }
    }
}