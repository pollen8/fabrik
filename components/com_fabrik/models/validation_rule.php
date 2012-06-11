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

require_once(JPATH_SITE . '/components/com_fabrik/models/plugin.php');

class plgFabrik_Validationrule extends FabrikPlugin
{

	protected $pluginName = null;

	protected $rule = null;

	/** @var string if true validation uses its own icon, if not reverts to string value */
	protected $icon = true;

	/**
	 * validate the elements data against the rule
	 * @param	string	data to check
	 * @param	object	element
	 * @param	int		plugin sequence ref
	 * @param	int		repeat group counter
	 * @return	bool	true if validation passes, false if fails
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		return true;
	}

	/**
	 * looks at the validation condition & evaulates it
	 * if evaulation is true then the validation rule is applied
	 * @param	string	elements data
	 * @param	int		repeat group counter
	 * @return	bool	apply validation
	 */

	function shouldValidate($data, $c)
	{
		$params = $this->getParams();
		$post = JRequest::get('post');
		$v = (array) $params->get($this->pluginName . '-validation_condition');
		if (!array_key_exists($c, $v))
		{
			return true;
		}
		$condition = $v[$c];
		if ($condition == '')
		{
			return true;
		}
		$w = new FabrikWorker();
		// $$$ rob merge join data into main array so we can access them in parseMessageForPlaceHolder()
		$joindata = JArrayHelper::getValue($post, 'join', array());
		foreach ($joindata as $joinid => $joind)
		{
			foreach ($joind as $k => $v)
			{
				if ($k !== 'rowid')
				{
					$post[$k] = $v;
				}
			}
		}
		$condition = trim($w->parseMessageForPlaceHolder($condition, $post));
		$res = @eval($condition);
		if (is_null($res))
		{
			return true;
		}
		return $res;
	}

	function getParams()
	{
		return $this->elementModel->getParams();
	}

	/**
	 * get the warning message
	 * @param	int		validation rule number.
	 * @return	string
	 */

	public function getMessage($c = 0)
	{
		$params = $this->getParams();
		$v = (array) $params->get($this->pluginName . '-message');
		$v = JArrayHelper::getValue($v, $c, '');
		if ($v === '')
		{
			$v = 'COM_FABRIK_FAILED_VALIDATION';
		}
		return JText::_($v);
	}

	/**
	 * @deprecated @since 3.0.5
	 * now show only on validation icon next to the element name and put icons and text inside hover text
	 * gets the validation rule icon
	 * @param	object	element model
	 * @param	int		$c repeat group counter
	 * @param	string	$tmpl =
	 */

	public function getIcon($elementModel, $c = 0, $tmpl = '')
	{
		$name = $this->icon === true ? $this->pluginName : $this->icon;
		if ($this->allowEmpty($elementModel, $c))
		{
			$name .= '_allowempty';
		}
		$label = '<span>' . $this->getLabel($elementModel, $c) . '</span>';
		$str = FabrikHelperHTML::image($name.'.png', 'form', $tmpl, array('class' => 'fabrikTip ' . $this->pluginName, 'opts' => "{notice:true}",  'title' => $label));
		return $str;
	}
	
	public function getHoverText($elementModel, $c = 0, $tmpl = '')
	{
		$name = $this->icon === true ? $this->pluginName : $this->icon;
		if ($this->allowEmpty($elementModel, $c))
		{
			$name .= '_allowempty';
		}
		$i = FabrikHelperHTML::image($name.'.png', 'form', $tmpl, array('class' => $this->pluginName));
		return $i .  $this->getLabel($elementModel, $c) ;
	}

	/**
	 * gets the hover/alt text that appears over the validation rule icon in the form
	 * @param	object	element model
	 * @param	int		repeat group counter
	 * @return	string	label
	 */

	protected function getLabel($elementModel, $c)
	{
		if ($this->allowEmpty($elementModel, $c))
		{
			return JText::_('PLG_VALIDATIONRULE_' . strtoupper($this->pluginName) . '_ALLOWEMPTY_LABEL');
		}
		else
		{
			return JText::_('PLG_VALIDATIONRULE_' . strtoupper($this->pluginName) . '_LABEL');
		}
	}

	/**
	* does the validation allow empty value?
	* Default is false, can be overrideen on per-validation basis (such as isnumeric)
	* @param	object	element model
	* @param	int		repeat group counter
	* @return	bool
	*/

	protected function allowEmpty($elementModel, $c)
	{
		return false;
	}
}
?>
