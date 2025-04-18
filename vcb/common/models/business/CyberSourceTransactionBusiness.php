<?php

namespace common\models\business;

use common\components\libs\NotifySystem;
use common\models\db\CyberSourceTransaction;

class CyberSourceTransactionBusiness
{
    public static function add3DsInfo($data): bool
    {
        try {
            $result = $data['data'];
            if (isset($result->payerAuthEnrollReply->eci)) {
                $eci = $result->payerAuthEnrollReply->eci;
            } elseif (isset($result->payerAuthEnrollReply->eciRaw)) {
                $eci = $result->payerAuthEnrollReply->eciRaw;
            } else {
                $eci = '';
            }
            if (isset($result->ccAuthReply->avsCode)) {
                $avs = $result->ccAuthReply->avsCode;
            } elseif (isset($result->ccAuthReply->avsCodeRaw)) {
                $avs = $result->ccAuthReply->avsCodeRaw;
            } else {
                $avs = '';
            }

            $model = new CyberSourceTransaction();
            $model->transaction_id = $data['transaction_id'];
            $model->eci = $eci;
            $model->pares_status = $result->payerAuthEnrollReply->paresStatus ?? '';
            $model->veres_enrolled = $result->payerAuthEnrollReply->veresEnrolled ?? '';
            $model->three_ds_version = $result->payerAuthEnrollReply->specificationVersion ?? '';
            $model->three_server_transaction_id = $result->payerAuthEnrollReply->threeDSServerTransactionID ?? '';
            $model->xid = $result->payerAuthEnrollReply->xid ?? '';
            $model->avs = $avs;
            $model->authentication_type = $result->payerAuthEnrollReply->authenticationType ?? '';
            if ($model->save()) {
                return true;
            } else {
                NotifySystem::send("ERROR: " . json_encode($model->getErrors()));
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }
}