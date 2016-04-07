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
 * FedEx shipping package class
 *
 * Whilst rate requests can send mutliple packages
 * for the actual shipping you have to perform one request per package
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
	 * @param array $opts / alcohol Package contains alcohol
	 * @return string
	 */
	public function rateRequest($opts = [])
	{
		$alcohol = array_key_exists('alcohol', $opts) ? $opts['alcohol'] : false;
		$insuranceValue = array_key_exists('insuranceValue', $opts) ? $opts['insuranceValue'] : 0;
		$insuranceCurrency = array_key_exists('insuranceCurrency', $opts) ? $opts['insuranceCurrency'] : 'USD';
		$sequenceNumber = array_key_exists('SequenceNumber', $opts) ? $opts['SequenceNumber'] : 1;
		$GroupPackageCount = array_key_exists('GroupPackageCount', $opts) ? $opts['GroupPackageCount'] : 1;
		$CustomerReferences = array_key_exists('CustomerReferences', $opts) ? $opts['CustomerReferences'] : [];
		$RecipientType = array_key_exists('CustomerReferences', $opts) ? $opts['CustomerReferences'] : '';

		// @TODO - insured value & amount & customer reference
		$packageLineItem = [
			'SequenceNumber' => $sequenceNumber,
			'GroupPackageCount' => $GroupPackageCount,
			'InsuredValue' => [
				'Amount' => $insuranceValue,
				'Currency' => $insuranceCurrency
			],
			'Weight' => $this->weight,
			'Dimensions' => $this->dimensions,
			'CustomerReferences' => $CustomerReferences
		];

		if ($alcohol)
		{
			$packageLineItem['SpecialServicesRequested'] = [
				'SpecialServiceTypes' => 'ALCOHOL',
				'AlcoholDetail' => [
					'RecipientType' => $RecipientType
				]
			];
		}

		return $packageLineItem;
	}
}
