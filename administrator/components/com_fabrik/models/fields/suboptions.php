<?php
/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\ArrayHelper;
use Fabrik\Helpers\Html;
use Fabrik\Helpers\Worker;
use Fabrik\Helpers\Text;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldSuboptions extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Suboptions';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		Text::script('COM_FABRIK_SUBOPTS_VALUES_ERROR');

		$default = new stdClass;
		$default->sub_values = array();
		$default->sub_labels = array();
		$default->sub_initial_selection = array();
		$opts = $this->value == '' ? $default : ArrayHelper::toObject($this->value);
		$j3 = Worker::j3();

		if ($j3)
		{
			$delButton  = '<div class="btn-group">';
			$delButton .= '<a class="btn btn-success" href="#" data-button="addSuboption"><i class="icon-plus"></i> </a>';
			$delButton .= '<a class="btn btn-danger" href="#" data-button="deleteSuboption"><i class="icon-minus"></i> </a>';
			$delButton .= '</div>';
		}
		else
		{
			$delButton = '<a class="removeButton" href="#"><i class="icon-minus"></i> ' . Text::_('COM_FABRIK_DELETE') . '</a>';
		}

		if (is_array($opts))
		{
			$opts['delButton'] = $delButton;
		}
		else
		{
			$opts->delButton = $delButton;
		}

		$opts->id = $this->id;
		$opts->j3 = $j3;
		$opts->defaultMax = (int) $this->getAttribute('default_max', 0);
		$opts = json_encode($opts);
		$script[] = "window.addEvent('domready', function () {";
		$script[] = "\tnew Suboptions('$this->name', $opts);";
		$script[] = "});";
		Html::script('administrator/components/com_fabrik/models/fields/suboptions.js', implode("\n", $script));
		$html = array();

		if (!$j3)
		{
			$html[] = '<div style="float:left;width:100%">';
		}

		$html[] = '<table class="table table-striped" style="width: 100%" id="' . $this->id . '">';
		$html[] = '<thead>';
		$html[] = '<tr style="text-align:left">';
		$html[] = '<th style="width: 5%"></th>';
		$html[] = '<th style="width: 30%">' . Text::_('COM_FABRIK_VALUE') . '</th>';
		$html[] = '<th style="width: 30%">' . Text::_('COM_FABRIK_LABEL') . '</th>';
		$html[] = '<th style="width: 10%">' . Text::_('COM_FABRIK_DEFAULT') . '</th>';

		if ($j3)
		{
			$html[] = '<th style="width: 20%"><a class="btn btn-success" href="#" data-button="addSuboption"><i class="icon-plus"></i> </a></th>';
		}

		$html[] = '</tr>';
		$html[] = '</thead>';
		$html[] = '<tbody></tbody>';
		$html[] = '</table>';

		if (!$j3)
		{
			$html[] = '<ul id="sub_subElementBody" class="subelements">';
			$html[] = '<li></li>';
			$html[] = '</ul>';
			$html[] = '<a class="addButton" href="#" id="addSuboption"><i class="icon-plus"></i> ' . Text::_('COM_FABRIK_ADD') . '</a></div>';
		}

		Html::framework();
		Html::iniRequireJS();

		return implode("\n", $html);
	}
}
