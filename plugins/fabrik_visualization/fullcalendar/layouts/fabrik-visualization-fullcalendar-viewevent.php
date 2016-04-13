<?php
defined('JPATH_BASE') or die;
use Fabrik\Helpers\Text;
?>
<div class="form-horizontal" id="viewDetails">
	<div class="row-striped">
		<div class="row-fluid span8">
			<div class="span2"><?php echo Text::_('PLG_VISUALIZATION_FULLCALENDAR_START') ?>:</div>
			<div class="span6" id="viewstart"></div>
		</div>
		<div class="row-fluid span8">
			<div class="span2"><?php echo Text::_('PLG_VISUALIZATION_FULLCALENDAR_END') ?>:</div>
			<div class="span6" id="viewend"></div>
		</div>
	</div>
</div>

