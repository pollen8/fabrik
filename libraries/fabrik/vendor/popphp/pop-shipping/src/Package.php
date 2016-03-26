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
namespace Pop\Shipping;

/**
 * Shipping class
 *
 * @category   Pop
 * @package    Pop_Shipping
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Package
{

    /**
     * Package adapter
     * @var mixed
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the package object
     *
     * @param  PackageAdapter\AbstractAdapter $adapter
     * @return Package
     */
    public function __construct(PackageAdapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Access the adapter
     *
     * @return Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }


}
