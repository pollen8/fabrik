<?php /*
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since		1.6
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;
?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="fabrik-form" class="form-validate">
	<table class="adminlist">
	<tbody>
		<?php for ($i=0; $i < count($this->items); $i++) {
		$element = $this->items[$i];?>
  		<tr>
  		<td>
  			<input type="text" value="<?php echo $element->name?>" name="name[<?php echo $element->id?>]">
  		</td>
  		<td>
	  		<select id="copy-<?php echo $element->id?>" name="cid[<?php echo $element->id;?>]">
 						<?php foreach ($this->groups as $group) {?>
 							<option value="<?php echo $group->id?>"><?php echo $group->name?></option>
 						<?php }?>
 					</select>
 					</td>
  		</tr>
		<?php }?>
	</tbody>
	<thead>
	<tr>
		<th><?php echo JText::_('COM_FABRIK_NAME')?></th>
		<th><?php echo JText::_('COM_FABRIK_COPY_TO_GROUP')?></th>
	</tr>
	</thead>
	</table>

		</ul>

	<input type="hidden" name="task" value="" />
  	<?php echo JHTML::_('form.token');
	echo JHTML::_('behavior.keepalive'); ?>
</form>