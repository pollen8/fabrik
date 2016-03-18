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
 * Package adapter abstract class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */

abstract class AbstractAdapter implements AdapterInterface
{
	protected $weight = [];

	protected $dimensions = [];

	/**
	 * @param      $weight
	 * @param null $unit
	 *
	 * @return mixed
	 */
	public function setWeight($weight, $unit = null)
	{
		if ((null !== $unit) && (($unit == 'LB') || ($unit == 'KG')))
		{
			$this->weight['Units'] = $unit;
		}

		$this->weight['Value'] = $weight;
	}

	/**
	 * Get weight
	 * @return number
	 */
	public function getWeight()
	{
		return $this->weight['Value'];
	}

	/**
	 * @param array $dimensions
	 * @param null  $unit
	 *
	 * @return mixed
	 */
	public function setDimensions(array $dimensions, $unit = null)
	{
		if ((null !== $unit) && (($unit == 'IN') || ($unit == 'CM')))
		{
			$this->dimensions['Units'] = $unit;
		}

		foreach ($dimensions as $key => $value)
		{
			switch (strtolower($key))
			{
				case 'length':
					$this->dimensions['Length'] = $value;
					break;
				case 'width':
					$this->dimensions['Width'] = $value;
					break;
				case 'height':
					$this->dimensions['Height'] = $value;
					break;
			}
		}
	}

	/**
	 * @param bool $alcohol Package contains alcohol
	 * @return string
	 */
	public function rateRequest($alcohol = false)
	{
		$request = [
			'SequenceNumber'    => 1,
			'GroupPackageCount' => 1,
			'Weight'            => $this->weight
		];

		if ((null !== $this->dimensions['Length']) &&
			(null !== $this->dimensions['Width']) &&
			(null !== $this->dimensions['Height'])) {
			$request['Dimensions'] = $this->dimensions;
		}

		return $request;
	}

}
