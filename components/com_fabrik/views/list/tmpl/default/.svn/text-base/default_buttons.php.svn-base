<div class="fabrik_buttons">
	<ul class="fabrik_action"><?php if ($this->showAdd) {?>
		<li class="addbutton">
			<a class="addRecord" href="<?php echo $this->addRecordLink;?>">
				<?php echo FabrikHelperHTML::image('add.png', 'list', $this->tmpl, JText::_('COM_FABRIK_ADD'));?>
				<span><?php echo JText::_('COM_FABRIK_ADD');?></span>
			</a>

		</li>
	<?php }?>

	<!--  remove this if you always want to show the filters, this button toggles their visibility -->
	<?php if (!empty($this->filters)) {?>
		<li>
			<a href="#" class="toggleFilters">
				<?php echo FabrikHelperHTML::image('filter.png', 'list', $this->tmpl, JText::_('COM_FABRIK_FILTER'));?>
				<span><?php echo JText::_('COM_FABRIK_FILTER');?></span>
			</a>
		</li>
	<?php } ?>

		<li>
			<a href="#" class="groupBy">
				<?php echo FabrikHelperHTML::image('group_by.png', 'list', $this->tmpl, JText::_('COM_FABRIK_GROUP_BY'));?>
				<span><?php echo JText::_('COM_FABRIK_GROUP_BY');?></span>
			</a>
			<ul class="floating-tip">
				<?php foreach($this->groupByHeadings as $url => $label) {?>
					<li><a href="<?php echo $url?>"><?php echo $label?></a></li>
				<?php
				}?>
			</ul>

		</li>



	<?php if ($this->showCSV) {?>
		<li class="csvExportButton">
			<a href="#">
				<?php echo FabrikHelperHTML::image('csv-export.png', 'list', $this->tmpl, JText::_('COM_FABRIK_EXPORT_TO_CSV'));?>
				<span><?php echo JText::_('COM_FABRIK_EXPORT_TO_CSV');?></span>
			</a>
		</li>
	<?php }?>

	<?php if ($this->showCSVImport) {?>
	<li class="csvImportButton">
			<a href="<?php echo $this->csvImportLink;?>">
				<?php echo FabrikHelperHTML::image('csv-import.png', 'list', $this->tmpl, JText::_('COM_FABRIK_IMPORT_FROM_CSV'));?>
				<span><?php echo JText::_('COM_FABRIK_IMPORT_FROM_CSV');?></span>
			</a>

		</li>
	<?php }?>

	<?php if ($this->showRSS) {?>
	<li class="feedButton">
			<a href="<?php echo $this->rssLink;?>">
				<?php echo FabrikHelperHTML::image('feed.png', 'list', $this->tmpl, JText::_('COM_FABRIK_SUBSCRIBE_RSS'));?>
				<span><?php echo JText::_('COM_FABRIK_SUBSCRIBE_RSS');?></span>
			</a>
		</li>
	<?php }?>

	<?php if ($this->showPDF) {
	echo '<li>'.$this->pdfLink.'<li>';
	}?>

	<?php if ($this->emptyLink) {?>
		<li>
		<a href="<?php echo $this->emptyLink?>" class="button doempty">
		<?php echo FabrikHelperHTML::image('trash.png', 'list', $this->tmpl, JText::_('COM_FABRIK_EMPTY'));?>
		<span><?php echo JText::_('COM_FABRIK_EMPTY')?></span>
		</a>
		</li>
	<?php }?>
	</ul>
</div>