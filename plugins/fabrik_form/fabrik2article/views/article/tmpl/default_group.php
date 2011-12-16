<ul>
<?php foreach ( $this->elements as $element ) {
	if ($this->isSkipElement($element->id)) {
		continue;
	}
	?>
	<li <?php echo @$element->column;?> class="<?php echo $element->containerClass;?>">
		<?php echo $element->label;?>
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

