<?php

namespace common\models\db;

/**
 * This is the ActiveQuery class for [[InstallmentExcludedDate]].
 *
 * @see InstallmentExcludedDate
 */
class InstallmentExcludedDateQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return InstallmentExcludedDate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return InstallmentExcludedDate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
