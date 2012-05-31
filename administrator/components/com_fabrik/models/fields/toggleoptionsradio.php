<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a radio list which will toggle visibility of a specified group
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

class JFormFieldToggleoptionsradio extends JFormFieldRadio

{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'ToggleOptionsRadio';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		$script = "head.ready(function() {
		var s = $('".$this->id."').getElements('input').filter(function(e){
		return (e.checked);
		});
		if(s[0].get('value') == '".$this->element['hide']."'){
			$('".$this->element['toggle']."').hide();
		}
			$('".$this->id."').getElements('input').addEvent('change', function(e){
				if(e.target.checked == true){
					var v = e.target.get('value');
					if(v == '".$this->element['show']."') {
						$('".$this->element['toggle']."').show();
					} else{
						if(v == '".$this->element['hide']."') {
							$('".$this->element['toggle']."').hide();
						}
					}
				}
			});
		})";
		FabrikHelperHTML::addScriptDeclaration($script);
		return parent::getInput();

	}

}