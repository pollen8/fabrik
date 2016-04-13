<?php
/**
 * Admin Elements Batch processing tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Text;

$published = $this->state->get('filter.published');
?>
<fieldset class="batch">
	<legend><?php echo Text::_('COM_FABRIK_BATCH_OPTIONS');?></legend>

	<label for="batchview_access"><?php echo Text::_('COM_FABRIK_ACCESS_VIEWABLE_ELEMENT')?></label>
	<?php echo JHtml::_('access.level', 'batch[params][view_access]', '', '', false); ?>


	<label for="batchaccess"><?php echo Text::_('COM_FABRIK_ACCESS_EDITABLE_ELEMENT')?></label>
	<?php echo JHtml::_('access.level', 'batch[access]', '', '', false); ?>

	<button type="submit" onclick="Joomla.submitbutton('elements.batch');">
		<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button" onclick="document.id('batchaccess').selectedIndex = '';document.id('batchparamsview_access').selectedIndex ='';">
		<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
