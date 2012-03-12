<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$row = $this->row;
?>
<div id="slideshow_viz_<?php echo $row->id;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 1)) {?>
		<h1><?php echo $row->label;?></h1>
	<?php }?>
	<?php echo $this->loadTemplate( 'filter'); ?>
	<div><?php echo $row->intro_text;?></div>
	<div class="slideshow" id="slideshow_viz">
		<div class="slideshow-images">
			<a><img /></a>
			<div class="slideshow-loader"></div>
		</div>
		<div class="slideshow-captions"></div>
		<div class="slideshow-controller"></div>
		<div class="slideshow-thumbnails"></div>
	</div>
</div>