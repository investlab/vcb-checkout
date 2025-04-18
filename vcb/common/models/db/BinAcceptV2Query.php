<?php
namespace common\models\db;

/**
 * This is the ActiveQuery class for [[BinAcceptV2]].
 *
 * @see BinAcceptV2
 */
class BinAcceptV2Query extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BinAcceptV2[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BinAcceptV2|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
