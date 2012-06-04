<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a list of connections
 *
 * @author Rob Clayburn
 * @package Joomla
 * @subpackage Fabrik
 * @since	1.6
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldConnections extends JFormFieldList
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Connections';


	function getOptions()
	{

		// Initialize variables.
		$options = array();

		$db	= FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);

		$query->select('id AS value, description AS text, '.$db->nameQuote('default'));
		$query->from('#__fabrik_connections AS c');
		$query->where('published = 1');
		$query->order('host');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
		$sel = JHtml::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT'));
		$sel->default = false;
		array_unshift($options, $sel);
		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		if ((int)$this->form->getValue('id') == 0 && $this->value == '') {
			// default to default connection on new form where no value specified
			$options = (array) $this->getOptions();
			foreach ($options as $opt) {
				if ($opt->default == 1) {
					$this->value = $opt->value;
				}
			}
		}
		if ((int)$this->form->getValue('id') == 0 || !$this->element['readonlyonedit']) {
			return parent::getInput();
		} else {
			$options = (array)$this->getOptions();
			$v = '';
			foreach ($options as $opt) {
				if ($opt->value == $this->value) {
					$v = $opt->text;
				}
			}
		}
		return '<input type="hidden" value="'.$this->value.'" name="'.$this->name.'" />'.
		'<input type="text" value="'.$v.'" name="connection_justalabel" class="readonly" readonly="true" />';
	}

}