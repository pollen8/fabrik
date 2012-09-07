<div class="control-group">
<?php if (!$this->field->hidden) :?>
	<div class="control-label">
		<?php echo $this->field->label; ?>
	</div>
<?php endif; ?>
	<div class="controls">
		<?php echo $this->field->input; ?>
	</div>
</div>