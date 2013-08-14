<?php
/**
 * Form Field class for the Joomla Platform.
 * An ace.js code editor field
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('textarea');

/**
 * Form Field class for the Joomla Platform.
 * An ace.js code editor field
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @see         JEditor
 * @since       1.6
 */
class JFormFieldFabrikeditor extends JFormFieldTextArea
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Fabrikeditor';

	/**
	 * Method to get the field input markup for the editor area
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */

	protected function getInput()
	{
		$version = new JVersion;
		if ($version->RELEASE == 2.5)
		{
			// Initialize some field attributes.
			$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
			$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
			$columns = $this->element['cols'] ? ' cols="' . (int) $this->element['cols'] . '"' : '';
			$rows = $this->element['rows'] ? ' rows="' . (int) $this->element['rows'] . '"' : '';
			$required = $this->required ? ' required="required" aria-required="true"' : '';

			// Initialize JavaScript field attributes.
			$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

			return '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . $required . '>'
				. $this->value . '</textarea>';

		}
		$mode = $this->element['mode'] ? $this->element['mode'] : 'html';
		$theme = $this->element['theme'] ? $this->element['theme'] : 'clouds';
		$height = $this->element['height'] ? $this->element['height'] : '200px';
		$width = $this->element['width'] ? $this->element['width'] : '300px';
		FabrikHelperHTML::framework();
		FabrikHelperHTML::iniRequireJS();

		$script = '
			var MyEditor = ace.edit("' . $this->id . '-ace");
			MyEditor.setTheme("ace/theme/' . $theme . '");
   			MyEditor.getSession().setMode("ace/mode/' . $mode . '");
			window.addEvent("form.save", function () {
   				if (typeOf(document.id("' . $this->id . '")) !== "null") {
   					document.id("' . $this->id . '").value = MyEditor.getValue();
   				}
   			});
			';

		$shim = array();
		$deps = new stdClass;
		$deps->deps = array();

		$src = array('media/com_fabrik/js/lib/ace/src-min-noconflict/ace.js');
		if ($mode !== 'javascript')
		{
			$deps->deps[] = 'fabrik/lib/ace/src-min-noconflict/mode-' . $mode;
		}

		$shim['fabrik/lib/ace/src-min-noconflict/ace'] = $deps;
		FabrikHelperHTML::iniRequireJs($shim);
		FabrikHelperHTML::script($src, $script);

		echo '<style type="text/css" media="screen">
    #' . $this->id . '-ace {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    	 #' . $this->id . '-container {
        position: relative;
       	width: ' . $width . ';
    	 	height: ' . $height . ';
    }
</style>';
		$this->element['cols'] = 1;
		$this->element['rows'] = 1;
		$editor = parent::getInput();

		// For element js event code.
		return '<div id="' . $this->id . '-container"><div id="' . $this->id . '-ace">' . $this->value . '</div>' . $editor . '</div>';
	}

}
