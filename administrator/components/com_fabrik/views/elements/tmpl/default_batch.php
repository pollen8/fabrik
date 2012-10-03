<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_FABRIK_BATCH_OPTIONS');?></legend>

	<label for="batchaccess"><?php echo JText::_('COM_FABRIK_ACCESS_EDITABLE_ELEMENT')?></label>
	<?php echo JHtml::_('access.level', 'batch[access]', '', '', false); ?>

	<label for="batchview_access"><?php echo JText::_('COM_FABRIK_ACCESS_VIEWABLE_ELEMENT')?></label>
	<?php echo JHtml::_('access.level', 'batch[params][view_access]', '', '', false); ?>



	<button type="submit" onclick="Joomla.submitbutton('elements.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button" onclick="document.id('batchaccess').selectedIndex = '';document.id('batchparamsview_access').selectedIndex ='';">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
