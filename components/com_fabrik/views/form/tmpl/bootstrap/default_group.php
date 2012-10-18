
<?php foreach ($this->elements as $element) :
	$class = 'fabrikErrorMessage';
	if (trim($element->error) !== '') :
		$element->error = '<i class=" icon-exclamation-sign"></i> ' . $element->error;
		//$class .= ' alert alert-error';
		$element->containerClass .= ' error';
	endif;
?>

	<div class="control-group <?php echo $element->containerClass; ?>">

	<?php echo $element->label;?>
	<div class="controls">

		<?php if ($this->tipLocation == 'above') : ?>
			<p clas="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>


		<div class="fabrikElement">

			<?php echo $element->element;?>
			<span class="<?php echo $class?>">
				<?php echo $element->error ?>
			</span>
		</div>


		<?php if ($this->tipLocation == 'side') : ?>
			<p clas="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>
		</div>

	<?php if ($this->tipLocation == 'below') :?>
		<p clas="help-block"><?php echo $element->tipAbove ?></p>
	<?php endif ?>

	</div>

<?php endforeach; ?>
