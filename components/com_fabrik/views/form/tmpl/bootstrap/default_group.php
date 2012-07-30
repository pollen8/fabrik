
<?php foreach ($this->elements as $element) :?>

	<div class="control-group <?php echo $element->containerClass; ?>">
	<label for="<?php echo $element->id ?>" class="control-label">
		<?php echo $element->label_raw ?>
	</label>
	<div class="controls">

		<?php if ($this->tipLocation == 'above') : ?>
			<p clas="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>


		<div class="fabrikElement">

			<?php echo $element->element;?>
			<span class="help-inline fabrikErrorMessage">
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
