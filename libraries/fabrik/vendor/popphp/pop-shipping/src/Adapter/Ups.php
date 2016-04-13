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
 * UPS shipping adapter class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Ups extends AbstractAdapter
{
	/**
	 * Live API URLs
	 *
	 * @var array
	 */
	protected $liveUrl = [
		'rates' => 'https://onlinetools.ups.com/ups.app/xml/Rate',
		'shipping' => 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm',
		'shipAccept' => 'https://onlinetools.ups.com/ups.app/xml/ShipAccept'
	];

	/**
	 * Test API URLs
	 *
	 * @var array
	 */
	protected $testUrl = [
		'rates' => 'https://wwwcie.ups.com/ups.app/xml/Rate',
		'shipping' => 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm',
		'shipAccept' => 'https://wwwcie.ups.com/ups.app/xml/ShipAccept'
	];

	/**
	 * Test mode flag
	 *
	 * @var boolean
	 */
	protected $testMode = false;

	/**
	 * Optional Processing. nonvalidate = No street level address
	 * validation would be performed, but Postal Code/State
	 * combination validation would still be performed.
	 * validate = No street level address validation would be
	 * performed, but City/State/Postal Code combination validation
	 * would still be performed.
	 *
	 * @var string
	 */
	protected $validateAddress = 'nonvalidate';

	/**
	 * Shipment description required for international shipments
	 *
	 * @var string
	 */
	protected $description = '';
	/**
	 * User ID
	 *
	 * @var string
	 */
	protected $userId = null;

	/**
	 * Access Request XML
	 *
	 * @var string
	 */
	protected $accessRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<AccessRequest xml:lang=\"en-US\">";

	/**
	 * Selected Packaging code, see packagingTypes
	 *
	 * @var string
	 */
	protected $packageCode = '02';

	/**
	 * Selected shipment charge type
	 *  01 =Transportation • 02 = Duties and Taxes not required
	 * see page 43 of API docs
	 *
	 * @var string
	 */
	protected $shipmentChargeType = '01';

	// @TODO replace this with parameter
	protected $shipperNumber = 'XF3464';

	/**
	 * @var string base64 encoded shipping label
	 */
	protected $label;

	/**
	 * Rate Request XML
	 *
	 * @var string
	 */
	protected $rateRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<RatingServiceSelectionRequest>";

	/**
	 * Pickup Types
	 *
	 * @var array
	 */
	protected static $pickupTypes = [
		'01' => 'Daily Pickup',
		'03' => 'Customer Counter',
		'06' => 'One Time Pickup',
		'07' => 'On Call Air',
		'19' => 'Letter Center',
		'20' => 'Air Service Center'
	];

	/**
	 * Services
	 *
	 * @var array
	 */
	protected static $services = [
		'14' => 'Next Day Air Early AM',
		'01' => 'Next Day Air',
		'13' => 'Next Day Air Saver',
		'59' => '2nd Day Air AM',
		'02' => '2nd Day Air',
		'12' => '3 Day Select',
		'03' => 'Ground',
		'11' => 'Standard',
		'07' => 'Worldwide Express',
		'54' => 'Worldwide Express Plus',
		'08' => 'Worldwide Expedited',
		'65' => 'Saver'
	];

	/**
	 * @TODO replce with parameter
	 * Shipping options
	 * @var array
	 */
	protected $shippingOptions = [
		'alcohol' => false,
		'alcoholRecipientType' => 'LICENSEE'
	];

	/**
	 * Ship to fields
	 *
	 * @var array
	 */
	protected $shipTo = [
		'CompanyName' => null,
		'AddressLine1' => null,
		'AddressLine2' => null,
		'AddressLine3' => null,
		'City' => null,
		'StateProvinceCode' => '',
		'PostalCode' => null,
		'CountryCode' => null
	];

	/**
	 * Ship from fields
	 *
	 * @var array
	 */
	protected $shipFrom = [
		'CompanyName' => null,
		'AddressLine1' => null,
		'AddressLine2' => null,
		'AddressLine3' => null,
		'City' => null,
		'StateProvinceCode' => '',
		'PostalCode' => null,
		'CountryCode' => null,
		'PhoneNumber' => null
	];

	/**
	 * Pickup type
	 *
	 * @var string
	 */
	protected $pickupType = '01';

	/**
	 * Service
	 *
	 * @var string
	 */
	protected $service = '03';

	/**
	 * Package dimensions
	 *
	 * @var array
	 */
	protected $dimensions = [
		'UnitOfMeasurement' => 'IN',
		'Length' => null,
		'Width' => null,
		'Height' => null
	];

	/**
	 * Package weight
	 *
	 * @var array
	 */
	protected $weight = [
		'UnitOfMeasurement' => 'LBS',
		'Weight' => null
	];

	/**
	 * Constructor
	 *
	 * Method to instantiate an UPS shipping adapter object
	 *
	 * @param  string  $accessKey
	 * @param  string  $userId
	 * @param  string  $password
	 * @param  boolean $test
	 *
	 * @return Ups
	 */
	public function __construct($accessKey, $userId, $password, $test = false)
	{
		$this->testMode = (bool) $test;
		$this->userId   = $userId;

		if (empty($accessKey) || empty($userId) ||empty($password))
		{
			throw new \InvalidArgumentException('UPS shipping mut supply an accesskey; userid and password');
		}

		$this->accessRequest .= PHP_EOL . '    <AccessLicenseNumber>' . $accessKey . '</AccessLicenseNumber>';
		$this->accessRequest .= PHP_EOL . '    <UserId>' . $userId . '</UserId>';
		$this->accessRequest .= PHP_EOL . '    <Password>' . $password . '</Password>';
		$this->accessRequest .= PHP_EOL . '</AccessRequest>' . PHP_EOL;
	}

	/**
	 * Static method to get the pickup types
	 *
	 * @return array
	 */
	public static function getPickupTypes()
	{
		return self::$pickupTypes;
	}

	/**
	 * Static method to get the packaging types
	 *
	 * @return array
	 */
	public static function getPackagingTypes()
	{
		return self::$packagingTypes;
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
	 * Set pickup type
	 *
	 * @param  string $code
	 *
	 * @throws Exception
	 * @return void
	 */
	public function setPickup($code)
	{
		if (!array_key_exists($code, self::$pickupTypes))
		{
			throw new Exception('Error: That pickup code does not exist.');
		}

		$this->pickupType = $code;
	}

	/**
	 * Set service
	 *
	 * @param  string $code
	 *
	 * @throws Exception
	 * @return void
	 */
	public function setService($code)
	{
		if (!array_key_exists($code, self::$services))
		{
			throw new Exception('Error: That service code does not exist.');
		}

		$this->service = $code;
	}

	/**
	 * Set ship to
	 *
	 * @param  array $shipTo
	 *
	 * @return void
	 */
	public function shipTo(array $shipTo)
	{
		$map = [
			'phone' => 'PhoneNumber',
			'state' => 'StateProvinceCode',
			'company' => 'CompanyName',
			'addressline1' => 'AddressLine1',
			'address1' => 'AddressLine1',
			'address' => 'AddressLine1',
			'addressline2' => 'AddressLine2',
			'address2' => 'AddressLine2',
			'addressline3' => 'AddressLine3',
			'address3' => 'AddressLine3',
			'city' => 'City',
			'postalcode' => 'PostalCode',
			'zipcode' => 'PostalCode',
			'zip' => 'PostalCode',
			'countrycode' => 'CountryCode',
			'country' => 'CountryCode',
		];

		foreach ($map as $key => $val)
		{
			if (array_key_exists($key, $shipTo))
			{
				$this->shipTo[$val] = $shipTo[$key];
			}
		}
	}

	/**
	 * Set ship from
	 *
	 * @param  array $shipFrom
	 *
	 * @return void
	 */
	public function shipFrom(array $shipFrom)
	{
		$map = [
			'phone' => 'PhoneNumber',
			'state' => 'StateProvinceCode',
			'company' => 'CompanyName',
			'addressline1' => 'AddressLine1',
			'address1' => 'AddressLine1',
			'address' => 'AddressLine1',
			'addressline2' => 'AddressLine2',
			'address2' => 'AddressLine2',
			'addressline3' => 'AddressLine3',
			'address3' => 'AddressLine3',
			'city' => 'City',
			'postalcode' => 'PostalCode',
			'zipcode' => 'PostalCode',
			'zip' => 'PostalCode',
			'countrycode' => 'CountryCode',
			'country' => 'CountryCode',
		];

		foreach ($map as $key => $val)
		{
			if (array_key_exists($key, $shipFrom))
			{
				$this->shipFrom[$val] = $shipFrom[$key];
			}
		}
	}

	/**
	 * Confirm a shipment
	 *
	 * @param bool $verifyPeer
	 *
	 * @return string Shipping label
	 */
	public function ship($verifyPeer = true)
	{
		$xml = $this->buildConfirmRequest();

		$options = [
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $this->accessRequest . $xml,
			CURLOPT_URL => $this->testMode ? $this->testUrl['shipping'] : $this->liveUrl['shipping'],
			CURLOPT_RETURNTRANSFER => true
		];

		if (!$verifyPeer)
		{
			$options[CURLOPT_SSL_VERIFYPEER] = false;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$xml = $this->parseResponse($curl);

		if (curl_errno($curl) !== 0)
		{
			$this->responseCode    = 500;
			$this->responseMessage = curl_error($curl);

			return;
		}

		$this->response     = simplexml_load_string($xml);
		$this->responseCode = (int) $this->response->Response->ResponseStatusCode;

		if ($this->responseCode == 1)
		{
			$this->label = $this->sendAcceptRequest((string) $this->response->ShipmentDigest, $verifyPeer);
		}
		else
		{
			$this->responseCode    = (string) $this->response->Response->Error->ErrorCode;
			$this->responseMessage = (string) $this->response->Response->Error->ErrorDescription;
		}

		return $this->label;
	}

	/**
	 * Second part of accepting the confirmed shipment
	 *
	 * @param string $digest
	 * @param bool   $verifyPeer
	 *
	 * @return array label file format, label image data
	 */
	protected function sendAcceptRequest($digest, $verifyPeer)
	{
		$xml = '<?xmlversion="1.0"?>';
		$xml .= PHP_EOL . '<ShipmentAcceptRequest>';
		$xml .= PHP_EOL . '    <Request>';
		$xml .= PHP_EOL . '        <TransactionReference>';
		$xml .= PHP_EOL . '            <CustomerContext>Accept shipping</CustomerContext>';
		$xml .= PHP_EOL . '            <XpciVersion>1.0</XpciVersion>';
		$xml .= PHP_EOL . '        </TransactionReference>';
		$xml .= PHP_EOL . '        <RequestAction>ShipAccept</RequestAction>';
		$xml .= PHP_EOL . '        <RequestOption>01</RequestOption>';
		$xml .= PHP_EOL . '    </Request>';
		$xml .= PHP_EOL . '    <ShipmentDigest>' . $digest . '</ShipmentDigest>';
		$xml .= PHP_EOL . '</ShipmentAcceptRequest>';

		$options = [
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $this->accessRequest . $xml,

			CURLOPT_URL => $this->testMode ? $this->testUrl['shipAccept'] : $this->liveUrl['shipAccept'],
			CURLOPT_RETURNTRANSFER => true
		];

		if (!$verifyPeer)
		{
			$options[CURLOPT_SSL_VERIFYPEER] = false;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$xml = $this->parseResponse($curl);

		if (curl_errno($curl) !== 0)
		{
			$this->responseCode    = 500;
			$this->responseMessage = curl_error($curl);

			return false;
		}

		$this->response = simplexml_load_string($xml);
		$label = (string) $this->response->ShipmentResults->PackageResults->LabelImage->GraphicImage;

		// Label is base64 encoded, so decode first
		 return ['gif', base64_decode($label)];
	}

	/**
	 * Send transaction
	 *
	 * @param  boolean $verifyPeer
	 *
	 * @return void
	 */
	public function send($verifyPeer = true)
	{
		$this->buildRateRequest();

		$options = [
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $this->accessRequest . $this->rateRequest,
			CURLOPT_URL => $this->testMode ? $this->testUrl['rates'] : $this->liveUrl['rates'],
			CURLOPT_RETURNTRANSFER => true
		];

		if (!$verifyPeer)
		{
			$options[CURLOPT_SSL_VERIFYPEER] = false;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$xml = $this->parseResponse($curl);

		if (curl_errno($curl) !== 0)
		{
			$this->responseCode    = 500;
			$this->responseMessage = curl_error($curl);

			return;
		}

		$this->response     = simplexml_load_string($xml);
		$this->responseCode = (int) $this->response->Response->ResponseStatusCode;

		//print_r($this->response);
		if ($this->responseCode == 1)
		{
			$this->responseMessage = (string) $this->response->Response->ResponseStatusDescription;

			foreach ($this->response->RatedShipment as $rate)
			{
				$serviceType                       = self::$services[(string) $rate->Service->Code];
				$this->rates[$serviceType]         = (string) $rate->TotalCharges->MonetaryValue;
				$this->ratesExtended[$serviceType] = (object) [
					'shipper' => 'ups',
					'total' => $this->rates[$serviceType],
					'title' => $serviceType
				];
			}
		}
		else
		{
			$this->responseCode    = (string) $this->response->Response->Error->ErrorCode;
			$this->responseMessage = (string) $this->response->Response->Error->ErrorDescription;
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

	/**
	 * @return mixed
	 */
	protected function buildConfirmRequest()
	{
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= PHP_EOL . '<ShipmentConfirmRequest xml:lang="en-US">';
		$xml .= PHP_EOL . '    <Request>';
		$xml .= PHP_EOL . '        <TransactionReference>';
		$xml .= PHP_EOL . '            <CustomerContext>Confirm shipping</CustomerContext>';
		$xml .= PHP_EOL . '            <XpciVersion/>';
		$xml .= PHP_EOL . '        </TransactionReference>';
		$xml .= PHP_EOL . '        <RequestAction>ShipConfirm</RequestAction>';
		$xml .= PHP_EOL . '        <RequestOption>' . $this->validateAddress . '</RequestOption>';
		$xml .= PHP_EOL . '    </Request>';
		$xml .= PHP_EOL . '    <Shipment>';
		$xml .= PHP_EOL . '        <Description>' . $this->description . '</Description>';
		$xml .= PHP_EOL . '        <Shipper>';
		$xml .= PHP_EOL . '            <Name>' . $this->shipFrom['CompanyName'] . '</Name>';
		$xml .= PHP_EOL . '            <ShipperNumber>' . $this->shipperNumber . '</ShipperNumber>';
		$xml .= PHP_EOL . '            <PhoneNumber>' . $this->shipFrom['PhoneNumber'] . '</PhoneNumber>';
		$xml .= PHP_EOL . '            <Address>';

		foreach ($this->shipFrom as $key => $value)
		{
			if ($key !== 'CompanyName' && $key !== 'PhoneNumber')
			{
				if (null !== $value)
				{
					$xml .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$xml .= PHP_EOL . '                <' . $key . '></' . $key . '>';
				}
			}
		}

		$xml .= PHP_EOL . '            </Address>';
		$xml .= PHP_EOL . '        </Shipper>';

		$xml .= PHP_EOL . '        <ShipTo>';
		$xml .= PHP_EOL . '            <CompanyName>' . $this->shipTo['CompanyName'] . '</CompanyName>';
		$xml .= PHP_EOL . '            <PhoneNumber>' . $this->shipTo['PhoneNumber'] . '</PhoneNumber>';
		$xml .= PHP_EOL . '            <Address>';

		foreach ($this->shipTo as $key => $value)
		{
			if ($key !== 'CompanyName' && $key !== 'PhoneNumber')
			{
				if (null !== $value)
				{
					$xml .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$xml .= PHP_EOL . '                <' . $key . '></' . $key . '>';
				}
			}
		}
		$xml .= PHP_EOL . '            </Address>';
		$xml .= PHP_EOL . '        </ShipTo>';

		$xml .= PHP_EOL . '        <ShipFrom>';
		$xml .= PHP_EOL . '            <CompanyName>' . $this->shipFrom['CompanyName'] . '</CompanyName>';
		$xml .= PHP_EOL . '            <Address>';

		foreach ($this->shipFrom as $key => $value)
		{
			if ($key !== 'CompanyName')
			{
				if (null !== $value)
				{
					$xml .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$xml .= PHP_EOL . '                <' . $key . '></' . $key . '>';
				}
			}
		}

		$xml .= PHP_EOL . '            </Address>';
		$xml .= PHP_EOL . '        </ShipFrom>';
		$xml .= PHP_EOL . '        <PaymentInformation>';
		$xml .= PHP_EOL . '            <Prepaid>';
		$xml .= PHP_EOL . '                <BillShipper>';
		$xml .= PHP_EOL . '                    <AccountNumber>' . $this->shipperNumber . '</AccountNumber>';
		$xml .= PHP_EOL . '                </BillShipper>';
		$xml .= PHP_EOL . '            </Prepaid>';
		$xml .= PHP_EOL . '        </PaymentInformation>';
		$xml .= PHP_EOL . '        <Service>';
		$xml .= PHP_EOL . '            <Code>' . $this->service . '</Code>';
		$xml .= PHP_EOL . '            <Description>' . self::$services[$this->service] . '</Description>';
		$xml .= PHP_EOL . '        </Service>';

		$alcohol = $this->shippingOptions['alcohol'];

		// Insurance....

		foreach ($this->packages as $package)
		{
			$xml .= $package->rateRequest($alcohol);
		}

		$xml .= PHP_EOL . '    </Shipment>';
		$xml .= PHP_EOL . '    <LabelSpecification>';
		$xml .= PHP_EOL . '        <LabelPrintMethod>';
		$xml .= PHP_EOL . '            <Code>GIF</Code>';
		$xml .= PHP_EOL . '            <Description>GIF</Description>';
		$xml .= PHP_EOL . '        </LabelPrintMethod>';
		$xml .= PHP_EOL . '        <LabelImageFormat>';
		$xml .= PHP_EOL . '            <Code>GIF</Code>';
		$xml .= PHP_EOL . '        </LabelImageFormat>';
		$xml .= PHP_EOL . '    </LabelSpecification>';

		$xml .= PHP_EOL . '</ShipmentConfirmRequest>';

		return $xml;
	}

	/**
	 * Build rate request
	 *
	 * @return void
	 */
	protected function buildRateRequest()
	{
		$this->rateRequest .= PHP_EOL . '    <Request>';
		$this->rateRequest .= PHP_EOL . '        <TransactionReference>';
		$this->rateRequest .= PHP_EOL . '            <CustomerContext>Rating and Service</CustomerContext>';
		$this->rateRequest .= PHP_EOL . '            <XpciVersion>1.0</XpciVersion>';
		$this->rateRequest .= PHP_EOL . '        </TransactionReference>';
		$this->rateRequest .= PHP_EOL . '        <RequestAction>Rate</RequestAction>';
		$this->rateRequest .= PHP_EOL . '        <RequestOption>Shop</RequestOption>';
		$this->rateRequest .= PHP_EOL . '    </Request>';
		$this->rateRequest .= PHP_EOL . '    <PickupType>';
		$this->rateRequest .= PHP_EOL . '        <Code>' . $this->pickupType . '</Code>';
		$this->rateRequest .= PHP_EOL . '        <Description>' . self::$pickupTypes[$this->pickupType] . '</Description>';
		$this->rateRequest .= PHP_EOL . '    </PickupType>';
		$this->rateRequest .= PHP_EOL . '    <Shipment>';
		$this->rateRequest .= PHP_EOL . '        <Description>Rate</Description>';
		$this->rateRequest .= PHP_EOL . '        <Shipper>';
		$this->rateRequest .= PHP_EOL . '            <ShipperNumber>' . $this->userId . '</ShipperNumber>';
		$this->rateRequest .= PHP_EOL . '            <Address>';

		foreach ($this->shipFrom as $key => $value)
		{
			if ($key !== 'CompanyName')
			{
				if (null !== $value)
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . ' />';
				}
			}
		}

		$this->rateRequest .= PHP_EOL . '            </Address>';
		$this->rateRequest .= PHP_EOL . '        </Shipper>';
		$this->rateRequest .= PHP_EOL . '        <ShipTo>';

		if (null !== $this->shipTo['CompanyName'])
		{
			$this->rateRequest .= PHP_EOL . '            <CompanyName>' . $this->shipTo['CompanyName'] . '</CompanyName>';
		}

		$this->rateRequest .= PHP_EOL . '            <Address>';

		foreach ($this->shipTo as $key => $value)
		{
			if ($key !== 'CompanyName')
			{
				if (null !== $value)
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . ' />';
				}
			}
		}

		$this->rateRequest .= PHP_EOL . '            </Address>';
		$this->rateRequest .= PHP_EOL . '        </ShipTo>';
		$this->rateRequest .= PHP_EOL . '        <ShipFrom>';

		if (null !== $this->shipFrom['CompanyName'])
		{
			$this->rateRequest .= PHP_EOL . '            <CompanyName>' . $this->shipFrom['CompanyName'] . '</CompanyName>';
		}

		$this->rateRequest .= PHP_EOL . '            <Address>';

		foreach ($this->shipFrom as $key => $value)
		{
			if ($key !== 'CompanyName')
			{
				if (null !== $value)
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . '>' . $value . '</' . $key . '>';
				}
				else
				{
					$this->rateRequest .= PHP_EOL . '                <' . $key . ' />';
				}
			}
		}

		$this->rateRequest .= PHP_EOL . '            </Address>';
		$this->rateRequest .= PHP_EOL . '        </ShipFrom>';
		$this->rateRequest .= PHP_EOL . '        <Service>';
		$this->rateRequest .= PHP_EOL . '            <Code>' . $this->service . '</Code>';
		$this->rateRequest .= PHP_EOL . '            <Description>' . self::$services[$this->service] . '</Description>';
		$this->rateRequest .= PHP_EOL . '        </Service>';
		$alcohol = $this->shippingOptions['alcohol'];

		foreach ($this->packages as $package)
		{
			$this->rateRequest .= $package->rateRequest($alcohol);
		}

		$this->rateRequest .= PHP_EOL . '    </Shipment>';
		$this->rateRequest .= PHP_EOL . '</RatingServiceSelectionRequest>' . PHP_EOL;

		//echo $this->rateRequest;
	}

	/**
	 * Get Package
	 *
	 * @return \Pop\Shipping\PackageAdapter\Ups
	 */
	public function getPackage()
	{
		return new \Pop\Shipping\PackageAdapter\Ups();
	}

}
