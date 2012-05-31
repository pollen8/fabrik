<ul>
<?php foreach ( $this->elements as $element) {
	?>
	<li <?php echo @$element->column;?> class="<?php echo $element->containerClass;?>">
		<?php echo $element->label;?>
		<?php echo $element->errorTag; ?>
		<div class="fabrikElement">
			<?php echo $element->element;?>
		</div>
		<div class="fabrikErrorMessage">
				<?php echo $element->error;?>
			</div>
		<div style="clear:both"></div>
	</li>
	<?php }?>
</ul>

