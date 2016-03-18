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
 * Shipping adapter interface
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
interface AdapterInterface
{
    /**
     * @param      $weight
     * @param null $unit
     *
     * @return mixed
     */
    public function setWeight($weight, $unit = null);

    /**
     * @param array $dimensions
     * @param null  $unit
     *
     * @return mixed
     */
    public function setDimensions(array $dimensions, $unit = null);

    /**
     * Get weight
     * @return number
     */
    public function getWeight();
}
