<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
/**
 * @package fabrik
 * @Copyright (C) Rob Clayburn
 * @version $Revision: 1.3 $
 */

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'plugin.php');

class plgFabrik_Validationrule extends FabrikPlugin
{

	var $_pluginName = null;

	var $_counter = null;

	var $pluginParams = null;

	var $_rule = null;
	
	/** @var string if true validation uses its own icon, if not reverts to string value */
	protected $icon = true;

	/**
	 * validate the elements data against the rule
	 * @param string data to check
	 * @param object element
	 * @param int plugin sequence ref
	 * @return bol true if validation passes, false if fails
	 */

	function validate($data, &$element, $c)
	{
		return true;
	}

	/**
	 * looks at the validation condition & evaulates it
	 * if evaulation is true then the validation rule is applied
	 *@return bol apply validation
	 */

	function shouldValidate($data, $c)
	{
		$params = $this->getParams();
		$post	= JRequest::get('post');
		$v = (array)$params->get($this->_pluginName .'-validation_condition');
		if (!array_key_exists($c, $v)) {
			return true;
		}
		$condition = $v[$c];
		if ($condition == '') {
			return true;
		}
		
		$w = new FabrikWorker();

		// $$$ rob merge join data into main array so we can access them in parseMessageForPlaceHolder()
		$joindata = JArrayHelper::getValue($post, 'join', array());
		foreach ($joindata as $joinid => $joind) {
			foreach ($joind as $k => $v) {
				if ($k !== 'rowid') {
					$post[$k] = $v;
				}
			}
		}

		$condition = trim($w->parseMessageForPlaceHolder($condition, $post));
		
		$res = @eval($condition);
		if (is_null($res)) {
			return true;
		}
		return $res;
	}

	function getParams()
	{
		return $this->elementModel->getParams();
	}

 	function getPluginParams()
	{
		if (!isset($this->pluginParams)) {
			$this->pluginParams = $this->_loadPluginParams();
		}
		return $this->pluginParams;
	}

	function _loadPluginParams()
	{
		return $this->elementModel->getParams();
	}

	function &getValidationRule()
	{
		if (!$this->_rule) {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'tables');
			$row = FabTable::getInstance('Validationrule', 'FabrikTable');
			$row->load($this->_id);
			$this->_rule = $row;
		}
		return $this->_rule;
	}

	/**
	 * get the warning message
	 *
	 * @return string
	 */

	function getMessage($c)
	{
		$params = $this->getParams();
		$v = $params->get($this->_pluginName .'-message', JText::_('COM_FABRIK_FAILED_VALIDATION'), '_default', 'array', $c);
		return $v[$c];
	}

	/**
	 * gets the validation rule icon
	 * @param object element model
	 * @param int $c repeat group counter
	 * @param string $tmpl =
	 */
	
	public function getIcon($elementModel, $c = 0, $tmpl = '')
	{
		$name = $this->icon === true ? $this->_pluginName : $this->icon;
		$label = '<span>'.$this->getLabel($elementModel, $c).'</span>';
		$str = FabrikHelperHTML::image($name.'.png', 'form', $tmpl, array('class' => 'fabrikTip', 'title' => $label));
		return $str;
	}
	
	/**
	 * gets the hover/alt text that appears over the validation rule icon in the form
	 * @param object element model
	 * @param int repeat group counter
	 * @return string label
	 */
	
	protected function getLabel($elementModel, $c)
	{
		return JText::_('PLG_VALIDATIONRULE_'.strtoupper($this->_pluginName).'_LABEL');
	}
}
?>
