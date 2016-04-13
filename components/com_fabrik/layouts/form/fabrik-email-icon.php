<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;

if (!$d->popup):

?>

	<a class="btn btn-default fabrikWin" rel='{"title":"<?php echo Text::_('JGLOBAL_EMAIL'); ?>", "loadMethod":"iframe", "height":"300px"}' href="<?php echo $d->link;?>">
		<?php echo Html::icon('icon-envelope', Text::_('JGLOBAL_EMAIL'));?>
	</a>

	<?php
endif;

