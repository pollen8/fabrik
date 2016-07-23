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
 * Gso shipping adapter class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Gso extends AbstractAdapter
{

	/**
	 * SOAP Client
	 *
	 * @var \SoapClient
	 */
	protected $client = null;

	/**
	 * FedEx WSDL File
	 *
	 * @var string
	 */
	protected $wsdl = null;

	/**
	 * Request array
	 *
	 * @var array
	 */
	protected $request = null;

	/**
	 * Request header - common header to apply to all API requests
	 *
	 * @var SoapHeader
	 */
	protected $requestHeader;

	/**
	 * Ship to fields
	 *
	 * @var array
	 */
	protected $shipTo = [
		'Contact' => [
			'PersonName' => '',
			'CompanyName' => '',
			'PhoneNumber' => ''
		],
		'Address' => [
			'StreetLines' => ['', ''],
			'City' => '',
			'StateOrProvinceCode' => '',
			'PostalCode' => '',
			'CountryCode' => '',
			'Residential' => false
		]
	];

	/**
	 * Ship from fields
	 *
	 * @var array
	 */
	protected $shipFrom = [
		'Contact' => [
			'PersonName' => '',
			'CompanyName' => '',
			'PhoneNumber' => ''
		],
		'Address' => [
			'StreetLines' => [],
			'City' => '',
			'StateOrProvinceCode' => '',
			'PostalCode' => '',
			'CountryCode' => ''
		]
	];

	/**
	 * Drop of types
	 *
	 * @var array
	 */
	protected $dropOfTypes = [
		'REGULAR_PICKUP', 'REQUEST_COURIER', 'DROP_BOX', 'BUSINESS_SERVICE_CENTER', 'STATION'
	];

	protected $dropOfType = 'REGULAR_PICKUP';

	/**
	 * Package dimensions
	 *
	 * @var array
	 */
	protected $dimensions = [
		'Length' => null,
		'Width' => null,
		'Height' => null,
		'Units' => 'IN'
	];

	/**
	 * Package weight
	 *
	 * @var array
	 */
	protected $weight = [
		'Value' => null,
		'Units' => 'LB'
	];

	/**
	 * Services
	 *
	 * @var array
	 */
	protected static $services = [

		'GSO' => 'Golden State Overnight',
		'CPS' => 'California Parcel Service',
		'EPS' => 'Early Priority Service',
		'ESS' => 'Early Saturday Service',
 		'NPS' => 'Noon Priority Service',
	    'PDS' => 'Priority Delivery Service',
	    'SDS' => 'Saturday Delivery Service'
	];

	protected $accountNumber;

	/**
	 * Constructor
	 *
	 * Method to instantiate an FedEx shipping adapter object
	 *
	 * @param  string $username
	 * @param  string $password
	 * @param  string $accountNumber
	 * @param  string $wsdl
	 *
	 * @return Gso
	 */
	public function __construct($username, $password, $accountNumber, $wsdl)
	{
		if (!is_array($wsdl))
		{
			$wsdl = ['rates' => $wsdl, 'shipping' => $wsdl];
		}
		$this->wsdl          = $wsdl;
		$this->accountNumber = $accountNumber;
		ini_set('soap.wsdl_cache_enabled', '0');

		$this->requestHeader = new \SoapHeader('http://gso.com/GsoShipWS', 'AuthenticationHeader',
			array(
				'UserName' => $username,
				'Password' => $password
			));
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
	 * @param  array $shipTo
	 *
	 * @return mixed
	 */
	public function shipTo(array $shipTo)
	{
		foreach ($shipTo as $key => $value)
		{
			if (stripos($key, 'person') !== false)
			{
				$this->shipTo['Contact']['PersonName'] = $value;
			}
			else
			{
				if (stripos($key, 'company') !== false)
				{
					$this->shipTo['Contact']['CompanyName'] = $value;
				}
				else
				{
					if (stripos($key, 'phone') !== false)
					{
						$this->shipTo['Contact']['PhoneNumber'] = $value;
					}
					else
					{
						if (stripos($key, 'address') !== false)
						{
							$v = (array) $value;
							foreach ($v as $k => $s)
							{
								$this->shipTo['Address']['StreetLines'][$k] = $s;
							}
						}
						else
						{
							if (strtolower($key) == 'city')
							{
								$this->shipTo['Address']['City'] = $value;
							}
							else
							{
								if ((stripos($key, 'state') !== false) || (stripos($key, 'province') !== false))
								{
									$this->shipTo['Address']['StateOrProvinceCode'] = $value;
								}
								else
								{
									if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip'))
									{
										$this->shipTo['Address']['PostalCode'] = $value;
									}
									else
									{
										if ((strtolower($key) == 'countrycode') || (strtolower($key) == 'country'))
										{
											$this->shipTo['Address']['CountryCode'] = $value;
										}
										else
										{
											if (strtolower($key) == 'residential')
											{
												$this->shipTo['Address']['Residential'] = $value;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $this->shipTo;
	}

	/**
	 * Set ship from
	 *
	 * @param  array $shipFrom
	 *
	 * @return mixed
	 */
	public function shipFrom(array $shipFrom)
	{
		foreach ($shipFrom as $key => $value)
		{
			if (stripos($key, 'person') !== false)
			{
				$this->shipFrom['Contact']['PersonName'] = $value;
			}
			else
			{
				if (stripos($key, 'company') !== false)
				{
					$this->shipFrom['Contact']['CompanyName'] = $value;
				}
				else
				{
					if (stripos($key, 'phone') !== false)
					{
						$this->shipFrom['Contact']['PhoneNumber'] = $value;
					}
					else
					{
						if (stripos($key, 'address') !== false)
						{
							$this->shipFrom['Address']['StreetLines'][] = $value;
						}
						else
						{
							if (strtolower($key) == 'city')
							{
								$this->shipFrom['Address']['City'] = $value;
							}
							else
							{
								if ((stripos($key, 'state') !== false) || (stripos($key, 'province') !== false))
								{
									$this->shipFrom['Address']['StateOrProvinceCode'] = $value;
								}
								else
								{
									if ((strtolower($key) == 'postalcode') || (strtolower($key) == 'zipcode') || (strtolower($key) == 'zip'))
									{
										$this->shipFrom['Address']['PostalCode'] = $value;
									}
									else
									{
										if ((strtolower($key) == 'countrycode') || (strtolower($key) == 'country'))
										{
											$this->shipFrom['Address']['CountryCode'] = $value;
										}
										else
										{
											if (strtolower($key) == 'residential')
											{
												$this->shipFrom['Address']['Residential'] = $value;
											}
										}
									}
								}
							}
						}
					}
				}
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
	 *
	 * @return array
	 */
	protected function addLabelSpecification()
	{
		$labelSpecification = array(
		);


		return $labelSpecification;
	}

	/**
	 * Add a shipping package line item
	 *
	 * @param Pop\Shipping\PackageAdapter\AbstractAdapter
	 *
	 * @return array
	 */
	protected function addPackageLineItem($package)
	{
		// TODO unhardwire:
		$this->trackingNumber = '012312312';
		$this->shipmentReference = 'aadfasdfasdf';
		$this->CODValue = '0.00';
		$this->signatureRequired = 'ADULT_SIG_REQD'; // >SIG_REQD or SIG_NOT_REQD or ADULT_SIG_REQD

		echo "shipoing info = ";print_r($this->shippingInfo);
		$serviceType = isset($this->shippingInfo->serviceType) ? $this->shippingInfo->serviceType : 'STANDARD_OVERNIGHT';
		echo "serice type = $serviceType";
		// TODO - not sure if $this->declaredValue should be per package or as total?
		return [
			'AccountNumber' => $this->accountNumber,
			'TrackingNumber' => $this->trackingNumber,
			'ShipperCompany' => $this->shipFrom['Contact']['CompanyName'],
			'ShipperContact' => $this->shipFrom['Contact']['PersonName'],
			'ShipperPhone' => $this->shipFrom['Contact']['PhoneNumber'],
			'PickupAddress1' => $this->shipFrom['Address']['StreetLines'][0],
			'PickupAddress2' => $this->shipFrom['Address']['StreetLines'][1],
			'PickupCity' => $this->shipFrom['Address']['City'],
			'PickupState' => $this->shipFrom['Address']['StateOrProvinceCode'],
			'PickupZip' => $this->shipFrom['Address']['PostalCode'],
			'ShipToCompany' => $this->shipTo['Contact']['CompanyName'],
			'ShipToAttention' => $this->shipTo['Contact']['PersonName'],
			'ShipToPhone' => $this->shipTo['Contact']['PhoneNumber'],
			'DeliveryAddress1' => $this->shipTo['Address']['StreetLines'][0],
			'DeliveryAddress2' => $this->shipTo['Address']['StreetLines'][1],
			'DeliveryCity' => $this->shipTo['Address']['City'],
			'DeliveryState' => $this->shipTo['Address']['StateOrProvinceCode'],
			'DeliveryZip' => $this->shipTo['Address']['PostalCode'],
			'ServiceCode' => $serviceType,
			'ShipmentReference' => $this->shipmentReference,
			'DeclaredValue' => $this->declaredValue,
			'CODValue' => $this->CODValue,
			'Weight' => $package->getWeight(),
			'SignatureCode' => $this->signatureRequired
		];
	}

	/**
	 * Set the drop off type
	 *
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
		$request = [
		];

		$request['Shipment']['Package'] = [];

		$request['Shipment'] =
			[
				'Notification' => [
					'ShipmentNotification' => '',
					'ExceptionNotification' => '',
					'DeliveryNotification' => '',
					'ReceiptNotification' => ''
				],
				'ShipmentLabel' => [
					'Type' => 'PAPER_LABEL'
					]
		];

		// TODO - we can only add in one package - ask G what to do here....
		foreach ($this->packages as $package)
		{
			$request['Shipment']['Package'] = $this->addPackageLineItem($package);
		}

		$this->client   = new \SoapClient($this->wsdl['shipping'], ['trace' => 1]);
		$this->client->__setSoapHeaders($this->requestHeader);
		$this->response = $this->client->SubmitShipment($request);

		if ($this->response->SubmitShipmentResult->Result->Code == '1')
		{
			throw new \Exception(implode("\n", $this->response->Result->Message));
		}

		$label = $this->response->SubmitShipmentResult->Label->Paper;

		// @todo test this is the right format and label is not base64 enoded
		return ['gif', $label];
	}

	/**
	 * Get the SOAP client
	 * @return \SoapClient
	 */
	private function getClient()
	{
		if (!isset($this->client))
		{
			$this->client = new \SoapClient($this->wsdl['rates'], ['trace' => 1]);
			$this->client->__setSoapHeaders($this->requestHeader);
		}
		return $this->client;
	}

	/**
	 * @return array|void
	 */
	private function getServiceTypes()
	{
		self::$services = array();
		$client = $this->getClient();

		$typeRequest = [
			'GetServiceTypesRequest' => [
				'AccountNumber' => $this->accountNumber
			]
		];
		$serviceTypes = $client->GetServiceTypes($typeRequest);

		if ($serviceTypes->GetServiceTypesResult->Result->Code === 1) {
			$this->responseCode = 1;
			$this->responseMessage = 'Could not get service types';
			return;
		}

		foreach ($serviceTypes->GetServiceTypesResult->Service->Service as $service)
		{
			self::$services[$service->Code] = $service->Description;
		}

		return self::$services;
	}

	/**
	 * Send transaction
	 *
	 * @return void
	 */
	public function send()
	{
		$request['GetShippingRateRequest'] = [
			'AccountNumber' => $this->accountNumber,
			'OriginZip' => $this->shipFrom['Address']['PostalCode'],
			'DestinationZip' => $this->shipTo['Address']['PostalCode'],
			'PackageWeight' => $this->totalWeight(),
			'DeclaredValue' => floatval($this->declaredValue),
			'CODValue' => $this->CODValue
		];

		$client = $this->getClient();
		$this->getServiceTypes();
		$responseCodes = [];
		$responseMessage = [];
		$this->ratesExtended = [];

		foreach (self::$services as $serviceCode => $serviceType)
		{
			$request['GetShippingRateRequest']['ServiceCode'] = $serviceCode;
			$this->response        = $client->GetShippingRate($request);
			$responseCode = (int) $this->response->GetShippingRateResult->Result->Code;
			$responseCodes[] = $responseCode;
			$responseMessage[] = (string) $this->response->GetShippingRateResult->Result->Message;

			if ($responseCode == 0)
			{
				$total       = number_format((string) $this->response->GetShippingRateResult->ShipmentCharges->TotalCharge, 2);

				$this->ratesExtended[$serviceCode]          = (object) [
					'shipper' => 'gso',
					'total' => $total,
					'serviceType' =>$serviceCode,
					'title' => $serviceType
				];
				$this->rates[$serviceCode] = $total;
			}
		}
		// Presume that response is OK if at least one API call returned true
		$this->responseCode = min($responseCodes);
		$this->responseMessage = $responseMessage[array_search($this->responseCode, $responseCodes)];
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
	 * @return \Pop\Shipping\PackageAdapter\Gso
	 */
	public function getPackage()
	{
		return new \Pop\Shipping\PackageAdapter\Gso();
	}

}
