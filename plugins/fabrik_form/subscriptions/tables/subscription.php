<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php';

/**
 * Fabsub subscription table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.1
 */

class FabrikTableSubscription extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('#__fabrik_subs_subscriptions', 'id', $db);
	}

	/**
	 * Expire the sub
	 *
	 * @param   strng  $msg  reason for expiration
	 *
	 * @return  bool
	 */

	public function expire($msg = 'IPN expireSub')
	{
		$now = JFactory::getDate()->toSql();
		$this->status = 'Expired';
		$this->eot_date = $now;
		$this->eot_cause = $msg;
		return $this->store();
	}

	/**
	 * Activate the sub
	 *
	 * @return  bool
	 */

	public function activate()
	{
		$now = JFactory::getDate()->toSql();
		$this->status = 'Active';
		$this->lastpay_date = $now;
		return $this->store();
	}

	/**
	 * Refund the sub - performed by merchant
	 *
	 * @return  bool
	 */

	public function refund()
	{
		$now = JFactory::getDate()->toSql();
		$this->status = 'Refunded';
		$this->cancel_date = $now;
		$this->eot_date = $now;
		$this->eot_cause = 'IPN Refund';
		return $this->store();
	}

	/**
	 * Cancel the sub - performed by user
	 *
	 * @return bool
	 */
	public function cancel()
	{
		$now = JFactory::getDate()->toSql();
		$this->status = 'Cancelled';
		$this->cancel_date = $now;
		$this->eot_cause = 'IPN Cancel';
		return $this->store();
	}

}
