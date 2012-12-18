<?php
$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_title', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
<?php
endif;

if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
	<h1><?php echo $form->label;?></h1>
</div>
<?php
endif;

echo $form->intro;

if ($model->editable) :
echo '<form action="' . $form->action . '" class="fabrikForm form-horizontal" method="post" name="' . $form->name . '" id="' . $form->formid
				. '" enctype="' . $model->getFormEncType() . '">';
else:
	echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
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
<?php endif;

if ($this->showPDF):?>
	<a class="btn" href="<?php echo $this->pdfURL?>">
		<i class="icon-file"></i>
		<?php echo JText::_('COM_FABRIK_PDF')?>
	</a>
<?php endif;

if ($this->showPrint):?>
	<a class="btn" href="<?php echo $this->printURL?>">
		<i class="icon-print"></i>
		<?php echo JText::_('JGLOBAL_PRINT')?>
	</a>
<?php
endif;

echo $this->loadTemplate('relateddata');
foreach ($this->groups as $group) :
	?>

		<<?php echo $form->fieldsetTag ?> class="fabrikGroup row-fluid" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">

		<?php if (trim($group->title) !== '') :
		?>
		<div class="page-header">
			<<?php echo $form->legendTag ?> class="legend"><span><?php echo $group->title;?></span></<?php echo $form->legendTag ?>>
		</div>
		<?php endif;

		if ($group->intro !== '') : ?>
			<div class="groupintro"><?php echo $group->intro ?></div>
		<?php
		endif;
		if ($group->canRepeat) :
			foreach ($group->subgroups as $subgroup) :
			?>
				<div class="fabrikSubGroup">
					<div class="fabrikSubGroupElements">
						<?php
						$this->elements = $subgroup;
						echo $this->loadTemplate($groupTmpl);
						?>
					</div>
					<?php if ($group->editable) : ?>
						<div class="fabrikGroupRepeater">
							<?php if ($group->canAddRepeat) :?>
							<a class="addGroup" href="#">
								<?php echo FabrikHelperHTML::image('plus-sign.png', 'form', $this->tmpl, array('class' => 'fabrikTip', 'title' => JText::_('COM_FABRIK_ADD_GROUP')));?>
							</a>
							<?php
							endif;
							if ($group->canDeleteRepeat) :?>
							<a class="deleteGroup" href="#">
								<?php echo FabrikHelperHTML::image('minus-sign.png', 'form', $this->tmpl, array('class' => 'fabrikTip', 'title' => JText::_('COM_FABRIK_DELETE_GROUP')));?>
							</a>
							<?php endif;?>
						</div>
					<?php endif; ?>
				</div>
				<?php
			endforeach;
		else:
			$this->elements = $group->elements;
			echo $this->loadTemplate($groupTmpl);
		endif; ?>
	</<?php echo $form->fieldsetTag ?>>
<?php
endforeach;
if ($model->editable) :
	echo $this->hiddenFields;
endif;
?>
<?php echo $this->pluginbottom;

if ($this->hasActions) : ?>
<div class="fabrikActions form-actions">
	<div class="row-fluid">
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
				<?php echo $form->nextButton . ' ' . $form->prevButton; ?>
			</div>
		</div>

		<div class="span4">
			<div class="btn-group">
				<?php
				echo $form->gobackButton  . ' ' . $this->message;
				echo $form->resetButton . ' ';
				echo  $form->deleteButton;
				?>
			</div>
		</div>
	</div>
</div>
<?php
endif;
echo $form->endTag;
echo $this->pluginend;
echo FabrikHelperHTML::keepalive();
?>