<?php
/**
 * List related data add button.
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;
$trigger = $d->popUp ? 'data-fabrik-view="form"' : '';
?>

<?php if ($d->canAdd) :?>
<span class="addbutton">
	<a <?php echo $trigger; ?> href="<?php echo $d->url ;?>" title="<?php echo $d->label; ?>">
	<?php echo Html::icon('icon-plus', $d->label); ?>
	</a>
</span></a>
<?php else :?>
	<div style="text-align:center">
		<a title="<?php echo Text::_('JERROR_ALERTNOAUTHOR'); ?>">
			<img src="<?php echo COM_FABRIK_LIVESITE; ?>media/com_fabrik/images/login.png"
				alt="<?php echo Text::_('JERROR_ALERTNOAUTHOR'); ?>" />
		</a>
	</div>
<?php endif; ?>
