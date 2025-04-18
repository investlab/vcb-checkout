<?php

namespace common\models\business;

use common\components\libs\Tables;
use common\models\db\MerchantConfig;

class MerchantConfigBusiness
{
    /**
     * @param $merchant_id
     * @return array
     */
    public static function getConfigByMerchantId($merchant_id): array
    {
        $result = [];
        $merchant_configs = Tables::selectAllBySql('SELECT mcft.`key`, mcf.`value` FROM merchant_config AS mcf INNER JOIN merchant_config_type AS mcft ON mcf.merchant_config_type_id = mcft.id  WHERE merchant_id = ' . $merchant_id . ' AND `status` = ' . MerchantConfig::STATUS_ACTIVE);


        if (!empty($merchant_configs)) {
            foreach ($merchant_configs as $merchant_config) {
                $result[$merchant_config['key']] = $merchant_config['value'];
            }
        }
        return $result;
    }

    public static function getMerchantEnableByKey($key): array
    {
        $result = [];
        $merchants = Tables::selectAllBySql("select mccf.merchant_id as merchant_id
from merchant_config AS mccf
         INNER JOIN merchant_config_type mccft ON mccf.merchant_config_type_id = mccft.id
WHERE mccft.`key` = '" . $key . "'
  AND mccf.status = " . MerchantConfig::STATUS_ACTIVE);
        if (!empty($merchants)) {
            foreach ($merchants as $key => $merchant) {
                $result[] = $merchant['merchant_id'];
            }
        }
        return $result;
    }
}