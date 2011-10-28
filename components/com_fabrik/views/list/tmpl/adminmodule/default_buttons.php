<div class="fabrik_buttons">
	<ul class=""><?php if ($this->showAdd) {?>
		<li class="button addbutton">
			<a class="addRecord" href="<?php echo $this->addRecordLink;?>">
				<?php echo FabrikHelperHTML::image('add.png', 'list', $this->tmpl, JText::_('COM_FABRIK_ADD'));?>
				<span><?php echo $this->addLabel?></span>
			</a>
		</li>
	<?php }?>
		<li class="button">
			<a href="index.php?option=com_fabrik&task=list.view&listid=<?php echo $this->list->id?>">
				<?php echo FabrikHelperHTML::image('view.png', 'list', $this->tmpl, 'view all');?>
				<span><?php echo JText::_('view all');?></span>
			</a>
		</li>
	</ul>
</div>