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
<form class="fabrikForm" action="<?php echo $this->table->action;?>"
	method="post" id="<?php echo $this->formid;?>" name="fabrikList">
	<?php
	echo $this->loadTemplate('buttons');

	//for some really ODD reason loading the headings template inside the group
	//template causes an error as $this->_path['template'] doesnt cotain the correct
	// path to this template - go figure!
	$this->headingstmpl =  $this->loadTemplate('headings');
	if ($this->showFilters) {
		echo $this->loadTemplate('filter');
	}
	?> <br style="clear: right;" />
<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>"><?php echo $this->emptyDataMessage; ?></div>
<div class="fabrikDataContainer" style="<?php echo $this->tableStyle?>">
<?php foreach ($this->pluginBeforeList as $c) {
			echo $c;
			}?>
	<?php

		echo $this->loadTemplate('group');
		echo $this->nav;


	print_r($this->hiddenFields);?>
	</div>
</form>