<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
//@TODO if we ever get calendars inside packages then the ids will need to be
//replaced with classes contained within a distinct id

$row =& $this->row;
?>


<div id="<?php echo $this->containerId;?>" style="border:1px sold;margin:5px;">
	<h1><?php echo $row->label;?></h1>
	<div class='calendar-message'>

	</div>
	<?php echo $this->loadTemplate( 'filter'); ?>
		<?php if ($this->canAdd) {?>
		<a href="#" class="addEventButton" title="Add an event"><?php echo JText::_('PLG_VISUALIZATION_CALENDAR_ADD') ?></a>
	<?php }?>
	<?php if ($row->intro_text != '') {?>
	<div><?php echo $row->intro_text;?></div>
	<?php }?>

</div>