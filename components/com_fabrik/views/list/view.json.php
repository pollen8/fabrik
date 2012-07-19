<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewlist extends JViewLegacy
{

	/**
	 * display a json object representing the table data.
	 * Not used for updating fabrik tables, use raw view for that, here in case you want to export the data to another application
	 */

	function display()
	{
		$model = $this->getModel();
		$model->setId(JRequest::getInt('listid'));
		$data = $model->getData();
		echo json_encode($data);
	}

}
?>