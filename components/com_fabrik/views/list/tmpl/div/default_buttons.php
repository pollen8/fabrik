<div class="fabrik_buttons">
	<ul class="fabrik_action neverToggle"><?php if ($this->showAdd) {?>
		<li class="addbutton">
			<a class="addRecord" href="<?php echo $this->addRecordLink;?>">
				<?php echo FabrikHelperHTML::image('add.png', 'list', $this->tmpl,  array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_ADD').'</span>'));?>
				<span><?php echo $this->addLabel?></span>
			</a>
		</li>
	<?php }
	
	if ($this->showFilters && $this->params->get('show-table-filters') == 2) {?>
		<li>
			<a href="#" class="toggleFilters">
				<?php echo FabrikHelperHTML::image('filter.png', 'list', $this->tmpl, array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_FILTER').'</span>'));?>
				<span><?php echo JText::_('COM_FABRIK_FILTER');?></span>
			</a>
		</li>
	<?php }
	if ($this->canGroupBy) {?>
		<li>
			<a href="#" class="groupBy">
				<?php echo FabrikHelperHTML::image('group_by.png', 'list', $this->tmpl, array('title' => JText::_('COM_FABRIK_GROUP_BY')));?>
				<span><?php echo JText::_('COM_FABRIK_GROUP_BY');?></span>
			</a>
			<ul class="floating-tip">
				<?php foreach ($this->groupByHeadings as $url => $label) {?>
					<li><a href="<?php echo $url?>"><?php echo $label?></a></li>
				<?php
				}?>
			</ul>
		</li>
	<?php }
	 if ($this->showCSV) {?>
		<li class="csvExportButton">
			<a href="#">
				<?php echo FabrikHelperHTML::image('csv-export.png', 'list', $this->tmpl, array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_EXPORT_TO_CSV').'</span>'));?>
				<span><?php echo JText::_('COM_FABRIK_EXPORT_TO_CSV');?></span>
			</a>
		</li>
	<?php }
	if ($this->showCSVImport) {?>
	<li class="csvImportButton">
			<a href="<?php echo $this->csvImportLink;?>">
				<?php echo FabrikHelperHTML::image('csv-import.png', 'list', $this->tmpl, array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_IMPORT_FROM_CSV').'</span>'));?>
				<span><?php echo JText::_('COM_FABRIK_IMPORT_FROM_CSV');?></span>
			</a>
		</li>
	<?php }
	if ($this->showRSS) {?>
	<li class="feedButton">
			<a href="<?php echo $this->rssLink;?>">
				<?php echo FabrikHelperHTML::image('feed.png', 'list', $this->tmpl, array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_SUBSCRIBE_RSS').'</span>'));?>
				<span><?php echo JText::_('COM_FABRIK_SUBSCRIBE_RSS');?></span>
			</a>
		</li>
	<?php }
	if ($this->showPDF) {
		echo '<li>'.$this->pdfLink.'<li>';
	}
	if ($this->emptyLink) {?>
		<li>
		<a href="<?php echo $this->emptyLink?>" class="button doempty">
		<?php echo FabrikHelperHTML::image('trash.png', 'list', $this->tmpl,  array('class' => 'fabrikTip', 'title' => '<span>'.JText::_('COM_FABRIK_EMPTY').'</span>'));?>
		<span><?php echo JText::_('COM_FABRIK_EMPTY')?></span>
		</a>
		</li>
<?php }?>
</ul>
</div>