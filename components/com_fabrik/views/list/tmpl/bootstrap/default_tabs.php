<?php
if (!empty($this->tabs)) :
?>
<div>
	<ul class="nav nav-tabs">
	<?php foreach ($this->tabs as $tab) :
	?>
    <li <?php echo $tab->class ?>><a href="<?php echo $tab->url?>"><?php echo $tab->label?></a></li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
