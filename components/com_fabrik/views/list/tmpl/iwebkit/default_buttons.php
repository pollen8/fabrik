    <div class="btn-group">


<?php if ($this->showAdd) {?>

		<a class="addbutton btn addRecord" href="<?php echo $this->addRecordLink;?>">
			<i class="icon-plus-sign"></i>
			<?php echo $this->addLabel?>
		</a>
<?php }?>

<?php if ($this->showCSV) {?>
		<a href="#" class="btn csvExportButton">
			<i class="icon-upload"></i>
			<?php echo JText::_('COM_FABRIK_EXPORT_TO_CSV');?>
		</a>
<?php }?>

<?php if ($this->showCSVImport) {?>
		<a href="<?php echo $this->csvImportLink;?>" class="btn csvImportButton">
			<i class="icon-download"></i>
			<?php echo JText::_('COM_FABRIK_IMPORT_FROM_CSV');?>
		</a>
<?php }?>

<?php if ($this->showRSS) {?>
		<a href="<?php echo $this->rssLink;?>" class="btn feedButton">
			<?php echo FabrikHelperHTML::image('feed.png', 'list', $this->tmpl);?>
			<?php echo JText::_('COM_FABRIK_SUBSCRIBE_RSS');?>
		</a>
<?php }
	if ($this->showPDF) {?>
			<a href="<?php echo $this->pdfLink;?>" class="btn pdfButton">
				<i class="icon-file"></i>
				<?php echo JText::_('COM_FABRIK_PDF');?>
			</a>
	<?php }?>
</div>