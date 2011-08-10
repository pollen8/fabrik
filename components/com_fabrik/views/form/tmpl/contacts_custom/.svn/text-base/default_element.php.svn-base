<?php
/*
 This part of the template is what actually renders each individual element.  You will be loading this
 template multiple times (once for each element you want to display) from your default_group.php file.

 You probably won't need to edit this file - most changes you want can probably be done
 by overriding the template_css.php file in your J template html overrides folder

 If you do edit this file, make sure you use the same parts of the element this example uses,
 i.e. the same class definitions, etc.
*/
?>
	<div <?php echo @$this->element->column;?> class="<?php echo $this->element->containerClass;?>">
		<?php echo $this->element->label;?>
		<?php echo $element->errorTag; ?>
		<div class="fabrikElement">
			<?php echo $this->element->element;?>

		</div>
		<div class="fabrikErrorMessage">
				<?php echo $this->element->error;?>
			</div>
		<div style="clear:both"></div>
	</div>

	<?php
	$this->element->rendered = true;
	?>