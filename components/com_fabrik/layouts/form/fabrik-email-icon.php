<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;

if (!$d->popup):

?>

	<a class="btn btn-default fabrikWin" rel='{"title":"<?php echo FText::_('JGLOBAL_EMAIL'); ?>", "loadMethod":"iframe", "height":"300px"}' href="<?php echo $d->link;?>">
		<?php echo FabrikHelperHTML::icon('icon-envelope', FText::_('JGLOBAL_EMAIL'));?>
	</a>

	<?php
endif;

