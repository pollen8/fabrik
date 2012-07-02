<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list which will toggle visibility of a specified group
 *
 * @author 		Rob Clayburn
 * @package 	Joomla
 * @subpackage		Fabrik
 * @since		1.5
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldToggleoptionslist extends JFormFieldList

{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $name = 'ToggleOptionsList';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		$script = "head.ready(function() {

		if($('".$this->id."').get('value') == '".$this->element['hide']."'){
			$('".$this->element['toggle']."').hide();
		}
			$('".$this->id."').addEvent('change', function(e){
				var v = e.target.get('value');
				if(v == '".$this->element['show']."') {
					$('".$this->element['toggle']."').show();
				} else{
					if(v == '".$this->element['hide']."') {
						$('".$this->element['toggle']."').hide();
					}
				}
			});
		})";
		FabrikHelperHTML::addScriptDeclaration($script);
		return parent::getInput();

	}

}