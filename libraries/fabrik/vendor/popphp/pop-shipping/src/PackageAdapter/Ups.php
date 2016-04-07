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
namespace Pop\Shipping\PackageAdapter;

/**
 * Usps shipping adapter class
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
	 * Package type
	 *
	 * @var string
	 */
	protected $packageType = '02';

	/**
	 * Pickup Types
	 *
	 * @var array
	 */
	protected static $packagingTypes = [
		'00' => 'UNKNOWN',
		'01' => 'UPS Letter',
		'02' => 'Package',
		'03' => 'Tube',
		'04' => 'Pak',
		'21' => 'Express Box',
		'24' => '25KG Box',
		'25' => '10KG Box',
		'30' => 'Pallet',
		'2a' => 'Small Express Box',
		'2b' => 'Medium Express Box',
		'2c' => 'Large Express Box'
	];

	/**
	 * Set dimensions
	 *
	 * @param  array  $dimensions
	 * @param  string $unit
	 *
	 * @return Ups
	 */
	public function setDimensions(array $dimensions, $unit = null)
	{
		parent::setDimensions($dimensions, $unit);

		if ((null !== $unit) && (($unit == 'IN') || ($unit == 'CM')))
		{
			$this->dimensions['UnitOfMeasurement'] = $unit;
		}

		return $this;
	}

	/**
	 * Set weight
	 *
	 * @param  string $weight
	 * @param  string $unit
	 *
	 * @return Ups
	 */
	public function setWeight($weight, $unit = null)
	{
		if ((null !== $unit) && (($unit == 'LBS') || ($unit == 'KGS')))
		{
			$this->weight['UnitOfMeasurement'] = $unit;
		}
		else
		{
			$this->weight['UnitOfMeasurement'] = 'LBS';
		}

		$this->weight['Weight'] = $weight;

		return $this;
	}

	/**
	 * Set package type
	 *
	 * @param  string $code
	 *
	 * @throws Exception
	 * @return Ups
	 */
	public function setPackage($code)
	{
		if (!array_key_exists($code, self::$packagingTypes))
		{
			throw new Exception('Error: That package code does not exist.');
		}

		$this->packageType = $code;

		return $this;
	}

	/**
	 * @param bool $alcohol Package contains alcohol
	 * @return string
	 */
	public function rateRequest($alcohol = false)
	{
		$request = '';
		$request .= PHP_EOL . '        <Package>';
		$request .= PHP_EOL . '            <PackagingType>';
		$request .= PHP_EOL . '                <Code>' . $this->packageType . '</Code>';
		$request .= PHP_EOL . '                <Description>' . self::$packagingTypes[$this->packageType] . '</Description>';
		$request .= PHP_EOL . '            </PackagingType>';
		$request .= PHP_EOL . '            <Description>Rate</Description>';

		if ((null !== $this->dimensions['Length']) &&
			(null !== $this->dimensions['Width']) &&
			(null !== $this->dimensions['Height'])
		)
		{
			$request .= PHP_EOL . '            <Dimensions>';
			$request .= PHP_EOL . '                <UnitOfMeasurement>';
			$request .= PHP_EOL . '                    <Code>' . $this->dimensions['UnitOfMeasurement'] . '</Code>';
			$request .= PHP_EOL . '                </UnitOfMeasurement>';
			$request .= PHP_EOL . '                <Length>' . $this->dimensions['Length'] . '</Length>';
			$request .= PHP_EOL . '                <Width>' . $this->dimensions['Width'] . '</Width>';
			$request .= PHP_EOL . '                <Height>' . $this->dimensions['Height'] . '</Height>';
			$request .= PHP_EOL . '            </Dimensions>';
		}

		$request .= PHP_EOL . '            <PackageWeight>';
		$request .= PHP_EOL . '                <UnitOfMeasurement>';
		$request .= PHP_EOL . '                    <Code>' . $this->weight['UnitOfMeasurement'] . '</Code>';
		$request .= PHP_EOL . '                </UnitOfMeasurement>';
		$request .= PHP_EOL . '                <Weight>' . $this->weight['Weight'] . '</Weight>';
		$request .= PHP_EOL . '            </PackageWeight>';

		if ($alcohol)
		{
			$request .= PHP_EOL . '            	<PackageServiceOptions>';
			$request .= PHP_EOL . '                	<DeliveryConfirmation>';
			// 3 = DeliveryConfirmation AdultSignature Required
			$request .= PHP_EOL . '                    	<DCISType>3</DCISType>';
			$request .= PHP_EOL . '                	</DeliveryConfirmation>';
			$request .= PHP_EOL . '            	</PackageServiceOptions>';
		}

		$request .= PHP_EOL . '        </Package>';

		return $request;
	}
}
