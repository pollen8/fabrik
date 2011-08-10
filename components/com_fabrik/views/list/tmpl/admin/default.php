<?php if ($this->params->get('show_page_title', 1)) { ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php } ?>
<?php if ($this->tablePicker != '') { ?>
	<div style="text-align: right">
		<?php echo JText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?>
	</div>
<?php } ?>
<?php if ($this->params->get('show-title', 1)) {?>
	<h1><?php echo $this->table->label;?></h1>
<?php }?>
<?php echo $this->table->intro;?>
<form action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

<?php echo $this->loadTemplate('buttons');?>

<?php if ($this->showFilters) {?>
	<table class="filtertable">
		<tr>
			<th style="text-align:left"><?php echo JText::_('COM_FABRIK_SEARCH');?>:</th>
			<th style="text-align:right"><?php echo $this->clearFliterLink;?></th>
		</tr>
		<?php
		$c = 0;
		foreach ($this->filters as $filter) {
			$required = $filter->required == 1 ? ' class="notempty"' : '';?>
			<tr class="fabrik_row oddRow<?php echo ($c % 2);?>">
				<td<?php echo $required ?>><?php echo $filter->label;?></td>
				<td style="text-align:right;"><?php echo $filter->element;?></td>
			</tr>
		<?php $c++;
		} ?>
		<?php if ($this->filter_action != 'onchange') {?>
		<tr>
			<td colspan="2" style="text-align:right;">
				<input type="button" class="fabrik_filter_submit button" value="<?php echo JText::_('COM_FABRIK_GO');?>" name="filter" />
			</td>
		</tr>
		<?php }?>
	</table>
<?php } // end show filters ?>
<br style="clear:right" />
<?php $fbConfig =& JComponentHelper::getParams('com_fabrik');
		if ($fbConfig->get('use_fabrikdebug', false) == 1) {?>
<label>
<?php $checked = JRequest::getVar('fabrikdebug', 0) == 1 ? 'checked="checked"' : '';?>
	<input type="checkbox" name="fabrikdebug" value="1" <?php echo $checked?> onclick="document.fabrikList.submit()" />
	<?php echo JText::_('debug')?>
</label>
<?php }?>

<div class="tablespacer"></div>

<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>"><?php echo $this->emptyDataMessage; ?></div>
<div class="fabrikDataContainer" style="<?php echo $this->tableStyle?>">
<?php foreach ($this->pluginBeforeList as $c) {
			echo $c;
			}?>
<?php
echo $this->loadTemplate('group');
?>
<table class="adminlist fabrikList">
	<tfoot>
		<tr>
		<td colspan="<?php echo count($this->headings) ?>">
		<?php echo $this->nav;?>
		<div style="text-align:right">
		</div>
	</td>
	</tr>
</tfoot>
</table>

<?php print_r($this->hiddenFields);?>
</div>
</form>


