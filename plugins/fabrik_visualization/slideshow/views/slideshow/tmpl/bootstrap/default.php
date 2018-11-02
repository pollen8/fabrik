<?php
/**
 * Slideshow vizualization: bootstrap template
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.slideshow
 * @copyright	Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$row = $this->row;
?>
<style>

    .slick-slide
    {
        width: 400px;
    }


    .slick-prev:before, .slick-next:before {
        color:red !important;
    }

    .slider img {
        height: calc(50vh - 100px);
        width: auto;
        margin: 0 auto; /* it centers any block level element */
    }
</style>
<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 1)) :?>
	<h1>
		<?php echo $row->label;?>
	</h1>
	<?php endif;?>
	<?php echo $this->loadTemplate('filter'); ?>
	<div>
		<?php echo $row->intro_text;?>
	</div>
	<div class="slideshow" id="slideshow_viz_<?php echo $row->id; ?>">
		<div class="slider" style="width:400px;margin:auto">
            <?php
            foreach ($this->slideData as $slide):
                ?>
            <div class="image">
                <img src="<?php echo $slide['href']; ?>" />
                <figcaption>
                    <?php echo $slide['caption']; ?>
                </figcaption>
            </div>
                <?php
            endforeach;
            ?>
		</div>

        <?php
		if ($this->params->get('slideshow_viz_thumbnails', false)):
            ?>
        <div class="slider-nav">
            <?php
            foreach ($this->slideData as $slide):
                ?>
            <div class="image">
                <img src="<?php echo $slide['thumbnail']; ?>" />
            </div>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        endif;
        ?>
	</div>
</div>
