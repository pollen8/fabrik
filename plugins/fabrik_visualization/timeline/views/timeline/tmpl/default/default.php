<?php
/**
 * Default Timeline Viz layout
 * 
 * @package  Fabrik
 * @since    3.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$row = $this->row;
?>
<div id="<?php echo $this->containerId;?>">
<?php if ($this->params->get('show-title', 1))
{?>
	<h1><?php echo $row->label;?></h1>
<?php
}
?>
	<div><?php echo $row->intro_text;?></div>
	<?php echo $this->loadTemplate('filter'); ?>
	<div id="my-timeline" style="margin-top:20px;border:1px solid #ccc;width:<?php echo $this->width?>px;height:<?php echo $this->height?>px;"></div>
</div>