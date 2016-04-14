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
 * FedEx shipping adapter class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Fedex extends AbstractAdapter
{

    /**
     * SOAP Client
     * @var \SoapClient
     */
    protected $client = null;

    /**
     * FedEx WSDL File
     * @var string
     */
    protected $wsdl = null;

    /**
     * Request array
     * @var array
     */
    protected $request = null;

    /**
     * Request header - common header to apply to all API requests
     * @var array
     */
    protected $requestHeader = [];

    /**
     * Ship to fields
     * @var array
     */
    protected $shipTo = [
        'Contact' => [
            'PersonName'  => '',
            'CompanyName' => '',
            'PhoneNumber' => ''
        ],
        'Address' => [
            'StreetLines'         => [],
            'City'                => '',
            'StateOrProvinceCode' => '',
            'PostalCode'          => '',
            'CountryCode'         => '',
            'Residential'         => false
        ]
    ];

    /**
     * Ship from fields
     * @var array
     */
    protected $shipFrom = [
        'Contact' => [
            'PersonName'  => '',
            'CompanyName' => '',
            'PhoneNumber' => ''
        ],
        'Address' => [
            'StreetLines'         => [],
            'City'                => '',
            'StateOrProvinceCode' => '',
            'PostalCode'          => '',
            'CountryCode'         => ''
        ]
    ];

	/**
	 * Drop of types
	 * @var array
	 */
	protected $dropOfTypes = [
		'REGULAR_PICKUP', 'REQUEST_COURIER', 'DROP_BOX', 'BUSINESS_SERVICE_CENTER', 'STATION'
	];

	protected $dropOfType = 'REGULAR_PICKUP';

    /**
     * Package dimensions
     * @var array
     */
    protected $dimensions = [
        'Length' => null,
        'Width'  => null,
        'Height' => null,
        'Units'  => 'IN'
    ];

    /**
     * Package weight
     * @var array
     */
    protected $weight = [
        'Value' => null,
        'Units' => 'LB'
    ];

    /**
     * Services
     * @var array
     */
    protected static $services = [
        'FIRST_OVERNIGHT'     => '1st Overnight',
        'PRIORITY_OVERNIGHT'  => 'Priority Overnight',
        'STANDARD_OVERNIGHT'  => 'Standard Overnight',
        'FEDEX_2_DAY_AM'      => 'FedEx 2 Day AM',
        'FEDEX_2_DAY'         => 'FedEx 2 Day',
        'FEDEX_EXPRESS_SAVER' => 'FedEx Express Saver',
        'FEDEX_GROUND'        => 'FedEx Ground'
    ];

    /**
     * Shipping options
     * @var array
     */
    protected $shippingOptions = [
        'alcohol' => true,
        'alcoholRecipientType' => 'LICENSEE'
    ];

    protected $accountNumber;

    /**
     * Constructor
     *
     * Method to instantiate an FedEx shipping adapter object
     *
     * @param  string $key
     * @param  string $password
     * @param  string $account
     * @param  string $meter
     * @param  array $wsdl keys rates, shipping
     * @return Fedex
     */
    public function __construct($key, $password, $account, $meter, $wsdl)
    {
        $this->wsdl = $wsdl;
        $this->accountNumber = $account;
        ini_set('soap.wsdl_cache_enabled', '0');

        $this->requestHeader['WebAuthenticationDetail'] = [
            'UserCredential' =>[
                'Key'      => $key,
                'Password' => $password
            ]
        ];

        $this->requestHeader['ClientDetail'] = [
            'AccountNumber' => $account,
            'MeterNumber'   => $meter
        ];

        $this->requestHeader['TransactionDetail'] = [
            'CustomerTransactionId' => ' *** Rate Request v18 using PHP ***'
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
        foreach ($shipTo as $key => $value) {
            if (stripos($key, 'person') !== false) {
                $this->shipTo['Contact']['PersonName'] = $value;
            } else if (stripos($key, 'company') !== false) {
                $this->shipTo['Contact']['CompanyName'] = $value;
            } else if (stripos($key, 'phone') !== false) {
                $this->shipTo['Contact']['PhoneNumber'] = $value;
            } else if (stripos($key, 'address') !== false) {
                $this->shipTo['Address']['StreetLines'][] = $value;
            } else if (strtolower($key) == 'city') {
                $this->shipTo['Address']['City'] = $value;
            } else if ((stripos($key, 'state') !== false) || (stripos($key, 'province') !== false)) {
                $this->shipTo['Address']['StateOrProvinceCode'] = $value;
            } else if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip')) {
                $this->shipTo['Address']['PostalCode'] = $value;
            } else if ((strtolower($key) == 'countrycode') || (strtolower($key) == 'country')) {
                $this->shipTo['Address']['CountryCode'] = $value;
            } else if (strtolower($key) == 'residential') {
                $this->shipTo['Address']['Residential'] = $value;
            }
        }

        return $this->shipTo;
    }

    /**
     * Set ship from
     *
     * @param  array  $shipFrom
     * @return mixed
     */
    public function shipFrom(array $shipFrom)
    {
        foreach ($shipFrom as $key => $value) {
            if (stripos($key, 'person') !== false) {
                $this->shipFrom['Contact']['PersonName'] = $value;
            } else if (stripos($key, 'company') !== false) {
                $this->shipFrom['Contact']['CompanyName'] = $value;
            } else if (stripos($key, 'phone') !== false) {
                $this->shipFrom['Contact']['PhoneNumber'] = $value;
            } else if (stripos($key, 'address') !== false) {
                $this->shipFrom['Address']['StreetLines'][] = $value;
            } else if (strtolower($key) == 'city') {
                $this->shipFrom['Address']['City'] = $value;
            } else if ((stripos($key, 'state') !== false) || (stripos($key, 'province') !== false)) {
                $this->shipFrom['Address']['StateOrProvinceCode'] = $value;
            } else if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip')) {
                $this->shipFrom['Address']['PostalCode'] = $value;
            } else if ((strtolower($key) == 'countrycode') || (strtolower($key) == 'country')) {
                $this->shipFrom['Address']['CountryCode'] = $value;
            } else if (strtolower($key) == 'residential') {
                $this->shipFrom['Address']['Residential'] = $value;
            }
        }

        return $this->shipFrom;
    }

    /**
     * Add Info on who pays the shipping charges.
     *
     * @return array
     */
    protected function addShippingChargesPayment()
    {
        $shippingChargesPayment = array('PaymentType' => 'SENDER',
            'Payor' => array(
                'ResponsibleParty' => array(
                    'AccountNumber' => $this->accountNumber,
                    'Contact' => null,
                    'Address' => array(
                        'CountryCode' => 'US'
                    )
                )
            )
        );
        return $shippingChargesPayment;
    }

    /**
     * Add label specification to shipping.
     * @return array
     */
    protected function addLabelSpecification()
    {
        $labelSpecification = array(
            'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
            'ImageType' => 'PNG',  // valid values DPL, EPL2, PDF, ZPLII and PNG
            'LabelStockType' => 'PAPER_7X4.75'
        );

        if ($this->shippingOptions['alcohol'])
        {
            $labelSpecification['CustomerSpecifiedDetail']['RegulatoryLabels'] = ['Type' => 'ALCOHOL_SHIPMENT_LABEL'];
        }

        return $labelSpecification;
    }

    /**
     * Add a shipping package line item
     * @return array
     */
    protected function addPackageLineItem1()
    {
        // @TODO - insured value & amount & customer reference
        $packageLineItem = array(
            'SequenceNumber'=> 1,
            'GroupPackageCount'=> 1,
            'InsuredValue' => array(
                'Amount' => floatval($this->insuranceValue),
                'Currency' => 'USD'
            ),
            'Weight' => $this->weight,
            'Dimensions' => $this->dimensions,
            'CustomerReferences' => array(
                '0' => array(
                    'CustomerReferenceType' => 'CUSTOMER_REFERENCE', // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
                    'Value' => 'GR4567892'
                ),
                '1' => array(
                    'CustomerReferenceType' => 'INVOICE_NUMBER',
                    'Value' => 'INV4567892'
                ),
                '2' => array(
                    'CustomerReferenceType' => 'P_O_NUMBER',
                    'Value' => 'PO4567892'
                )
            )
        );

        if ($this->shippingOptions['alcohol'])
        {
            $packageLineItem['SpecialServicesRequested'] = [
                'SpecialServiceTypes' => 'ALCOHOL',
                'AlcoholDetail' => [
                    'RecipientType' => $this->shippingOptions['alcoholRecipientType']
                ]
            ];
        }

        return $packageLineItem;
    }

    /**
     * Set the drop off type
     * @param $type
     */
	protected function setDropOfType($type)
	{
		if (in_array($this->dropOfTypes, $type))
		{
			$this->dropOfType = $type;
		}
		else
		{
			$this->dropOfType = 'REGULAR_PICKUP';
		}
	}

    /**
     * Confirm a shipment
     *
     * @param bool $verifyPeer
     *
     * @throws \Exception
     *
     * @return array label file format, label image data
     */
    public function ship($verifyPeer = true)
    {
        $request = [];

        $request['Version'] = array(
            'ServiceId' => 'ship',
            'Major' => '17',
            'Intermediate' => '0',
            'Minor' => '0'
        );

        $serviceType = isset($this->shippingInfo->serviceType) ? $this->shippingInfo->serviceType : 'STANDARD_OVERNIGHT';
        $packageType = isset($this->shippingInfo->PackagingType) ? $this->shippingInfo->PackagingType : 'YOUR_PACKAGING';

        $request['RequestedShipment'] = array(
            'ShipTimestamp' => date('c'),
            'DropoffType' => $this->dropOfType,
            'ServiceType' => $serviceType, // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
            'PackagingType' => $packageType, // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
            'Shipper' => $this->shipFrom,
            'Recipient' => $this->shipTo,
            'ShippingChargesPayment' => $this->addShippingChargesPayment(),
            'LabelSpecification' => $this->addLabelSpecification(),

            'CustomerSpecifiedDetail' => array('MaskedData'=> 'SHIPPER_ACCOUNT_NUMBER'),
            'PackageCount' => 1,
            'RequestedPackageLineItems' => array(
                '0' => $this->addPackageLineItem1()
            )
        );

        $request = array_merge($this->requestHeader, $request);
        $this->client = new \SoapClient($this->wsdl['shipping'], ['trace' => 1]);
        $this->response = $this->client->processShipment($request);

        if ($this->response->HighestSeverity === 'ERROR')
        {
            $errors = $this->response->Notifications;

            if (is_object($errors))
            {
                $errors = [$errors];
            }
            foreach ($errors as $error)
            {
                if ($error->Severity === 'ERROR')
                {
                    $errorMsg[] = $error->Message;
                }

            }

            throw new \Exception(implode("\n", $errorMsg), $errors[0]->Code);
        }

        $label = $this->response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;

        return ['png', base64_decode($label)];
    }

    /**
     * Request shipping rates
     *
     * @return void
     */
    public function send()
    {
        $this->requestHeader['Version'] = [
            'ServiceId'    => 'crs',
            'Major'        => '18',
            'Intermediate' => '0',
            'Minor'        => '0'
        ];

	    $request['RequestedShipment'] = [
		    'RateRequestTypes' => 'ACCOUNT',
		    'RateRequestTypes' => 'LIST',
		    'PackageCount' => count($this->packages),
		    'Shipper' => $this->shipFrom,
		    'Recipient' => $this->shipTo,
		    'RequestedPackageLineItems' => []
	    ];

        foreach ($this->packages as $package)
        {
            $request['RequestedShipment']['RequestedPackageLineItems'][] = $package->rateRequest();
        }

        $request = array_merge($this->requestHeader, $request);
        $this->client = new \SoapClient($this->wsdl['rates'], ['trace' => 1]);
        $this->response = $this->client->getRates($request);
        $this->responseCode = (int) $this->response->Notifications->Code;
        $this->responseMessage = (string) $this->response->Notifications->Message;
	    $this->ratesExtended = [];

        if ($this->responseCode == 0) {
            foreach ($this->response->RateReplyDetails as $rate) {
	            $serviceType = (string) $rate->ServiceType;
	            $total = number_format((string) $rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2);

	            $this->ratesExtended[$serviceType] = (object) [
                    'shipper' => 'fedex',
		            'total' => $total,
		            'PackagingType' => (string) $rate->PackagingType,
		            'SignatureOption' => (string) $rate->SignatureOption,
		            'ActualRateType' => (string) $rate->ActualRateType,
		            'serviceType' => $serviceType,
		            'title' => self::$services[$serviceType]
	            ];
                $this->rates[self::$services[$serviceType]] = $total;
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

    /**
     * Get Package
     * @return \Pop\Shipping\PackageAdapter\Fedex
     */
    public function getPackage()
    {
        return new \Pop\Shipping\PackageAdapter\Fedex();
    }

}
