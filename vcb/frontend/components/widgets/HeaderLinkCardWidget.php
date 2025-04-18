<?php

namespace frontend\components\widgets;

use Yii;
use yii\base\Widget;
use common\models\db\LinkCard;

class HeaderLinkCardWidget extends Widget
{

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $card_token_id = Yii::$app->request->get('card_token_id');
        $link_card = LinkCard::getByToken($card_token_id);
        $view = "header_link_card_widget";
        if ($link_card && $link_card['merchant_id'] == 19) {
            $view = "customs/header_link_card_widget_{$link_card['merchant_id']}";
        }
        return $this->render("$view", [
            'root_url' => ROOT_URL,
            'link_card' => $link_card
        ]);
    }

}
