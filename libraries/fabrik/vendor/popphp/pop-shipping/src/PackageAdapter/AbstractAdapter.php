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
	 * @param  number    $weight
	 * @param string $unit
	 *
	 * @return \Pop\Shipping\PackageAdapter\AbstractAdapter
	 */
	public function setWeight($weight, $unit = 'LB')
	{
		if ($unit == 'LB' || $unit == 'KG')
		{
			$this->weight['Units'] = $unit;
		}

		$this->weight['Value'] = $weight;

		return $this;
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
	 * @param array $dimensions key = [length, width, height]
	 * @param string  $unit
	 *
	 * @return \Pop\Shipping\PackageAdapter\AbstractAdapter
	 */
	public function setDimensions(array $dimensions, $unit = 'IN')
	{
		if ($unit == 'IN' || $unit == 'CM')
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

		return $this;
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
