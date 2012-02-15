<ul class="adminformlist">
<?php foreach ($this->listform->getFieldset('details') as $field): ?>
<li>
	<?php echo $field->label . $field->input; ?>
</li>
<?php endforeach; ?>
</ul>