<?php
/**
 * Admin Element Edit:access Tmpl
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
<?php echo JHtml::_('tabs.panel', JText::_('COM_FABRIK_GROUP_LABAEL_RULES_DETAILS'), 'element-access');?>
<fieldset class="adminform">
	<ul class="adminformlist">
	<?php
		foreach ($this->form->getFieldset('access') as $field) :?>
		<li>
			<?php echo $field->label; ?><?php echo $field->input; ?>
		</li>
		<?php endforeach;
		?>
		<?php
		foreach ($this->form->getFieldset('access2') as $field) :?>
		<li>
			<?php echo $field->label; ?><?php echo $field->input; ?>
		</li>
		<?php endforeach; ?>
	</ul>
</fieldset>