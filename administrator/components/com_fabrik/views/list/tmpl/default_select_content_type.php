<?php
/**
 * Admin List Confirm Copy Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<?php echo $this->form->renderFieldset('details'); ?>

	<?php foreach ($this->data as $key => $value) :
		if (is_array($value)) :
			foreach ($value as $key2 => $value2) :?>
				<input type="hidden" name="<?php echo 'jform[' . $key . '][' . $key2 . ']'; ?>" value="<?php echo $value2; ?>" />
			<?php endforeach;
		else: ?>
			<input type="hidden" name="jform[<?php echo $key; ?>]" value="<?php echo $value; ?>" />
		<?php endif;
	endforeach; ?>

	<input type="hidden" name="option" value="com_fabrik" />
	<input type="hidden" name="task" value="list.doSave" />
	<?php echo JHtml::_('form.token'); ?>
</form>