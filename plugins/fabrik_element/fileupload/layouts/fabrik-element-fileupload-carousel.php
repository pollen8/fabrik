<?php
defined('JPATH_BASE') or die;

$d      = $displayData;
?>

<div id="<?php echo $d->id;?>" class="carousel slide mootools-noconflict" data-interval="false"
	data-pause="hover" style="width:<?php echo $d->width;?>px">

	<!-- Carousel items -->
	<div class="carousel-inner">
		<div class="active item">
			<?php echo implode("\n		</div>\n" . '		<div class="item">', $d->imgs); ?>
		</div>
	</div>
	<!-- Carousel nav -->
	<a class="carousel-control left" href="#<?php echo $d->id; ?>" data-slide="prev">&lsaquo;</a>
	<a class="carousel-control right" href="#<?php echo $d->id; ?>" data-slide="next">&rsaquo;</a>
</div>



