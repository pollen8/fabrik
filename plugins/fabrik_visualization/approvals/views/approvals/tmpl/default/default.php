<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
//@TODO if we ever get calendars inside packages then the ids will need to be
//replaced with classes contained within a distinct id

$row =& $this->row;
?>


<div id="<?php echo $this->containerId;?>">
	<?php if ($this->params->get('show-title', 1)) {?>
		<h1><?php echo $row->label;?></h1>
	<?php }?>
	<?php echo $this->html?>
</div>