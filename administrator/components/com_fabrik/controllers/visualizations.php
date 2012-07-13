<?php
/**
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

require_once('fabcontrolleradmin.php');

/**
 * Visualization list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikControllerVisualizations extends FabControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_VISUALIZATIONS';

	protected $view_item = 'visualizations';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);

	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */

	public function &getModel($name = 'Visualization', $prefix = 'FabrikModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}