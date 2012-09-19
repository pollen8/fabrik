<?php if ($this->params->get('show_page_title', 1)) { ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php } ?>
<?php $form = $this->form;
//echo $form->startTag;
if ($this->params->get('show-title', 1)) {?>

<?php  /*This will show the forms label */?>
<div class="page-header">
	<h1><?php echo $form->label;?></h1>
</div>
<?php  /*This area will show the form's intro as well as any errors */ ?>
<?php }
echo $form->intro;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
if ($model->editable) {
echo '<form action="' . $form->action . '" class="fabrikForm form-horizontal" method="post" name="' . $form->name . '" id="' . $form->formid
				. '" enctype="' . $model->getFormEncType() . '">';
}
else
{
	echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
}
echo $this->plugintop;
$active = ($form->error != '') ? '' : ' fabrikHide';
?>

    <div class="fabrikMainError alert alert-error fabrikError<?php echo $active?>">
    <button class="close" data-dismiss="alert">×</button>
    <?php echo $form->error?>
    </div>

	<?php if ($this->showEmail): ?>
		<a class="btn" href="<?php echo $this->emailURL?>">
		<i class="icon-envelope"></i>
		<?php echo JText::_('JGLOBAL_EMAIL'); ?>
		</a>
	<?php endif?>

	<?php if ($this->showPDF):?>
		<a class="btn" href="<?php echo $this->pdfURL?>">
			<i class="icon-file"></i>
			<?php echo JText::_('COM_FABRIK_PDF')?>
		</a>
	<?php endif;?>

	<?php if ($this->showPrint):?>
		<a class="btn" href="<?php echo $this->printURL?>">
			<i class="icon-print"></i>
			<?php echo JText::_('JGLOBAL_PRINT')?>
		</a>
	<?php endif;?>


	<?php
	echo $this->loadTemplate('relateddata');
	foreach ($this->groups as $group) {
		?>

<?php  /* This is where the fieldset is set up */ ?>
		<<?php echo $form->fieldsetTag ?> class="fabrikGroup row-fluid" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">

		<?php if (trim($group->title) !== '') :
		?>
		<div class="page-header">
			<<?php echo $form->legendTag ?> class="legend"><span><?php echo $group->title;?></span></<?php echo $form->legendTag ?>>
		</div>
		<?php endif;?>

<?php  /* This is where the group intro is shown */ ?>
		<?php if ($group->intro !== '') {?>
		<div class="groupintro"><?php echo $group->intro ?></div>
		<?php }?>

		<?php if ($group->canRepeat) {
			foreach ($group->subgroups as $subgroup) {
			?>
				<div class="fabrikSubGroup">
					<div class="fabrikSubGroupElements">
						<?php
						$this->elements = $subgroup;
						echo $this->loadTemplate($groupTmpl);
						?>
					</div>
					<?php if ($group->editable) { ?>
						<div class="fabrikGroupRepeater">
							<?php if ($group->canAddRepeat) {?>
							<a class="addGroup" href="#">
								<?php echo FabrikHelperHTML::image('plus-sign.png', 'form', $this->tmpl, array('class' => 'fabrikTip', 'title' => JText::_('COM_FABRIK_ADD_GROUP')));?>
							</a>
							<?php }?>
							<?php if ($group->canDeleteRepeat) {?>
							<a class="deleteGroup" href="#">
								<?php echo FabrikHelperHTML::image('minus-sign.png', 'form', $this->tmpl, array('class' => 'fabrikTip', 'title' => JText::_('COM_FABRIK_DELETE_GROUP')));?>
							</a>
							<?php }?>
						</div>
					<?php } ?>
				</div>
				<?php
			}
		} else {
			$this->elements = $group->elements;
			echo $this->loadTemplate($groupTmpl);
		}?>
	</<?php echo $form->fieldsetTag ?>>
<?php
	}
	if ($model->editable) {
	echo $this->hiddenFields;
	}
	?>
	<?php echo $this->pluginbottom; ?>

<?php  /* This is where the buttons at the bottom of the form are set up */ ?>
	<?php if ($this->hasActions) {?>
	<div class="fabrikActions form-actions">

	<div class="row">
		<div class="span4">
			<div class="btn-group">
			<?php
			echo $form->submitButton;
			echo $form->applyButton;
			echo $form->copyButton;
			?>
			</div>
		</div>
		<div class="span4">
		<div class="btn-group">
	<?php echo $form->nextButton?> <?php echo $form->prevButton?>
	</div>
		</div>

		<div class="span4">
		<div class="btn-group">
	<?php echo $form->gobackButton  . ' ' . $this->message ?>
	<?php echo $form->resetButton . ' ';
	echo  $form->deleteButton;?>
	</div>
		</div>
	</div>



	</div>
	<?php } ?>

<?php
echo $form->endTag;
echo $this->pluginend;
echo FabrikHelperHTML::keepalive();?>