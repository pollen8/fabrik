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

if($this->params->get('slideshow_viz_width', '400') === '0')
{
	$width = 'width:auto;';
	$height = 'max-height:'. $this->params->get('slideshow_viz_height', '400').'px;';
}
else if($this->params->get('slideshow_viz_height', '300') === '0')
{
	$width = 'max-width:'. $this->params->get('slideshow_viz_width', '300').'px;';
	$height = 'height:auto;';
}
else
{
	$width = 'width:'.$this->params->get('slideshow_viz_width', '400').'px;';
	$height = 'height:'. $this->params->get('slideshow_viz_height', '300').'px;';
}
?>

<style>
    figure.image {
        text-align: center;
        font-style: italic;
        font-size: small;
        text-indent: 0;
        margin: 0em;
        padding: 0em;
    }

    figcaption {
        width: 100%;
        display: inline-block;
        margin:auto;
    }

    .slick-slide
    {
        width:auto;
        height:auto;
    }

    .slick-prev:before, .slick-next:before {
        /*color:red !important;*/
    }

    .slider img {
        <?php echo $height; ?>
        <?php echo $width; ?>
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
    <div class="slideshow" id="slideshow_viz_<?php echo $row->id; ?>" style="display:none">
        <div class="slider" style="width:<?php echo $this->params->get('slideshow_viz_width', '400')?>px;margin:auto">
			<?php
			foreach ($this->slideData as $slide):
				?>
                <figure class="image">
                    <img src="<?php echo $slide['href']; ?>" />
                    <figcaption>
						<?php echo $slide['caption']; ?>
                    </figcaption>
                </figure>
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
