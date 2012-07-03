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
jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Renders a radio list which will toggle visibility of a specified group
 *
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldToggleoptionsradio extends JFormFieldRadio
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'ToggleOptionsRadio';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		$script = "head.ready(function() {
		var s = $('" . $this->id . "').getElements('input').filter(function(e){
		return (e.checked);
		});
		if(s[0].get('value') == '" . $this->element['hide'] . "'){
			$('" . $this->element['toggle'] . "').hide();
		}
			$('" . $this->id
			. "').getElements('input').addEvent('change', function(e){
				if(e.target.checked == true){
					var v = e.target.get('value');
					if(v == '" . $this->element['show'] . "') {
						$('" . $this->element['toggle'] . "').show();
					} else{
						if(v == '" . $this->element['hide'] . "') {
							$('" . $this->element['toggle'] . "').hide();
						}
					}
				}
			});
		})";
		FabrikHelperHTML::addScriptDeclaration($script);
		return parent::getInput();

	}

}
