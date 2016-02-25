<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */


/**
 * @namespace
 */
namespace Pop\Shipping\Adapter;

/**
 * GSO shipping adapter class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com> Rob Clayburn <fabrikar@gmail.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Gso extends AbstractAdapter
{
    /**
     * SOAP Client
     * @var \SoapClient
     */
    protected $client = null;

    /**
     * Gso WSDL File
     * @var string
     */
    protected $wsdl = 'https://wsa.gso.com/gsoshipws1.0/gsoshipws.asmx?WSDL';

    /**
     * Request array
     * @var array
     */
    protected $request = null;

    /**
     * Package weight
     * @var array
     */
    protected $weight = [
        'Value' => null,
        'Units' => 'LB'
    ];

    /**
     * Constructor
     *
     * Method to instantiate an Gso shipping adapter object
     *
     * @param  string $username
     * @param  string $password
     * @param  string $accountNumber
     */
    public function __construct($username, $password, $accountNumber)
    {
        ini_set('soap.wsdl_cache_enabled', '0');

        $this->client = new \SoapClient($this->wsdl, ['trace' => 1]);

        $auth = array(
            'UserName' => $username,
            'Password' => $password
        );
        $header = new \SoapHeader('http://gso.com/GsoShipWS', 'AuthenticationHeader', $auth);
        $this->client->__setSoapHeaders($header);

       $this->request['GetShippingRateRequest'] = [
                'AccountNumber' => $accountNumber
        ];
    }

    /**
     * Static method to get the services
     *
     * @return array
     */
    public static function getServices()
    {
        return self::$services;
    }

    /**
     * Set ship to
     *
     * @param  array  $shipTo
     * @return mixed
     */
    public function shipTo(array $shipTo)
    {
        $this->request['GetShippingRateRequest']['DestinationZip'] = $shipTo['zip'];
    }

    /**
     * Set ship from
     *
     * @param  array  $shipFrom
     * @return mixed
     */
    public function shipFrom(array $shipFrom)
    {
       $this->request['GetShippingRateRequest']['OriginZip'] = $shipFrom['zip'];
    }

    /**
     * Set dimensions
     *
     * @param  array  $dimensions
     * @param  string $unit
     * @return mixed
     */
    public function setDimensions(array $dimensions, $unit = null)
    {
    }

    /**
     * Set dimensions
     *
     * @param  string $weight
     * @param  string $unit
     * @return mixed
     */
    public function setWeight($weight, $unit = null)
    {
        if ((null !== $unit) && (($unit == 'LB') || ($unit == 'KG'))) {
            $this->weight['Units'] = $unit;
        }

        $this->weight['Value'] = $weight;
    }

    /**
     * Send transaction
     *
     * @return void
     */
    public function send()
    {
        $this->request['GetShippingRateRequest']['PackageWeight'] = $this->weight['Value'];

        $this->request['GetShippingRateRequest']['DeclaredValue'] = 2.99;
        $this->request['GetShippingRateRequest']['CODValue'] = 1.44;

        $this->response = $this->client->GetShippingRate($this->request);

        print_r($this->response);exit;
        $this->responseCode = (int)$this->response->Notifications->Code;
        $this->responseMessage = (string)$this->response->Notifications->Message;

        if ($this->responseCode == 0) {
            foreach ($this->response->RateReplyDetails as $rate) {
                $this->rates[self::$services[(string)$rate->ServiceType]] = number_format((string)$rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2);
            }
            $this->rates = array_reverse($this->rates, true);
        }
    }

    /**
     * Return whether the transaction is a success
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return ($this->responseCode == 0);
    }

    /**
     * Return whether the transaction is an error
     *
     * @return boolean
     */
    public function isError()
    {
        return ($this->responseCode != 0);
    }

}
