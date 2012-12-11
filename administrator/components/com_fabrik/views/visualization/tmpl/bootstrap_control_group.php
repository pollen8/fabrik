<div class="control-group">
<?php if (!$this->field->hidden) :?>
	<div class="control-label">
		<?php echo $this->field->label; ?>
	</div>
<?php endif;
$style = $this->field->id == 'jform_intro_text' ? 'style="width:75%"': '' ?>
	<div class="controls" <?php echo $style?>>
		<?php echo $this->field->input; ?>
	</div>
</div>