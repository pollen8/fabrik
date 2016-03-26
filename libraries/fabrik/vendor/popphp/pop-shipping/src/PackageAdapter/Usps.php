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
class Usps extends AbstractAdapter
{

	/**
	 * Container type
	 * @var string
	 */
	protected $container = 'VARIABLE';

	/**
	 * Container size
	 * @var string
	 */
	protected $containerSize = 'REGULAR';

	/**
	 * Package dimensions
	 *
	 * @var array
	 */
	protected $dimensions = [
		'Width' => null,
		'Length' => null,
		'Height' => null,
		'Girth' => null
	];

	/**
	 * Package weight
	 *
	 * @var array
	 */
	protected $weight = [
		'Pounds' => 0,
		'Ounces' => 0
	];

	/**
	 * Set container
	 *
	 * @param  string $container
	 * @throws Exception
	 * @return void
	 */
	public function setContainer($container = 'RECTANGULAR')
	{
		// Not sure that NONRECTANGULAR is still valid.
		if (in_array($container, ['RECTANGULAR', 'NONRECTANGULAR', 'VARIABLE'])) {
			$this->container = $container;
		} else {
			throw new Exception('Error: The container type must be RECTANGULAR or NONRECTANGULAR.');
		}
	}

	/**
	 * Set dimensions
	 *
	 * @param  string $weight
	 * @param  string $unit
	 *
	 * @return void
	 */
	public function setWeight($weight, $unit = null)
	{
		if (is_float($weight))
		{
			$lbs = floor($weight);
			$ozs = round(16 * ($weight - $lbs), 2);
		}
		else
		{
			$lbs = $weight;
			$ozs = 0;
		}
		$this->weight['Pounds'] = $lbs;
		$this->weight['Ounces'] = $ozs;
	}

	/**
	 * Set dimensions
	 *
	 * @param  array  $dimensions
	 * @param  string $unit
	 *
	 * @return void
	 */
	public function setDimensions(array $dimensions, $unit = null)
	{
		parent::setDimensions($dimensions, $unit);

		foreach ($dimensions as $key => $value)
		{
			switch (strtolower($key))
			{
				case 'girth':
					$this->dimensions['Girth'] = $value;
					break;
			}
		}
	}

	/**
	 * @param bool   $alcohol Package contains alcohol
	 *
	 * @return string
	 */
	public function rateRequest($alcohol = false)
	{

		//$request = PHP_EOL . '    <Package ID="' . $id . '">';

	/*	$request .= PHP_EOL . '        <Service>ALL</Service>';
		$request .= PHP_EOL . '        <ZipOrigination>' . $shipFrom['ZipOrigination'] . '</ZipOrigination>';
		$request .= PHP_EOL . '        <ZipDestination>' . $shipTo['ZipDestination'] . '</ZipDestination>';*/
		$request = PHP_EOL . '        <Pounds>' . $this->weight['Pounds'] . '</Pounds>';
		$request .= PHP_EOL . '        <Ounces>' . $this->weight['Ounces'] . '</Ounces>';
		$request .= PHP_EOL . '        <Container>' . $this->container . '</Container>';
		$request .= PHP_EOL . '        <Size>' . $this->containerSize . '</Size>';

		if ((null !== $this->dimensions['Length']) &&
			(null !== $this->dimensions['Width']) &&
			(null !== $this->dimensions['Height'])
		)
		{
			$request .= PHP_EOL . '        <Width>' . $this->dimensions['Width'] . '</Width>';
			$request .= PHP_EOL . '        <Length>' . $this->dimensions['Length'] . '</Length>';
			$request .= PHP_EOL . '        <Height>' . $this->dimensions['Height'] . '</Height>';

			if (null == $this->dimensions['Girth'])
			{
				$this->dimensions['Girth'] = (2 * $this->dimensions['Width']) + (2 * $this->dimensions['Height']);
			}

			$request .= PHP_EOL . '        <Girth>' . $this->dimensions['Girth'] . '</Girth>';
		}

		return $request;
	}
}
