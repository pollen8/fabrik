<?php

  class OP_API_Exception extends Exception
  {
  }

  class OP_API
  {
    protected $url = null;
    protected $error = null;
    protected $timeout = null;
    protected $debug = null;
    static public $encoding = 'UTF-8';

    public function __construct ($url, $timeout = 1000)
    {
      $this->url = $url;
      $this->timeout = $timeout;
    }

    public function setDebug ($v)
    {
      $this->debug = $v;
      return $this;
    }

    public function process (OP_Request $r)
    {
      if ($this->debug) {
        echo $r->getRaw() . "\n";
      }

      $msg = $r->getRaw();
      $str = $this->_send($msg);
      if (!$str) {
        throw new OP_API_Exception ('Bad reply');
      }
      if ($this->debug) {
        echo $str . "\n";
      }
      return new OP_Reply($str);
    }

    static function encode ($str)
    {
      $ret = @htmlentities($str, null, OP_API::$encoding);
      if (strlen($str) && !strlen($ret)) {
        $str = iconv('ISO-8859-1', 'UTF-8', $str);
        $ret = htmlentities($str, null, OP_API::$encoding);
      }
      return $ret;
    }

    static function decode ($str)
    {
      return html_entity_decode($str, null, OP_API::$encoding);
    }

    static function createRequest ($xmlStr = null)
    {
      return new OP_Request ($xmlStr);
    }

    static function createReply ($xmlStr = null)
    {
      return new OP_Reply ($xmlStr);
    }

    protected function _send ($str)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
      curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
      $ret = curl_exec ($ch);
      $errno = curl_errno($ch);
      $this->error = $error = curl_error($ch);
      curl_close ($ch);

      if ($errno) {
          error_log("CURL error. Code: $errno, Message: $error");
          return false;
      } else {
          return $ret;
      }
    }

    // convert SimpleXML to PhpObj
    public static function convertXmlToPhpObj ($node)
    {
      $ret = array();

      if (is_object($node) && $node->hasChildNodes()) {
        foreach ($node->childNodes as $child) {
          $name = self::decode($child->nodeName);
          if ($child->nodeType == XML_TEXT_NODE) {
            $ret = self::decode($child->nodeValue);
          } else {
            if ('array' === $name) {
              return self::parseArray($child);
            } else {
              $ret[$name] = self::convertXmlToPhpObj($child);
            }
          }
        }
      }
      return 0 < count($ret) ? $ret : null;
    }

    // parse array
    protected static function parseArray ($node)
    {
      $ret = array();
      foreach ($node->childNodes as $child) {
        $name = self::decode($child->nodeName);
        if ('item' !== $name) {
          throw new OP_API_Exception('Wrong message format');
        }
        $ret[] = self::convertXmlToPhpObj($child);
      }
      return $ret;
    }

    /**
     * converts php-structure to DOM-object.
     *
     * @param array $arr php-structure
     * @param SimpleXMLElement $node parent node where new element to attach
     * @param DOMDocument $dom DOMDocument object
     * @return SimpleXMLElement
     */
    public static function convertPhpObjToDom ($arr, $node, $dom)
    {
      if (is_array($arr)) {
        /**
         * If arr has integer keys, this php-array must be converted in
         * xml-array representation (<array><item>..</item>..</array>)
         */
        $arrayParam = array();
        foreach ($arr as $k => $v) {
          if (is_integer($k)) {
            $arrayParam[] = $v;
          }
        }
        if (0 < count($arrayParam)) {
          $node->appendChild($arrayDom = $dom->createElement("array"));
          foreach ($arrayParam as $key => $val) {
            $new = $arrayDom->appendChild($dom->createElement('item'));
            self::convertPhpObjToDom($val, $new, $dom);
          }
        } else {
          foreach ($arr as $key => $val) {
            $new = $node->appendChild(
              $dom->createElement(self::encode($key))
            );
            self::convertPhpObjToDom($val, $new, $dom);
          }
        }
      } else {
        $node->appendChild($dom->createTextNode(self::encode($arr)));
      }
    }
  }

  class OP_Request
  {
    protected $cmd = null;
    protected $args = null;
    protected $username = null;
    protected $password = null;
    protected $token = null;
    protected $ip = null;
    protected $language = null;
    protected $raw = null;

    public function __construct ($str = null)
    {
      if ($str) {
        $this->raw = $str;
        $this->_parseRequest($str);
      }
    }

    /*
     * Parse request string to assign object properties with command name and
     * arguments structure
     *
     * @return void
     *
     * @uses OP_Request::__construct()
     */
    protected function _parseRequest ($str = "")
    {
      $dom = new DOMDocument;
      $dom->loadXML($str);
      $arr = OP_API::convertXmlToPhpObj($dom->documentElement);
      list($dummy, $credentials) = each($arr);
      list($this->cmd, $this->args) = each($arr);
      $this->username = $credentials['username'];
      $this->password = $credentials['password'];
      $this->token = isset($credentials['token']) ? $credentials['token'] : null;
      $this->ip = isset($credentials['ip']) ? $credentials['ip'] : null;
      if (isset($credentials['language'])) {
        $this->language = $credentials['language'];
      }
    }

    public function setCommand ($v)
    {
      $this->cmd = $v;
      return $this;
    }

    public function getCommand ()
    {
      return $this->cmd;
    }

    public function setLanguage ($v)
    {
      $this->language = $v;
      return $this;
    }

    public function getLanguage ()
    {
      return $this->language;
    }

    public function setArgs ($v)
    {
      $this->args = $v;
      return $this;
    }

    public function getArgs ()
    {
      return $this->args;
    }

    public function setAuth ($args)
    {
      $this->username = isset($args["username"]) ? $args["username"] : null;
      $this->password = isset($args["password"]) ? $args["password"] : null;
      $this->token = isset($args["token"]) ? $args["token"] : null;
      $this->ip = isset($args["ip"]) ? $args["ip"] : null;
      return $this;
    }

    public function getAuth ()
    {
      return array(
        "username" => $this->username,
        "password" => $this->password,
        "token" => $this->token,
        "ip" => $this->ip
      );
    }

    public function getRaw ()
    {
      if (!$this->raw) {
        $this->raw .= $this->_getRequest();
      }
      return $this->raw;
    }

    public function _getRequest ()
    {
      $dom = new DOMDocument('1.0', OP_API::$encoding);

      $credentialsElement = $dom->createElement('credentials');
      $usernameElement = $dom->createElement('username');
      $usernameElement->appendChild(
        $dom->createTextNode(OP_API::encode($this->username))
      );
      $credentialsElement->appendChild($usernameElement);

      $passwordElement = $dom->createElement('password');
      $passwordElement->appendChild(
        $dom->createTextNode(OP_API::encode($this->password))
      );
      $credentialsElement->appendChild($passwordElement);

      if (isset($this->language)) {
        $languageElement = $dom->createElement('language');
        $languageElement->appendChild($dom->createTextNode($this->language));
        $credentialsElement->appendChild($languageElement);
      }

      if (isset($this->token)) {
        $tokenElement = $dom->createElement('token');
        $tokenElement->appendChild($dom->createTextNode($this->token));
        $credentialsElement->appendChild($tokenElement);
      }

      if (isset($this->ip)) {
        $ipElement = $dom->createElement('ip');
        $ipElement->appendChild($dom->createTextNode($this->ip));
        $credentialsElement->appendChild($ipElement);
      }

      $rootElement = $dom->createElement('openXML');
      $rootElement->appendChild($credentialsElement);

      $rootNode = $dom->appendChild($rootElement);
      $cmdNode = $rootNode->appendChild(
        $dom->createElement($this->getCommand())
      );
      OP_API::convertPhpObjToDom($this->args, $cmdNode, $dom);

      return $dom->saveXML();
    }
  }

  class OP_Reply
  {
    protected $faultCode = 0;
    protected $faultString = null;
    protected $value = array();
    protected $raw = null;
    public function __construct ($str = null) {
      if ($str) {
        $this->raw = $str;
        $this->_parseReply($str);
      }
    }

    protected function _parseReply ($str = "")
    {
      $dom = new DOMDocument;
      $dom->loadXML($str);
      $arr = OP_API::convertXmlToPhpObj($dom->documentElement);
      $this->faultCode = (int) $arr['reply']['code'];
      $this->faultString = $arr['reply']['desc'];
      $this->value = $arr['reply']['data'];
    }

    public function setFaultCode ($v)
    {
      $this->faultCode = $v;
      return $this;
    }

    public function setFaultString ($v)
    {
      $this->faultString = $v;
      return $this;
    }

    public function setValue ($v)
    {
      $this->value = $v;
      return $this;
    }

    public function getValue ()
    {
      return $this->value;
    }

    public function getFaultString ()
    {
      return $this->faultString;
    }

    public function getFaultCode ()
    {
      return $this->faultCode;
    }

    public function getRaw ()
    {
      if (!$this->raw) {
        $this->raw .= $this->_getReply ();
      }
      return $this->raw;
    }

    public function _getReply ()
    {
      $dom = new DOMDocument('1.0', OP_API::$encoding);
      $rootNode = $dom->appendChild($dom->createElement('openXML'));
      $replyNode = $rootNode->appendChild($dom->createElement('reply'));
      $codeNode = $replyNode->appendChild($dom->createElement('code'));
      $codeNode->appendChild($dom->createTextNode($this->faultCode));
      $descNode = $replyNode->appendChild($dom->createElement('desc'));
      $descNode->appendChild(
        $dom->createTextNode(OP_API::encode($this->faultString))
      );
      $dataNode = $replyNode->appendChild($dom->createElement('data'));
      OP_API::convertPhpObjToDom($this->value, $dataNode, $dom);
      return $dom->saveXML();
    }
  }

?>