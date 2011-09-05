<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 *
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.5
 */
class FabrikViewVisualization extends JView
{
	protected $form;
	protected $item;
	protected $state;
	protected $pluginFields;

	/**
	 * Display the view
	 */

	public function display($tpl = null)
	{
		echo "viz admin raw display";
	}

}
