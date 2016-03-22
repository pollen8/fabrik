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
 * USPS shipping adapter class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Usps extends AbstractAdapter
{

    /**
     * Live API URL
     * @var string
     */
    protected $liveUrl = 'http://production.shippingapis.com/ShippingAPI.dll?API=RateV4&XML=';

    /**
     * Test API URL
     * @var string
     */
    protected $testUrl = 'http://production.shippingapis.com/ShippingAPITest.dll?API=RateV4&XML=';

    /**
     * Test mode flag
     * @var boolean
     */
    protected $testMode = false;

    /**
     * Request XML
     * @var string
     */
    protected $request = '<RateV4Request USERID="[{username}]" PASSWORD="[{password}]">';

    /**
     * Ship to fields
     * @var array
     */
    protected $shipTo = [
        'ZipDestination' => null
    ];

    /**
     * Ship from fields
     * @var string
     */
    protected $shipFrom = [
        'ZipOrigination' => null
    ];



    /**
     * Machinable flag
     * @var string
     */
    protected $machinable = 'false';

    /**
     * Constructor
     *
     * Method to instantiate an USPS shipping adapter object
     *
     * @param  string  $username
     * @param  string  $password
     * @param  boolean $test
     * @return Usps
     */
    public function __construct($username, $password, $test = false)
    {
        $this->testMode = (bool)$test;
        $this->request  = str_replace(['[{username}]', '[{password}]'], [$username, $password], $this->request);
    }

    /**
     * Set ship to
     *
     * @param  array $shipTo
     * @return void
     */
    public function shipTo(array $shipTo)
    {
        foreach ($shipTo as $key => $value) {
            if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip')) {
                $this->shipTo['ZipDestination'] = $value;
            }
        }
    }

    /**
     * Set ship from
     *
     * @param  array $shipFrom
     * @return void
     */
    public function shipFrom(array $shipFrom)
    {
        foreach ($shipFrom as $key => $value) {
            if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip')) {
                $this->shipFrom['ZipOrigination'] = $value;
            }
        }
    }

    /**
     * Set container
     *
     * @param  string $container
     * @throws Exception
     * @return void
     */
    public function setContainer($container = 'RECTANGULAR')
    {
        foreach ($this->packages as $package)
        {
            $package->setContainer($container);
        }
    }

    /**
     * Set machinable flag
     *
     * @param  boolean $machinable
     * @return void
     */
    public function setMachinable($machinable = false)
    {
        $this->machinable = ($machinable) ? 'true' : 'false';
    }

    /**
     * Confirm a shipment
     *
     * @param bool $verifyPeer
     *
     * @return string Label
     */
    public function sendConfirm($verifyPeer = true)
    {
        return '';
    }

    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @return void
     */
    public function send($verifyPeer = true)
    {
        $this->buildRequest();

        $options = [
            CURLOPT_HEADER         => false,
            CURLOPT_URL            => ((($this->testMode) ? $this->testUrl : $this->liveUrl) . rawurlencode($this->request)),
            CURLOPT_RETURNTRANSFER => true
        ];

        if (!$verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $this->response = simplexml_load_string($this->parseResponse($curl));
        $this->ratesExtended = [];


        if (isset($this->response->Package)) {
            if (isset($this->response->Package->Error)) {
                $this->responseCode    = (string) $this->response->Package->Error->Number;
                $this->responseMessage = (string)$this->response->Package->Error->Description;
            } else {
                $this->responseCode = 1;

                foreach ($this->response->Package as $package) {

                    foreach ($package->Postage as $rate) {

                        $serviceType = str_replace(['&lt;', '&gt;'], ['<', '>'], (string)$rate->MailService);

                        if (!array_key_exists($serviceType, $this->rates)) {
                            $this->rates[$serviceType] = (float) $rate->Rate;

                            $this->ratesExtended[$serviceType] = (object) [
                                'shipper' => 'usps',
                                'total' => (float)$rate->Rate,
                                'PackagingType' => (string) $rate->PackagingType,
                                'SignatureOption' => (string) $rate->SignatureOption,
                                'ActualRateType' => (string) $rate->ActualRateType,
                                'title' =>  $serviceType
                            ];
                        } else {
                            $this->rates[$serviceType] += (float) $rate->Rate;
                            $this->ratesExtended[$serviceType]->total += (float) $rate->Rate;
                        }

                    }
                }
                
                $this->rates = array_reverse($this->rates, true);
            }
        } else {
            if (isset($this->response->Number)) {
                $this->responseCode    = (string)$this->response->Number;
                $this->responseMessage = (string)$this->response->Description;
            } else {
                $this->responseCode = 0;
            }
        }
    }

    /**
     * Return whether the transaction is a success
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return ($this->responseCode == 1);
    }

    /**
     * Return whether the transaction is an error
     *
     * @return boolean
     */
    public function isError()
    {
        return ($this->responseCode != 1);
    }


    private function ordinal($number)
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if ((($number % 100) >= 11) && (($number % 100) <= 13))
        {
            return $number . 'th';
        }
        else
        {
            return $number . $ends[$number % 10];
        }
    }

    /**
     * Build rate request
     *
     * @return void
     */
    protected function buildRequest()
    {
        foreach ($this->packages as $id => $package)
        {
            $id  = $id + 1;
            $this->request .= PHP_EOL . '    <Package ID="' . $this->ordinal($id) . '">';
            $this->request .= PHP_EOL . '        <Service>ALL</Service>';
            $this->request .= PHP_EOL . '        <ZipOrigination>' . $this->shipFrom['ZipOrigination'] . '</ZipOrigination>';
            $this->request .= PHP_EOL . '        <ZipDestination>' . $this->shipTo['ZipDestination'] . '</ZipDestination>';
            $this->request .= $package->rateRequest();
            $this->request .= PHP_EOL . '        <Machinable>' . $this->machinable . '</Machinable>';
            $this->request .= PHP_EOL . '        <DropOffTime>12:00</DropOffTime>';
            $this->request .= PHP_EOL . '        <ShipDate>' . date('Y-m-d') . '</ShipDate>';
            $this->request .= PHP_EOL . '    </Package>';
        }

        $this->request .= PHP_EOL . '</RateV4Request>';
        //echo $this->request;//exit;
    }

    /**
     * Confirm a shipment
     *
     * @param bool $verifyPeer
     *
     * @throws \Exception
     *
     * @return string Shipping label
     */
    public function ship($verifyPeer = true)
    {
        //@TODO usps shipping
        return '';
    }


    /**
     * Get Package
     * @return \Pop\Shipping\PackageAdapter\Usps
     */
    public function getPackage()
    {
        return new \Pop\Shipping\PackageAdapter\Usps();
    }

}
