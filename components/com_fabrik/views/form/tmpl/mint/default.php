<?php if ($this->params->get('show_page_title', 1)) { ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php } ?>
<?php $form =& $this->form;
echo $form->startTag;
if ($this->params->get('show-title', 1)) {?>
<h1><?php echo $form->label;?></h1>
<?php }
echo $form->intro;
echo $this->plugintop;
$active = ($form->error != '') ? '' : ' fabrikHide';
echo "<div class=\"fabrikMainError fabrikError$active\">$form->error</div>";?>

	<?php
	if ($this->showEmail) {
		echo $this->emailLink;
	}
	if ($this->showPDF) {
		echo $this->pdfLink;
	}
	if ($this->showPrint) {
		echo $this->printLink;
	}
	echo $this->loadTemplate('relateddata');
	foreach ($this->groups as $group) {?>
		<fieldset class="fabrikGroup" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">
		<legend><?php echo $group->title;?></legend>
		<?php if ($group->canRepeat) {
			foreach ($group->subgroups as $subgroup) {
			?>
				<div class="fabrikSubGroup">
					<div class="fabrikSubGroupElements">
						<?php
						$this->elements = $subgroup;
						echo $this->loadTemplate('group');
						?>
					</div>
					<?php if ($group->editable) { ?>
						<div class="fabrikGroupRepeater">
							<a class="addGroup" href="#">
								<img src="<?php echo COM_FABRIK_LIVESITE?>components/com_fabrik/views/form/tmpl/default/images/add.png" alt="<?php echo JText::_('COM_FABRIK_ADD_GROUP');?>" />
							</a>
							<a class="deleteGroup" href="#">
								<img src="<?php echo COM_FABRIK_LIVESITE?>components/com_fabrik/views/form/tmpl/default/images/del.png" alt="<?php echo JText::_('COM_FABRIK_DELETE_GROUP');?>" />
							</a>
						</div>
					<?php } ?>
					<div style="clear:left;"></div>
				</div>
				<?php
			}
		} else {
			$this->elements = $group->elements;
			echo $this->loadTemplate('group');
		}?>
	</fieldset>
<?php
	}
	echo $this->hiddenFields;
	?>
	<?php echo $this->pluginbottom; ?>
	<div class="fabrikActions"><?php echo $form->resetButton;?> <?php echo $form->submitButton;?>
	 <?php echo $form->applyButton;?>
	<?php echo $form->copyButton  . " " . $form->gobackButton . ' ' . $form->deleteButton . ' ' . $this->message ?>
	</div>
<?php
echo $form->endTag;
echo FabrikHelperHTML::keepalive();?>