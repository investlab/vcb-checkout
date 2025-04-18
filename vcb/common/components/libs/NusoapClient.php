<?php

namespace common\components\libs;

use yii\base\Component;
use yii\base\InvalidConfigException;

class NusoapClient extends Component {

    public $url;
    public $options = [];
    private $_client;
    public $answer;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->url === null) {
            throw new InvalidConfigException('The "url" property must be set.');
        }
        require_once ROOT_PATH . DS. 'common' . DS . 'components' . DS . 'libs' . DS . 'nusoap' . DS . 'nusoap.php';
        $this->_client = new \nusoap_client($this->url, true);

        $error = $this->_client->getError();
        if ($error) {
            return $error;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function call($name, $arguments) {
        $answer = $this->_client->call($name, $arguments);
        $error = $this->_client->getError();
        /*echo '<h2>Request</h2><pre>' . htmlspecialchars($this->_client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($this->_client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->_client->debug_str, ENT_QUOTES) . '</pre>';*/
        if ($error) {
            return false;
            //return ("Error: {$error}\n" . $this->_client->response . $this->_client->getDebug());
        } else {
            return $answer;
        }
    }

}
