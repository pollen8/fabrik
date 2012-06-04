<?php
// no direct access
defined('_JEXEC') or die;
?>
<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<?php foreach ($this->lists as $list) {?>
	<h2><?php echo JText::_('COM_FABRIK_LIST_COPY_RENAME_LIST')?></h2>
	<label>
		<?php echo $list->label?>:
		<input type="text" name="names[<?php echo $list->id?>][listLabel]" value="<?php echo $list->label?>" />
	</label>
	<h2><?php echo JText::_('COM_FABRIK_LIST_COPY_RENAME_FORM')?></h2>
	<label>
		<?php echo $list->formlabel?>:
		<input type="text" name="names[<?php echo $list->id?>][formLabel]" value="<?php echo $list->formlabel?>" />
	</label>
	<h2><?php echo JText::_('COM_FABRIK_LIST_COPY_RENAME_GROUPS')?></h2>
	<ul>
	<?php foreach ($list->groups as $group) {?>
		<li>
		<label><?php echo $group->name?>:
		<input type="text" name="names[<?php echo $list->id?>][groupNames][<?php echo $group->id?>]" value="<?php echo $group->name?>" />
		</label>
		</li>
	<?php }?>
	</ul>
	<input type="hidden" name="cid[]" value="<?php echo $list->id?>" />
	<?php }?>
	<input type="hidden" name="option" value="com_fabrik" />
	<input type="hidden" name="task" value="list.doCopy" />
	<?php echo JHtml::_('form.token'); ?>
</form>