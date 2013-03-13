<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.time
 * @author      Jaanus Nurmoja <email@notknown.com>
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render time dropdowns - derivated from birthday element
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.time
 * @since       3.0
 */

class PlgFabrik_ElementTime extends PlgFabrik_Element
{

	public $hasSubElements = true;

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TIME';

	/**
	 * Draws the form element
	 *
	 * @param   array  $data           Dat to prepopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string  returns element html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$db = JFactory::getDbo();
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$bits = array();
		/*
		 * $$$ rob - not sure why we are setting $data to the form's data
		 * but in table view when getting read only filter value from url filter this
		 * _form_data was not set to no readonly value was returned
		 * added little test to see if the data was actually an array before using it
		 */
		$formModel = $this->getFormModel();
		if (is_array($formModel->data))
		{
			$data = $formModel->data;
		}
		$value = $this->getValue($data, $repeatCounter);
		$sep = $params->get('time_separatorlabel', JText::_(':'));
		$fd = $params->get('details_time_format', 'H:i:s');
		if (!$this->isEditable())
		{
			if ($value)
			{
				// Avoid 0000-00-00
				list($hour, $min, $sec) = strstr($value, ':') ? explode(':', $value) : explode(',', $value);

				// $$$ rob - all this below is nice but ... you still need to set a default
				$detailvalue = '';
				if ($fd == 'H:i:s')
				{
					$detailvalue = $hour . $sep . $min . $sep . $sec;
				}
				else
				{
					if ($fd == 'H:i')
					{
						$detailvalue = $hour . $sep . $min;
					}
					if ($fd == 'i:s')
					{
						$detailvalue = $min . $sep . $sec;
					}
				}
				$value = $this->replaceWithIcons($detailvalue);
				return ($element->hidden == '1') ? "<!-- " . $detailvalue . " -->" : $detailvalue;
			}
			else
			{
				return '';
			}
		}
		else
		{
			// Wierdness for failed validaion
			$value = strstr($value, ',') ? (explode(',', $value)) : explode(':', $value);
			$hourvalue = JArrayHelper::getValue($value, 0);
			$minvalue = JArrayHelper::getValue($value, 1);
			$secvalue = JArrayHelper::getValue($value, 2);

			$hours = array(JHTML::_('select.option', '', $params->get('time_hourlabel', JText::_('HOUR'))));
			for ($i = 0; $i < 24; $i++)
			{
				$v = str_pad($i, 2, '0', STR_PAD_LEFT);
				$hours[] = JHTML::_('select.option', $v, $i);
			}
			$mins = array(JHTML::_('select.option', '', $params->get('time_minlabel', JText::_('MINUTE'))));

			// Siin oli enne $monthlabels, viisin ülespoole
			for ($i = 0; $i < 60; $i++)
			{
				$i = str_pad($i, 2, '0', STR_PAD_LEFT);
				$mins[] = JHTML::_('select.option', $i);
			}
			$secs = array(JHTML::_('select.option', '', $params->get('time_seclabel', JText::_('SECOND'))));
			for ($i = 0; $i < 60; $i++)
			{
				$i = str_pad($i, 2, '0', STR_PAD_LEFT);
				$secs[] = JHTML::_('select.option', $i);
			}
			$errorCSS = $this->elementError != '' ? " elementErrorHighlight" : '';
			$attribs = 'class="fabrikinput inputbox' . $errorCSS . '"';
			$str = array();
			$str[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';

			// $name already suffixed with [] as element hasSubElements = true
			if ($fd != 'i:s')
			{
				$str[] = JHTML::_('select.genericlist', $hours, preg_replace('#(\[\])$#', '[0]', $name), $attribs, 'value', 'text', $hourvalue) . ' '
					. $sep;
			}
			$str[] = JHTML::_('select.genericlist', $mins, preg_replace('#(\[\])$#', '[1]', $name), $attribs, 'value', 'text', $minvalue);
			if ($fd != 'H:i')
			{
				$str[] = $sep . ' '
					. JHTML::_('select.genericlist', $secs, preg_replace('#(\[\])$#', '[2]', $name), $attribs, 'value', 'text', $secvalue);
			}
			$str[] = '</div>';
			return implode("\n", $str);
		}
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		return $this->_indStoreDBFormat($val);
	}

	/**
	 * get the value to store the value in the db
	 *
	 * @param   mixed  $val  (array normally but string on csv import or copy rows)
	 *
	 * @return  string  yyyy-mm-dd
	 */

	private function _indStoreDBFormat($val)
	{
		if (is_array($val) && implode($val) != '')
		{
			return rtrim(str_replace('', '00', $val[0]) . ':' . str_replace('', '00', $val[1]) . ':' . str_replace('', '00', $val[2]), ':');
		}
		return $val;
	}

	/**
	 * Used in isempty validation rule
	 *
	 * @param   array  $data           Data
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return bool
	 */

	public function dataConsideredEmpty($data, $repeatCounter)
	{
		$data = str_replace(null, '', $data);
		if (strstr($data, ','))
		{
			$data = explode(',', $data);
		}
		$data = (array) $data;
		foreach ($data as $d)
		{
			if (trim($d) == '')
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->separator = $params->get('time_separatorlabel', ':');
		return array('FbTime', $id, $opts);
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$db = FabrikWorker::getDbo();
		$params = $this->getParams();
		$groupModel = $this->getGroup();
		/*
		 * Jaanus: removed condition canrepeat() from renderListData:
		 * weird result such as ["00:03:45","00 when not repeating but still join and merged. Using isJoin() instead
		 */
		$data = $groupModel->isJoin() ? FabrikWorker::JSONtoData($data, true) : array($data);
		$data = (array) $data;
		$ft = $params->get('list_time_format', 'H:i:s');
		$sep = $params->get('time_separatorlabel', JText::_(':'));
		$format = array();

		foreach ($data as $d)
		{
			if ($d)
			{
				list($hour, $min, $sec) = explode(':', $d);
				$hms = $hour . $sep . $min . $sep . $sec;
				$hm = $hour . $sep . $min;
				$ms = $min . $sep . $sec;
				$timedisp = '';
				if ($ft == "H:i:s")
				{
					$timedisp = $hms;
				}
				else
				{
					if ($ft == "H:i")
					{
						$timedisp = $hm;
					}
					if ($ft == "i:s")
					{
						$timedisp = $ms;
					}
				}
				$format[] = $timedisp;
			}
			else
			{
				$format[] = '';
			}
		}
		$data = json_encode($format);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Turn form value into email formatted value
	 *
	 * @param   mixed  $value          element value
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  group repeat counter
	 *
	 * @return  string  email formatted value
	 */

	protected function _getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		$params = $this->getParams();
		$sep = $params->get('time_separatorlabel', JText::_(':'));
		$value = implode($sep, $value);
		return $value;
	}

}
