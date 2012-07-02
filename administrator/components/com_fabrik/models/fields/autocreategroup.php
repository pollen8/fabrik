<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders an eval element
 *
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldAutoCreateGroup extends JFormFieldRadio
{
	/**
	 * Element name
	 * @var		string
	 */
	protected $name = 'AutoCreateGroup';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 */

	public function getInput()
	{
		$this->value = $this->form->getValue('id') == 0 ? 1 : 0;
		return parent::getInput();
	}
}
