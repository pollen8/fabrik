<?php
/**
 * Admin Element Edit:validations Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die;
?>
<?php echo JHtml::_('tabs.panel', JText::_('COM_FABRIK_VALIDATIONS'), 'settings');
?>
<fieldset class="adminform">
	<div id="plugins" class="pane-sliders"></div>
	<a href="#" id="addPlugin" class="addButton"><?php echo JText::_('COM_FABRIK_ADD'); ?></a>
</fieldset>