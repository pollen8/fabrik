<?php
/**
 * First Data Payeezy Abstract Request
 */

namespace Omnipay\FirstData\Message;

/**
 * First Data Payeezy Abstract Request
 */
abstract class PayeezyAbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /** API version to use. See the note about the hashing requirements for v12 or higher. */
    const API_VERSION = 'v11';

    /** @var string live endpoint URL base */
    protected $liveEndpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/';

    /** @var string test endpoint URL base */
    protected $testEndpoint = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/';

    /** @var int api transaction type */
    protected $transactionType = '00';

    //
    // Transaction types
    //
    const TRAN_PURCHASE                 = '00';
    const TRAN_PREAUTH                  = '01';
    const TRAN_PREAUTHCOMPLETE          = '02';
    const TRAN_FORCEDPOST               = '03';
    const TRAN_REFUND                   = '04';
    const TRAN_PREAUTHONLY              = '05';
    const TRAN_PAYPALORDER              = '07';
    const TRAN_VOID                     = '13';
    const TRAN_TAGGEDPREAUTHCOMPLETE    = '32';
    const TRAN_TAGGEDVOID               = '33';
    const TRAN_TAGGEDREFUND             = '34';
    const TRAN_CASHOUT                  = '83';
    const TRAN_ACTIVATION               = '85';
    const TRAN_BALANCEINQUIRY           = '86';
    const TRAN_RELOAD                   = '88';
    const TRAN_DEACTIVATION             = '89';

    /** @var array Names of the credit card types. */
    protected static $cardTypes = array(
        'visa'        => 'Visa',
        'mastercard'  => 'Mastercard',
        'discover'    => 'Discover',
        'amex'        => 'American Express',
        'diners_club' => 'Diners Club',
        'jcb'         => 'JCB',
    );

    /**
     * Get Gateway ID
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getGatewayId()
    {
        return $this->getParameter('gatewayid');
    }

    /**
     * Set Gateway ID
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setGatewayId($value)
    {
        return $this->setParameter('gatewayid', $value);
    }

    /**
     * Get Password
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Set Password
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Set transaction type
     *
     * @param int $transactionType
     * @return PayeezyAbstractRequest provides a fluent interface.
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * Get transaction type
     *
     * @return int
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Get the base transaction data.
     *
     * @return array
     */
    protected function getBaseData()
    {
        $data = array();
        $data['gateway_id'] = $this->getGatewayID();
        $data['password'] = $this->getPassword();
        $data['transaction_type'] = $this->getTransactionType();

        return $data;
    }

    /**
     * Get the transaction headers.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return array(
            'Content-Type: application/json; charset=UTF-8;',
            'Accept: application/json'
        );
    }

    /**
     * Get the card type name, from the card type code.
     *
     * @param string $type
     * @return string
     */
    public static function getCardType($type)
    {
        if (isset(self::$cardTypes[$type])) {
            return self::$cardTypes[$type];
        }
        return $type;
    }

    /**
     * Get the AVS Hash.
     *
     * Important Note about v12 or higher of the Web Service API: Merchants wishing to use
     * V12 or higher of the API must implement the API HMAC hash security calculation.
     * Further information on this subject can be found at the link below.
     *
     * @link https://support.payeezy.com/entries/22069302-api-security-hmac-hash
     *
     * @return string
     */
    public function getAVSHash()
    {
        $parts = array();
        $parts[] = $this->getCard()->getAddress1();
        $parts[] = $this->getCard()->getPostcode();
        $parts[] = $this->getCard()->getCity();
        $parts[] = $this->getCard()->getState();
        $parts[] = $this->getCard()->getCountry();
        return implode('|', $parts);
    }

    public function getData()
    {
        $this->setTransactionType($this->action);
        $data = $this->getBaseData();
        return $data;
    }

    public function sendData($data)
    {
        $client = $this->httpClient->post(
            $this->getEndpoint(),
            $this->getHeaders(),
            $data
        );
        $client->getCurlOptions()->set(CURLOPT_PORT, 443);
        $httpResponse = $client->send();
        return $this->createResponse($httpResponse->getBody());
    }

    /**
     * Get the endpoint URL for the request.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint.self::API_VERSION : $this->liveEndpoint.self::API_VERSION;
    }

    /**
     * Create the response object.
     *
     * @param $data
     * @return PayeezyResponse
     */
    protected function createResponse($data)
    {
        return $this->response = new PayeezyResponse($this, $data);
    }
}
