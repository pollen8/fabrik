<ul class="fabrik_action"><?php if ($this->showAdd) {?>
	<li class="addbutton">
		<a class="addRecord" href="<?php echo $this->addRecordLink;?>">
			<?php echo $this->buttons->add;?>
			<span><?php echo $this->addLabel?></span>
		</a>

	</li>
<?php }?>

<!--  remove this if you always want to show the filters, this button toggles their visibility -->
<?php if (!empty($this->filters)) {?>
	<li>
		<a href="#" class="toggleFilters">
			<?php echo $this->buttons->filter;?>
			<span><?php echo JText::_('COM_FABRIK_FILTER');?></span>
		</a>
	</li>
<?php } ?>

<?php if ($this->showCSV) {?>
	<li class="csvExportButton">
		<a href="#">
			<?php echo $this->buttons->csvexport;?>
			<span><?php echo JText::_('COM_FABRIK_EXPORT_TO_CSV');?></span>
		</a>
	</li>
<?php }?>

<?php if ($this->showCSVImport) {?>
<li class="csvImportButton">
		<a href="<?php echo $this->csvImportLink;?>">
			<?php echo $this->buttons->csvimport;?>
			<span><?php echo JText::_('COM_FABRIK_IMPORT_FROM_CSV');?></span>
		</a>

	</li>
<?php }?>

<?php if ($this->showRSS) {?>
<li class="feedButton">
		<a href="<?php echo $this->rssLink;?>">
			<?php echo $this->buttons->feed;?>
			<span><?php echo JText::_('COM_FABRIK_SUBSCRIBE_RSS');?></span>
		</a>
	</li>

<?php }
	if ($this->showPDF) {?>
		<li class="pdfButton">
			<a href="<?php echo $this->pdfLink;?>">
				<?php echo $this->buttons->pdf;?>
				<span><?php echo JText::_('COM_FABRIK_PDF');?></span>
			</a>
		</li>
	<?php }

 if ($this->emptyLink) {?>
	<li>
	<a href="<?php echo $this->emptyLink?>" class="doempty"/>
	<?php echo FabrikHelperHTML::image('trash.png', 'list', $this->tmpl, array('alt' => JText::_('COM_FABRIK_EMPTY')));?>
	<span><?php echo JText::_('COM_FABRIK_EMPTY')?></span>
	</a>
	</li>
<?php }?>
</ul>