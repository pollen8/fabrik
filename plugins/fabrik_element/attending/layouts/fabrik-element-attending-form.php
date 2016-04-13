<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Text;

$d = $displayData;
?>

<div id="<?php echo $d->id; ?>" class="fabrikSubElementContainer">
	<div class="msg"></div>
	<a class="btn btn-primary" data-action="add"><?php echo Text::_('PLG_FABRIK_ELEMENT_ATTENDING_JOIN');?></a>
	<div class="media">
	<?php foreach ($d->attendees as $user) :
	?>
	<a class="pull-left" href="#">
		<img class="media-object" data-src="holder.js/64x64">
		</a>
	<div class="media-body">
		<h4 class="media-heading"><?php echo $user->get('name');?></h4>
		</div>
	<?php
	endforeach;
	?>
	</div>
</div>